<?php

namespace Builders_Plugin\Inc\Core\Javascript_App;

use WP_Error;
use WPGraphQL;

use Builders_Plugin\Inc\Helpers\Validation;

use function Builders_Plugin\Inc\Core\GraphQl\get_gym_member_graphql;

use const Builders_Plugin\Constants\ACTION_AJAX_REGISTER_GYM_MEMBER;
use const Builders_Plugin\Constants\FULL_NAME;
use const Builders_Plugin\Constants\BRANCH;
use const Builders_Plugin\Constants\MEMBERSHIP_DURATION;
use const Builders_Plugin\Constants\PLUGIN_PREFIX;
use const Builders_Plugin\Constants\IS_STUDENT;


if (!defined('ABSPATH')) exit; // Exit if accessed directly

//TODO maybe replace with GraphQL mutation
/**
 * Handles form when submitted from an external app to admin-ajax.php
 *
 * @since 1.0.0
 * @uses $this->do_register_member();
 */
add_action('wp_ajax_nopriv_' . ACTION_AJAX_REGISTER_GYM_MEMBER, __NAMESPACE__ . '\handle_form_response_app');
add_action('wp_ajax_' . ACTION_AJAX_REGISTER_GYM_MEMBER, __NAMESPACE__ . '\handle_form_response_app');
function handle_form_response_app()
{

    if (!is_user_logged_in()) {
        wp_send_json_error('403', 'Forbidden');
    }

    if (!current_user_can('create_gym_member')) {
        wp_send_json_error('403', 'Wrong capabilities');
    }

    $data = array(
        'username'  => $_REQUEST[FULL_NAME],
        FULL_NAME    => $_REQUEST[FULL_NAME],
        IS_STUDENT     => $_REQUEST[IS_STUDENT],
        BRANCH      => $_REQUEST[BRANCH],
        MEMBERSHIP_DURATION => $_REQUEST[MEMBERSHIP_DURATION]
    );

    //Handles validation and redirect
    do_register_member($data);
}

/**
 * Handles the registration of a new user from an external app
 *
 * @since 1.0.0
 * 
 * @uses validate_and_register_new_user()
 */

function do_register_member($data)
{

    if (!get_option('allow_portal_registration')) {
        // Portal registration disabled, return json error
        //automatically dies
        wp_send_json_error(new WP_Error('403', Validation::instance()->get_error_message('disabled')));
    } elseif (get_option('' . PLUGIN_PREFIX . '_recaptcha_site_key') && get_option('' . PLUGIN_PREFIX . '_recaptcha_secret_key')) {
        if (!Validation::instance()->verify_recaptcha()) {
            //Recaptcha check failed, return json error
            wp_send_json_error(new WP_Error('403', Validation::instance()->get_error_message('captcha')));
        }
    } else {
        //either an error or the user id
        $result = Validation::instance()->validate_and_register_new_user(
            $data,
            'gym_member'
        );

        if (is_wp_error($result)) {
            wp_send_json_error($result);
            // return new WP_HTTP_Response($result, 403);
        } else {
            //send back the user data as result
            $user = get_gym_member_graphql($result);
            wp_send_json_success($user['data']);
        }
    }
    exit;
}
