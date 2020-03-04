<?php

namespace Builders_Plugin\Inc\Core;

use Builders_Plugin\Inc\Helpers\Validation;
use WP_Error;

use const Builders_Plugin\Constants\ACTION_REGISTER_GYM_MEMBER;
use const Builders_Plugin\Constants\FULL_NAME;
use const Builders_Plugin\Constants\BRANCH;
use const Builders_Plugin\Constants\GYM_ROLE;
use const Builders_Plugin\Constants\MEMBERSHIP_DURATION;
use const Builders_Plugin\Constants\PLUGIN_PREFIX;
use const Builders_Plugin\Constants\IS_STUDENT;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Member_Registration
{
    /**
     * A shortcode for rendering the new user registration form.
     *
     * @since 1.0.0
     * @access public
     * 
     * @param  array   $attributes  Shortcode attributes.
     * @param  string  $content     The text content for shortcode. Not used.
     *
     * @return string  The shortcode output
     */
    public function render_registration_form($user_attributes, $content = null)
    {
        // normalize attribute keys, lowercase
        $user_attributes = array_change_key_case((array) $user_attributes, CASE_LOWER);
        // Parse shortcode attributes
        $default_attributes = array(
            'title'         => 'Please Enter Information <span role=img>📋</span>:',
            'button_text'   => 'Register',
            'disabled'      => false,
        );
        $attributes = shortcode_atts($default_attributes, $user_attributes);

        // Retrieve and assign recaptcha key to array
        $attributes['recaptcha_site_key'] = get_option('' . PLUGIN_PREFIX . '_recaptcha_site_key', null);

        if (isset($_REQUEST['success'])) {
            $attributes['success'] = true;
        }

        if (!get_option('allow_portal_registration')) {
            return __('Registering new users is currently not allowed.', 'builders-plugin');
        } elseif (is_user_logged_in()) {
            return 'You are logged in';
        } else {

            // Retrieve possible errors from request parameters
            $attributes['errors'] = array();
            if (isset($_REQUEST['registration-err'])) {
                $error_codes = explode(',', $_REQUEST['registration-err']);

                foreach ($error_codes as $error_code) {
                    $attributes['errors'][] = Validation::instance()->get_error_message($error_code);
                }
            }

            // Rendering of html is done here
            return Validation::instance()->get_template_html('reg_form_gym_member', $attributes);
        }
    }

    /**
     * Handles form when submitted to admin-post.php
     *
     * @since 1.0.0
     * @access public
     * 
     *
     * @uses $this->do_register_jobseeker();
     */
    public function handle_form_response()
    {
        if (!isset($_POST['register_gym_member_nonce'])) {
            wp_die('first');
        }

        if (!wp_verify_nonce($_POST['register_gym_member_nonce'], 'gym_member_reg_form_nonce')) {
            wp_die('second');
        }

        if (is_user_logged_in()) { //prevent submitting of registration form when logged in
            return;
        }

        $data = array(
            'username'  => $_POST[FULL_NAME],
            FULL_NAME    => $_POST[FULL_NAME],
            IS_STUDENT     => $_POST[IS_STUDENT],
            BRANCH      => $_POST[BRANCH],
            MEMBERSHIP_DURATION => $_POST[MEMBERSHIP_DURATION]
        );

        //Handles validation and redirect
        $this->do_register_member($data);
    }

    /**
     * Handles the registration of a new user.
     *
     * @since 1.0.0
     * @access public
     * 
     * 
     * @uses $this->validate_and_register_new_user()
     */
    public function do_register_member($data)
    {
        $redirect_url = home_url('registration');
        $errors = new WP_Error();

        if (!get_option('allow_portal_registration')) {
            // Portal registration disabled, display error
            $redirect_url = add_query_arg('registration-err', 'disabled', $redirect_url);
        } elseif (get_option('' . PLUGIN_PREFIX . '_recaptcha_site_key') && get_option('' . PLUGIN_PREFIX . '_recaptcha_secret_key')) {
            if (!Validation::instance()->verify_recaptcha()) {
                //Recaptcha check failed, display error
                $redirect_url = add_query_arg('registration-err', 'captcha', $redirect_url);
            }
        } else {
            //either an error or the user id
            $result = Validation::instance()->validate_and_register_new_user(
                $data,
                'gym_member'
            );

            if (is_wp_error($result)) {
                // Parse errors into a string and append as parameter to redirect
                $errors = join(',', $result->get_error_codes());
                $redirect_url = add_query_arg('registration-err', $errors, $redirect_url);
            } else {
                // Success, redirect to login page.
                $redirect_url = home_url('registration');
                $redirect_url = add_query_arg('success', $data[FULL_NAME], $redirect_url);
            }
        }
        wp_redirect($redirect_url);
        exit;
    }


    /**
     * An action function used to include the reCAPTCHA JavaScript file
     * at the end of the page.
     */
    public function add_captcha_js_to_footer()
    {
        echo "<script src='https://www.google.com/recaptcha/api.js'></script>";
    }

    public static $metas = [
        FULL_NAME => [
            'type'  => 'string',
            'rest'  => false,
            'obj_type'  => 'User',
        ],
        IS_STUDENT  => [
            'type'  => 'number',
            'rest'  => false,
            'obj_type'  => 'User',
        ],
        GYM_ROLE  => [
            'type'  => 'string',
            'rest'  => false,
            'obj_type'  => 'User'
        ],
        BRANCH  => [
            'type'  => 'string',
            'rest'  => false,
            'obj_type'  => 'User'
        ],
        MEMBERSHIP_DURATION => [
            'type'  => 'string',
            'rest'  => false,
            'obj_type'  => 'User'
        ]
    ];

    /**
     * Define user metas and show in REST API
     * see https://docs.wpgraphql.com/getting-started/custom-fields-and-meta/
     */

    private function register_user_metas()
    {
        foreach (self::$metas as $key => $val) {
            register_meta($val['obj_type'], $key, array(
                "type" => $val['type'],
                "show_in_rest" => $val['rest']
            ));
        }
    }

    public function register_user_metas_in_wpgraphql()
    {
        foreach (self::$metas as $key => $val) {
            register_graphql_field($val['obj_type'], $key, array(
                //The schema only has 'Int' type, everything else convert to uppercase
                'type' => $val['type'] === 'number' ? 'Int' : strtoupper($val['type']),
                'resolve' => function ($obj, $dunno, $app_context, $resolve_info) {
                    if ($resolve_info->fieldName === GYM_ROLE) {
                        $role = get_userdata($obj->userId)->roles[0];
                        if ($role === 'gym_member' || $role === 'gym_trainer' || $role === 'gym_admin' || $role === 'administrator') {
                            return $role;
                        }
                    } elseif ($resolve_info->returnType->name === 'Int') {
                        $return = get_user_meta($obj->userId, $resolve_info->fieldName, true) || 0;
                        return $return;
                    } else {
                        $return = get_user_meta($obj->userId, $resolve_info->fieldName, true);
                        return $return;
                    }
                }
            ));
        }
    }

    /**
     * Instance
     *
     * @since 1.0.0
     * @access private
     * @static
     *
     * @var Plugin The single instance of the class.
     */
    private static $_instance = null;

    /**
     * Instance
     *
     * Ensures only one instance of the class is loaded or can be loaded.
     *
     * @since 1.0.0
     * @access public
     *
     * @return Plugin An instance of the class.
     */
    public static function instance()
    {
        if (!self::$_instance)
            self::$_instance = new self();
        return self::$_instance;
    }

    /**
     *  Plugin class constructor
     *
     * @since 1.0.0
     * @access public
     */
    public function __construct()
    {
        $this->init();
    }

    /**
     *  Init function that handles all hooks and filters
     * 
     * @since 1.0.0
     * @access public
     */
    public function init()
    {
        //Register user metas
        $this->register_user_metas();
        add_action('graphql_register_types', array($this, 'register_user_metas_in_wpgraphql'));
        //Registration
        add_shortcode('builders-reg-form-member', array($this, 'render_registration_form'));

        // Add captcha javascript to footer
        add_action('wp_print_footer_scripts', array($this, 'add_captcha_js_to_footer'));

        // Handle form response
        add_action('admin_post_nopriv_' . ACTION_REGISTER_GYM_MEMBER, array($this, 'handle_form_response'));
    }
}
Member_Registration::instance();
