

<?php

namespace Builders_Plugin\Inc\Core\Validation;

use Builders_Plugin\Inc\Helpers\Validation;
use DateTime;

use function Builders_Plugin\Inc\Helpers\user_id_exists;

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
use const Builders_Plugin\Constants\VALIDMEMBERSHIPFORMAT;

/**
 * Handle custom validation for our data
 *
 * @since 1.0.0
 * @access public
 * 
 */
add_action('' . PLUGIN_PREFIX . '_custom_validation_on_user_update', __NAMESPACE__ . '\validate_gym_user_data_shared', 10, 3);
add_action('' . PLUGIN_PREFIX . '_custom_validation_on_user_registration', __NAMESPACE__ . '\validate_gym_user_data_shared', 10, 3);
function validate_gym_user_data_shared($errors, $data, $role)
{
    if ($role === GYM_MEMBER || $role === GYM_TRAINER || $role === GYM_ADMIN) {
        //check if the 'username' already exists
        if (isset($data[FULL_NAME]) && username_exists(sanitize_key($data[FULL_NAME]))) {
            $errors[] = 'username_exists';
        }
    }
}

add_action('' . PLUGIN_PREFIX . '_custom_save_on_user_update', __NAMESPACE__ . '\sanitize_before_saving', 4);
function sanitize_before_saving($errors, $data, $role, $newData)
{
    if ($role === GYM_MEMBER) {
        foreach ($data as $key => $val) {
            $safeData = sanitizeGymData($key, $val, $data['userId']);
            $success = update_user_meta($data['userId'], $key, $safeData);
            if ($success) {
                $newData[$key] = $safeData;
            } else {
                $errors[] = 'update_failed';
            }
        }
    }
}

/**
 * We must handle updating our gym user after it is inserted
 *
 * @since 1.0.0
 * @access public
 * 
 */
add_action('' . PLUGIN_PREFIX . 'after_success_validate_and_register_new_user', __NAMESPACE__ . '\update_gym_users', 10, 3);
function update_gym_users($user_id, $data, $role)
{
    //Check if gym users to avoid updating for other user roles
    if ($role === GYM_MEMBER || $role === GYM_TRAINER || $role === GYM_ADMIN) {
        foreach ($data as $key => $val) {
            $safeData = sanitizeGymData($key, $val, '');
            update_user_meta($user_id, $key, $safeData);
        }
    }

    if ($role === GYM_MEMBER || $role === GYM_TRAINER) {
        update_user_meta($user_id, 'show_admin_bar_front', 'false');
    }
}


//If a user ID is passed in, this function will expect
//to sanitize a value based on a previous value from the database
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
                $dateToUpdate = DateTime::createFromFormat(VALIDMEMBERSHIPFORMAT, get_user_meta($userId, MEMBERSHIP_DURATION, true));
            }
            switch ($val) {
                case THIRTY_DAYS:
                    return date_add($dateToUpdate, date_interval_create_from_date_string(THIRTY_DAYS))->format(VALIDMEMBERSHIPFORMAT);
                case NINETY_DAYS:
                    return date_add($dateToUpdate, date_interval_create_from_date_string(NINETY_DAYS))->format(VALIDMEMBERSHIPFORMAT);
                case HALF_YEAR:
                    return date_add($dateToUpdate, date_interval_create_from_date_string(HALF_YEAR))->format(VALIDMEMBERSHIPFORMAT);
                case ONE_YEAR:
                    return date_add($dateToUpdate, date_interval_create_from_date_string(ONE_YEAR))->format(VALIDMEMBERSHIPFORMAT);
                default: //$val should be date string in ISO format
                    $dateVal = new DateTime($val);
                    if ($dateVal > new DateTime('now')) {
                        return $dateVal->format(VALIDMEMBERSHIPFORMAT);
                    } else {
                        throw new \RuntimeException(Validation::instance()->get_error_message('date_format'), 403);
                    }
            }
            break;
        default:
            return;
    }
}
