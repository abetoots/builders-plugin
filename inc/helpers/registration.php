<?php

namespace Builders_Plugin\Inc\Helpers;

use WP_Error;
use DateTime;
use WPGraphQL;

if (!defined('ABSPATH')) exit;

const VALIDDATEFORMAT = 'Ymd';

class Registration
{
    /**
     * Define the metas to be registered in register_meta and register_graphql_field
     */
    public static $metas = array(
        'full_name' => array(
            'type'  => 'string',
            'rest'  => false,
            'obj_type'  => 'User',
        ),
        'gender'    => array(
            'type'  => 'string',
            'rest'  => false,
            'obj_type'  => 'User',
        ),
        'birthdate' => array(
            'type'  => 'string',
            'rest'  => false,
            'obj_type'  => 'User',
        ),
        'is_student'  => array(
            'type'  => 'number',
            'rest'  => false,
            'obj_type'  => 'User',
        ),
        'gym_role'  => array(
            'type'  => 'string',
            'rest'  => false,
            'obj_type'  => 'User'
        ),
        'branch'  => array(
            'type'  => 'string',
            'rest'  => false,
            'obj_type'  => 'User'
        ),
    );

    /**
     * Define user metas and show in REST API
     * TODO register our fields to the WPGraphQL Schema
     * see https://docs.wpgraphql.com/getting-started/custom-fields-and-meta/
     */

    private function register_user_metas()
    {
        foreach ($this::$metas as $key => $val) {
            register_meta($val['obj_type'], $key, array(
                "type" => $val['type'],
                "show_in_rest" => $val['rest']
            ));
        }
    }

    public function register_user_metas_in_wpgraphql()
    {
        foreach ($this::$metas as $key => $val) {
            register_graphql_field($val['obj_type'], $key, array(
                //The schema only has 'Int' type, everything else convert to uppercase
                'type' => $val['type'] === 'number' ? 'Int' : strtoupper($val['type']),
                'resolve' => function ($obj, $dunno, $app_context, $resolve_info) {
                    if ($resolve_info->fieldName === 'gym_role') {
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
     * Validates and then completes the new employer signup process if all went well.
     *
     * @param string $fullname          The new member's full name
     * @param string $email             The new member's email address to be used also as username
     * @param string $gender            The new member's gender
     *
     * @return int|WP_Error         The id of the user that was created, or error if failed.
     */
    public function validate_and_register_new_user(
        $fullname,
        $email,
        $gender,
        $birthdate,
        $isStudent,
        $branch,
        $role
    ) {
        $errors = array();

        if (empty($fullname) || empty($email) || empty($gender) || empty($birthdate) || empty($branch)) {
            $errors = 'empty_field';
        }

        //Make sure the number of full name characters is not less than 4
        if (4 > strlen($fullname)) {
            $errors[] = 'username_length';
        }

        //Check if the username is already registered
        if (username_exists($email)) {
            $errors[] = 'username_exists';
        }

        //Make sure the username is valid
        if (!validate_username($email)) {
            $errors[] = 'invalid_username_register';
        }

        //Check if valid email
        if (!is_email($email)) {
            $errors[] = 'email';
        }

        //Check if email is already registered
        if (email_exists($email)) {
            $errors[] = 'email_exists';
        }

        //Validate date against a defined format 
        if (!$this->validateDateFormat($birthdate)) {
            $errors[] = 'birthdate_format';
        }

        //Birthdate should not exceed current date
        if ($birthdate > date(VALIDDATEFORMAT)) {
            $errors[] = 'birthdate_exceed';
        }

        //return errors if any
        if (!empty($errors)) {
            $wp_error = new WP_Error();
            foreach ($errors as $error) {
                $wp_error->add($error, $this->get_error_message($error));
            }
            return $wp_error;
        }

        //If we reach here, Sanitize before inserting user data
        $email = sanitize_email($email);
        $login = sanitize_key($fullname);
        $fullname = sanitize_text_field($fullname);
        $gender = sanitize_text_field($gender);
        $birthdate = date(VALIDDATEFORMAT, strtotime($birthdate));
        $isStudent = absint($isStudent);
        $branch = sanitize_text_field($branch);
        // Generate the password so that the subscriber will have to check email...
        $password = wp_generate_password(12, false);
        $user_data = array(
            'user_login'    => $login,
            'user_email'    => $email,
            'user_pass'     => $password,
            'role'          => $role,
        );

        $user_id = wp_insert_user($user_data);
        update_user_meta($user_id, 'full_name', $fullname);
        update_user_meta($user_id, 'gender', $gender);
        update_user_meta($user_id, 'birthdate', $birthdate);
        update_user_meta($user_id, 'is_student', $isStudent);
        update_user_meta($user_id, 'branch', $branch);
        //wp_new_user_notification( $user_id, $password );

        return $user_id;
    }

    /**
     * Instead of manually inputting an error message at a given WP_Error instance,
     * we outsource and handle it all in this function.
     * 
     * Finds and returns a matching error message for the given error code.
     *
     * @param string $error_code    The error code to look up.
     *
     * @return string               An error message.
     */
    public function get_error_message($error_code)
    {
        switch ($error_code) {
                //Registration Error Codes
            case 'username_length':
                return __('Full name is "too short"- that\'s what she said', 'builders-plugin');

            case 'username_exists':
                return __('Username already exists', 'builders-plugin');

            case 'invalid_username_register':
                return __(
                    'Somehow that username is invalid. Maybe use a different one?',
                    'builders-plugin'
                );

            case 'password_length':
                return __('Password is too short- that\'s what she said', 'builders-plugin');

            case 'email':
                return __('The email address you entered is not valid.', 'builders-plugin');

            case 'email_exists':
                return __('An account exists with this email address.', 'builders-plugin');

            case 'birthdate_format':
                return __('Birthdate format invalid', 'builders-plugin');

            case 'birthdate_exceed':
                return __('Looks like you were born in the future', 'builders-plugin');

            case 'closed':
                return __('Registering new users is currently not allowed.', 'builders-plugin');
            case 'disabled':
                return __('Registration is currently not allowed.', 'builders-plugin');
            case 'captcha':
                return __('The Google reCAPTCHA check failed. Are you a robot?', 'builders-plugin');


                //Login Error Codes
            case 'invalid_username':
                return __(
                    "Invalid username/email",
                    'builders-plugin'
                );

            case 'incorrect_password':
                $err = __(
                    "The password you entered wasn't quite right. <a href='%s'>Did you forget your password</a>?",
                    'builders-plugin'
                );
                return sprintf($err, wp_lostpassword_url());

                //Neutral Error Codes
            case 'empty_field':
                return __('You forgot some fields though', 'builders-plugin');

            default:
                break;
        }

        return __('An unknown error occurred. Please try again later.', 'builders-plugin');
    }

    /**
     * Validates against a defined correct format
     */
    public function validateDateFormat($date, $format = VALIDDATEFORMAT)
    {
        $d = DateTime::createFromFormat($format, $date);
        // The Y ( 4 digits year ) returns TRUE for any integer with any number of digits so changing the comparison from == to === fixes the issue.
        return $d && $d->format($format) === $date;
    }

    /**
     * Renders the contents of the given template to a string and returns it.
     *
     * @param string $template_name The name of the template to render (without .php)
     * @param array  $attributes    The PHP variables for the template
     *
     * @return string               The contents of the template.
     */
    public function get_template_html($template_name, $attributes = null)
    {
        if (!$attributes) {
            $attributes = array();
        }

        /**
         * Notes:
         * The output buffer collects everything that is printed between 
         * ob_start and ob_end_clean so that it can then be retrieved as a string using ob_get_contents.
         * 
         * Notes: the do actions are called by add action, gives chance to other devs to add further customizations
         */
        ob_start();

        do_action('builders_plugin_customize_reg_before_' . $template_name);

        require(BUILDERS_PLUGIN_DIR . 'frontend/html-templates/' . $template_name . '.php');

        do_action('builders_plugin_customize_reg_after_' . $template_name);

        $html = ob_get_contents();
        ob_end_clean();

        return $html;
    }

    /**
     * Checks that the reCAPTCHA parameter sent with the registration
     * request is valid.
     *
     * @return bool True if the CAPTCHA is OK, otherwise false.
     */
    public function verify_recaptcha()
    {
        // This field is set by the recaptcha widget if check is successful
        if (isset($_POST['g-recaptcha-response'])) {
            $captcha_response = $_POST['g-recaptcha-response'];
        } else {
            return false;
        }

        // Verify the captcha response from Google
        $response = wp_remote_post(
            'https://www.google.com/recaptcha/api/siteverify',
            array(
                'body' => array(
                    'secret' => get_option('builders_plugin_recaptcha_secret_key'),
                    'response' => $captcha_response
                )
            )
        );

        $success = false;
        if ($response && is_array($response)) {
            $decoded_response = json_decode($response['body']);
            $success = $decoded_response->success;
        }

        return $success;
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
     * Register plugin action hooks and filters
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
        $this->register_user_metas();
        add_action('graphql_register_types', array($this, 'register_user_metas_in_wpgraphql'));
    }
}
Registration::instance();
