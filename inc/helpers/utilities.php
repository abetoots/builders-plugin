<?php

namespace Builders_Plugin\Inc\Helpers\Utilities;


if (!defined('ABSPATH')) exit;


add_action('wp_enqueue_scripts', __NAMESPACE__ . '\enqueue_scripts_frontend');
function enqueue_scripts_frontend()
{
    //versioning for cache busting: date('ymd-Gis', filemtime(dirpath . 'relativepath'))
    wp_enqueue_style(
        'builders-plugin-styles',
        BUILDERS_PLUGIN_URL . '/frontend/styles/styles.min.css',
        array(),
        date('ymd-Gis', filemtime(BUILDERS_PLUGIN_DIR . '/frontend/styles/styles.min.css')),
        'all'
    );
    if (is_page('registration')) {
        wp_enqueue_script(
            'builders-plugin-input',
            BUILDERS_PLUGIN_URL . '/frontend/js/input.js',
            array(),
            date('ymd-Gis', filemtime(BUILDERS_PLUGIN_DIR . '/frontend/js/input.js')),
            true
        );
        wp_enqueue_script(
            'datepicker.js',
            BUILDERS_PLUGIN_URL . '/frontend/third-party/flatpickr.min.js',
            array('jquery'),
            date('ymd-Gis', filemtime(BUILDERS_PLUGIN_DIR . '/frontend/third-party/flatpickr.min.js')),
            true
        );
        wp_enqueue_style(
            'datepickerjs-styles',
            BUILDERS_PLUGIN_URL . '/frontend/third-party/flatpickr.min.css',
            array(),
            date('ymd-Gis', filemtime(BUILDERS_PLUGIN_DIR . '/frontend/styles/datepicker.min.css')),
            'all'
        );
        wp_enqueue_script(
            'builders-plugin-datepicker',
            BUILDERS_PLUGIN_URL . '/frontend/js/form-datepicker.js',
            array('jquery'),
            date('ymd-Gis', filemtime(BUILDERS_PLUGIN_DIR . '/frontend/js/form-datepicker.js')),
            true
        );
    }

    if (isset($_REQUEST['success']) && isset($_REQUEST['gend'])) {
        //whenever there's a successful registration, add a timeout script to remove the notification after a few seconds
        wp_enqueue_script('builders-plugin-notification', BUILDERS_PLUGIN_URL . '/frontend/js/notification.js', array('jquery'), false, true);
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

    if ($role === 'gym_member' || $role === 'gym_trainer') {
        update_user_meta($user_id, 'show_admin_bar_front', 'false');
    }

    return $user_id;
}

/**
 * Redirects users based on their role
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
        if ($role === 'gym_member' || $role === 'trainer') {
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
