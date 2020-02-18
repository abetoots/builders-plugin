<?php

namespace Builders_Plugin\Inc\Core\Logout;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Do our own logout redirects before WordPress redirects the user back to wp-login.php.
 * Redirect to custom login page ONLY if current user are gym staff
 */
// add_action('logout', __NAMESPACE__ . '\redirect_after_logout');
// function redirect_after_logout()
// {
//     $user = wp_get_current_user();
//     $role_name = $user->roles[0];
//     if ($role_name === 'gym_trainer' || $role_name === 'gym_admin') {
//         $redirect_url = home_url('sign-in?logged_out=true');
//         wp_safe_redirect($redirect_url);
//         exit;
//     }
// }

add_action('wp_ajax_react_app_logout_hook', __NAMESPACE__ . '\handle_logout_response');
add_action('wp_ajax_nopriv_react_app_logout_hook', __NAMESPACE__ . '\handle_logout_response');

function handle_logout_response()
{
    if (!check_ajax_referer('wpreact_logout_nonce', 'react_query_wpnonce')) {
        wp_die('Meh');
    };

    wp_logout();
    //handles wp_die or die
    wp_send_json_success();
}
