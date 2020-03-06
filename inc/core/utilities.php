<?php

namespace Builders_Plugin\Inc\Core\Utilities;

use Builders_Plugin\Inc\Helpers\Validation;
use DateTime;

use const Builders_Plugin\Constants\BRANCH;
use const Builders_Plugin\Constants\FULL_NAME;
use const Builders_Plugin\Constants\GYM_ADMIN;
use const Builders_Plugin\Constants\GYM_MEMBER;
use const Builders_Plugin\Constants\GYM_TRAINER;
use const Builders_Plugin\Constants\HALF_YEAR;
use const Builders_Plugin\Constants\IS_STUDENT;
use const Builders_Plugin\Constants\MEMBERSHIP_DURATION;
use const Builders_Plugin\Constants\NINETY_DAYS;
use const Builders_Plugin\Constants\ONE_YEAR;
use const Builders_Plugin\Constants\PLUGIN_PREFIX;
use const Builders_Plugin\Constants\THIRTY_DAYS;
use const Builders_Plugin\Constants\VALIDDATEFORMAT;

if (!defined('ABSPATH')) exit;


add_action('wp_enqueue_scripts', __NAMESPACE__ . '\enqueue_scripts_frontend');
function enqueue_scripts_frontend()
{
    //versioning for cache busting: date('ymd-Gis', filemtime(dirpath . 'relativepath'))
    wp_enqueue_style(
        'builders-plugin-styles',
        BUILDERS_PLUGIN_URL . 'frontend/styles/styles.min.css',
        array(),
        date('ymd-Gis', filemtime(BUILDERS_PLUGIN_DIR . 'frontend/styles/styles.min.css')),
        'all'
    );

    if (is_page('registration')) {
        wp_enqueue_script(
            'builders-plugin-input',
            BUILDERS_PLUGIN_URL . 'frontend/js/input.js',
            array(),
            date('ymd-Gis', filemtime(BUILDERS_PLUGIN_DIR . 'frontend/js/input.js')),
            true
        );
        wp_enqueue_script(
            'datepicker.js',
            BUILDERS_PLUGIN_URL . 'frontend/third-party/flatpickr.min.js',
            array('jquery'),
            date('ymd-Gis', filemtime(BUILDERS_PLUGIN_DIR . 'frontend/third-party/flatpickr.min.js')),
            true
        );
        wp_enqueue_style(
            'datepickerjs-styles',
            BUILDERS_PLUGIN_URL . 'frontend/third-party/flatpickr.min.css',
            array(),
            date('ymd-Gis', filemtime(BUILDERS_PLUGIN_DIR . 'frontend/third-party/flatpickr.min.css')),
            'all'
        );
        wp_enqueue_script(
            'builders-plugin-datepicker',
            BUILDERS_PLUGIN_URL . 'frontend/js/form-datepicker.js',
            array('jquery'),
            date('ymd-Gis', filemtime(BUILDERS_PLUGIN_DIR . 'frontend/js/form-datepicker.js')),
            true
        );
    }

    if (isset($_REQUEST['success']) && isset($_REQUEST['gend'])) {
        //whenever there's a successful registration, add a timeout script to remove the notification after a few seconds
        wp_enqueue_script('builders-plugin-notification', BUILDERS_PLUGIN_URL . 'frontend/js/notification.js', array('jquery'), false, true);
    }
}


/**
 * When a new user is created, check if the new user is a gym member or trainer
 * If true, then update user meta to disable admin bar in front
 *
 * @uses wp_get_current_user()          Returns a WP_User object for the current user
 * @uses wp_redirect()                  Redirects the user to the specified URL
 */
add_action('user_register', __NAMESPACE__ . '\update_new_user_meta');
function update_new_user_meta($user_id)
{
    $user_info = get_userdata($user_id);
    $role = $user_info->roles[0];

    if ($role === GYM_MEMBER || $role === GYM_TRAINER) {
        update_user_meta($user_id, 'show_admin_bar_front', 'false');
    }

    return $user_id;
}

/**
 * Redirects users trying to access wp-admin based on their role
 *
 * @uses wp_get_current_user()          Returns a WP_User object for the current user
 * @uses wp_redirect()                  Redirects the user to the specified URL
 */
add_action('admin_init', __NAMESPACE__ . '\redirect_users_by_role');
function redirect_users_by_role()
{

    $current_user   = wp_get_current_user();
    if ($current_user->ID !== 0) {
        $role = $current_user->roles[0];
        if ($role === GYM_MEMBER || $role === GYM_TRAINER) {
            wp_redirect(home_url(), 403);
            exit;
        }
    }
}

/**
 * Adds post state to posts with meta 'inserted: builders'
 */
add_filter('display_post_states', __NAMESPACE__ . '\add_post_state', 10, 2);
function add_post_state($post_states, $post)
{
    $i = get_post_meta($post->ID, 'inserted', true);

    //Only add post states to pages added by our plugin
    if ($i == 'builders-plugin') {
        $post_states[] = 'Builders Plugin';
    }

    return $post_states;
}

/**
 * Handle custom validation for our data
 *
 * @since 1.0.0
 * @access public
 * 
 */
add_action('' . PLUGIN_PREFIX . '_custom_validation_update', __NAMESPACE__ . '\validate_gym_user_data_shared', 10, 2);
add_action('' . PLUGIN_PREFIX . '_custom_validation_register', __NAMESPACE__ . '\validate_gym_user_data_shared', 10, 2);
function validate_gym_user_data_shared($errors, $data)
{
    //check if the 'username' already exists
    if (isset($data[FULL_NAME]) && username_exists(sanitize_key($data[FULL_NAME]))) {
        $errors[] = 'username_exists';
    }
}


/**
 * We must handle updating our gym user after it is inserted
 *
 * @since 1.0.0
 * @access public
 * 
 */
add_action('' . PLUGIN_PREFIX . '_after_success_insert_user', __NAMESPACE__ . '\after_gym_user_insert_success', 10, 3);
function after_gym_user_insert_success($user_id, $data, $role)
{
    //Check if gym users to avoid updating for other user roles
    if ($role === GYM_MEMBER || $role === GYM_TRAINER || $role === GYM_ADMIN) {
        foreach ($data as $key => $val) {
            $safeData = sanitizeGymData($key, $val);
            update_user_meta($user_id, $key, $safeData);
        }
    }
}

function user_id_exists($user_id)
{
    global $wpdb;
    $count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(ID) FROM $wpdb->users WHERE ID = %d", $user_id));
    return empty($count) || 1 > $count ? false : true;
}


function sanitizeGymData($key, $val, $userId = '')
{
    switch ($key) {
        case FULL_NAME:
            return sanitize_text_field($val);
        case IS_STUDENT:
            return absint($val);
        case BRANCH:
            return sanitize_text_field($val);
        case MEMBERSHIP_DURATION:
            //dateToUpdate must be a DateTime obj
            //must return a string in a valid format
            $dateToUpdate = new DateTime('now');
            if (!empty($userId) && user_id_exists($userId)) {
                $dateToUpdate = DateTime::createFromFormat(VALIDDATEFORMAT, get_user_meta($userId, MEMBERSHIP_DURATION, true));
            }
            switch ($val) {
                case THIRTY_DAYS:
                    return date_add($dateToUpdate, date_interval_create_from_date_string(THIRTY_DAYS))->format(VALIDDATEFORMAT);
                case NINETY_DAYS:
                    return date_add($dateToUpdate, date_interval_create_from_date_string(NINETY_DAYS))->format(VALIDDATEFORMAT);
                case HALF_YEAR:
                    return date_add($dateToUpdate, date_interval_create_from_date_string(HALF_YEAR))->format(VALIDDATEFORMAT);
                case ONE_YEAR:
                    return date_add($dateToUpdate, date_interval_create_from_date_string(ONE_YEAR))->format(VALIDDATEFORMAT);
                default: //$val should be date string in ISO format
                    $dateVal = new DateTime($val);
                    if ($dateVal > new DateTime('now')) {
                        return $dateVal->format(VALIDDATEFORMAT);
                    } else {
                        throw new \RuntimeException(Validation::instance()->get_error_message('date_format'), 403);
                    }
            }
    }
}
