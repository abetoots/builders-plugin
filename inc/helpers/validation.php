<?php

namespace Builders_Plugin\Inc\Helpers;

use const Builders_Plugin\Constants\PLUGIN_PREFIX;
use WP_Error;
use DateTime;

if (!defined('ABSPATH')) exit;

class Validation
{

    public function build_errors($errors)
    {
        $wp_error = new WP_Error();
        foreach ($errors as $error) {
            $wp_error->add($error, $this->get_error_message($error));
        }
        return $wp_error;
    }

    /**
     * Validates and saves to the database the new data when updating a user and returns 
     * either a WP_Error or the new data  on success
     * 
     * @param Object $data Associative array containing fields to validate
     * @param String $role The role of the user's new data
     *
     * @return Object|WP_Error The updated data as $newData of the user that was updated, or errors if any.
     */
    public function validate_and_save_new_data_of_user($data)
    {
        $errors = array();

        do_action('' . PLUGIN_PREFIX . '_custom_validation_on_user_update', $errors, $data);

        //return errors if any
        if (!empty($errors)) {
            return $this->build_errors($errors);
        }

        $newData = [];
        do_action('' . PLUGIN_PREFIX . '_custom_save_on_user_update', $errors, $data);

        //return errors if any
        if (!empty($errors)) {
            return $this->build_errors($errors);
        }

        return $newData;
    }

    /**
     * Validates and then completes the new user signup process if all went well.
     * 
     * @param Object $data Associative array containing new user
     *
     * @return int|WP_Error The id of the user that was created, or error if failed.
     */
    public function validate_and_register_new_user($data, $role)
    {
        $errors = array();

        if (empty($role)) {
            $role = 'subscriber';
        }

        //'username' must always be set. 'email' and 'password' can be optional so check only if it is set
        if (empty($data['username']) || isset($data['email']) && empty($data['email']) || isset($data['password']) && empty($data['password'])) {
            $errors[] = 'empty_field';
        }

        //make sure the length of 'username' is not less than 4
        if (4 > strlen($data['username'])) {
            $errors[] = 'username_length';
        }

        //check if the 'username' already exists
        if (username_exists($data['username'])) {
            $errors[] = 'username_exists';
        }

        //make sure the 'username' is valid
        if (!validate_username($data['username'])) {
            $errors[] = 'invalid_username_register';
        }

        //Only if it's set, check if 'email' is valid
        if (isset($data['email']) && !is_email($data['email'])) {
            $errors[] = 'email';
        }

        //Only if it's set, check if email already exists
        if (isset($data['email']) && email_exists($data['email'])) {
            $errors[] = 'email_exists';
        }

        //Do custom validations for this plugin
        do_action('' . PLUGIN_PREFIX . '_custom_validation_on_user_registration', $errors, $data);

        //return errors if any
        if (!empty($errors)) {
            return $this->build_errors($errors);
        }

        //If we reach here, sanitize before inserting user data
        $dbEmail = '';
        if (isset($data['email'])) {
            $dbEmail = sanitize_email($data['email']);
        }

        //WP generated password is default if no password is set
        $dbPassword = wp_generate_password(12, false);
        if (isset($data['password'])) {
            $dbPassword = $data['password'];
        }

        $user_data = array(
            'user_login'    => sanitize_key($data['username']),
            'user_email'    => $dbEmail,
            'user_pass'     => $dbPassword,
            'role'          => $role,
        );
        $user_id = wp_insert_user($user_data);

        do_action('' . PLUGIN_PREFIX . 'after_success_validate_and_register_new_user', $user_id, $data, $role);
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
                return __('Full name is "too short"- that\'s what she said', PLUGIN_PREFIX);

            case 'username_exists':
                return __('Username already exists', PLUGIN_PREFIX);

            case 'invalid_username_register':
                return __(
                    'Somehow that username is invalid. Maybe use a different one?',
                    PLUGIN_PREFIX
                );

            case 'password_length':
                return __('Password is too short- that\'s what she said', PLUGIN_PREFIX);

            case 'email':
                return __('The email address you entered is not valid.', PLUGIN_PREFIX);

            case 'email_exists':
                return __('An account exists with this email address.', PLUGIN_PREFIX);

            case 'date_format':
                return __('Date format invalid', PLUGIN_PREFIX);

            case 'date_exceed':
                return __('Date input exceeded the expected date', PLUGIN_PREFIX);

            case 'date_before':
                return __('Date input must not be before the current date', PLUGIN_PREFIX);

            case 'closed':
                return __('Registering new users is currently not allowed.', PLUGIN_PREFIX);
            case 'disabled':
                return __('Registration is currently not allowed.', PLUGIN_PREFIX);
            case 'captcha':
                return __('The Google reCAPTCHA check failed. Are you a robot?', PLUGIN_PREFIX);

                //Login Error Codes
            case 'invalid_username':
                return __(
                    "Invalid username/email",
                    PLUGIN_PREFIX
                );

            case 'incorrect_password':
                $err = __(
                    "The password you entered wasn't quite right. <a href='%s'>Did you forget your password</a>?",
                    PLUGIN_PREFIX
                );
                return sprintf($err, wp_lostpassword_url());

                //Neutral Error Codes
            case 'empty_field':
                return __('You forgot some fields though. Also, username must be always defined', PLUGIN_PREFIX);

            case 'update_failed':
                return __('Failed to update data. Something went wrong with our servers', PLUGIN_PREFIX);

            default:
                break;
        }

        return __('An unknown error occurred. Please try again later.', PLUGIN_PREFIX);
    }

    /**
     * Validates against a defined correct format
     */
    public function validateDateFormat($date, $format)
    {
        $d = DateTime::createFromFormat($format, $date);
        // The Y ( 4 digits year ) returns TRUE for any integer with any number of digits so changing the comparison from == to === fixes the issue.
        return $d && $d->format($format) === $date;
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
                    'secret' => get_option('' . PLUGIN_PREFIX . '_recaptcha_secret_key'),
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
    }
}
Validation::instance();
