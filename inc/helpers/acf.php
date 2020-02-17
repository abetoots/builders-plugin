<?php

namespace Builders_Plugin\Inc\Helpers\ACF;


if (!defined('ABSPATH')) {
    exit;
}


/**
 * Adds new rule types/locations to the 'User' group
 */
add_filter('acf/location/rule_types', __NAMESPACE__ . '\add_new_rules_to_user_group');
function add_new_rules_to_user_group($choices)
{

    $choices['User']['capabilities'] = 'Capabilities';
    $choices['User']['profile_form'] = 'Profile';

    return $choices;
}

/**
 * The choices of the rule type: capabilities
 */
add_filter('acf/location/rule_values/capabilities', __NAMESPACE__ . '\values_of_capabilities_rule');
function values_of_capabilities_rule($choices)
{
    $caps  = get_role('administrator')->capabilities;
    if ($caps) {
        foreach ($caps as $key => $cap) {
            $choices[$key] = $key;
        }
    }

    return $choices;
}

/**
 * We want to show field groups, regardless of which edit screen we are viewing, if the 
 * current user matches the capability we set as a rule
 * @param Boolean $match The true / false variable which must be returned
 * @param Array $rule The true / false variable which must be returned
 * @param Array $options The current edit screen (user, post, page)
 */
add_filter('acf/location/rule_match/capabilities', __NAMESPACE__ . '\show_fields_if_user_has_right_capability', 10, 4);
function show_fields_if_user_has_right_capability($match, $rule, $options, $field_group)
{
    // error_log(print_r($options, 1));
    /**
     * We ran into errors with current_user_can() returning false even if we are an administrator with all the capabilities.
     * Turns out this was because current_user_can() is not reliable without passing the ID of the object to check against.
     * To get reliable matches whichever edit screen we are on, we access the current edit screen/object's ID ($options id) 
     * BUT this is different with different edit screens. For an object/edit screen that is related to users , this is 'user_id'
     * For screens related to posts, this is 'post_id'
     */
    if ($rule['operator'] == "==") {
        if (array_key_exists('user_id', $options)) {
            $match = current_user_can($rule['value'], $options['user_id']);
        } elseif (array_key_exists('post_id', $options)) {
            $match = current_user_can($rule['value'], $options['post_id']);
        }
    } elseif ($rule['operator'] == "!=") {
        if (array_key_exists('user_id', $options)) {
            $match = current_user_can($rule['value'], $options['user_id']);
        } elseif (array_key_exists('post_id', $options)) {
            $match = current_user_can($rule['value'], $options['post_id']);
        }
    }
    return $match;
}

/**
 * The choices of the rule type: profile_form
 */
add_filter('acf/location/rule_values/profile_form', __NAMESPACE__ . '\values_of_profile_rule');
function values_of_profile_rule($choices)
{
    $choices['add'] = 'Add New';
    $choices['edit'] = 'Your Profile';
    return $choices;
}

/**
 * We want to show field groups if the edit screen we are viewing is either 'add' or'edit' user
 * @param Boolean $match The true / false variable which must be returned
 * @param Array $rule The true / false variable which must be returned
 * @param Array $options The current edit screen (user, post, page)
 */
add_filter('acf/location/rule_match/profile_form', __NAMESPACE__ . '\show_fields_if_screen_is_edit_or_add_user', 10, 4);
function show_fields_if_screen_is_edit_or_add_user($match, $rule, $options, $field_group)
{

    if ($rule['operator'] == "==") {
        if (array_key_exists('user_form', $options)) {
            $match = $options['user_form'] === $rule['value'];
        }
    } elseif ($rule['operator'] == "!=") {
        if (array_key_exists('user_form', $options)) {
            $match = $options['user_form'] !== $rule['value'];
        }
    }
    return $match;
}


/**
 * Local JSON save point
 */

add_filter('acf/settings/save_json', __NAMESPACE__ . '\my_acf_json_save_point');
function my_acf_json_save_point($path)
{
    // update path
    $path = BUILDERS_PLUGIN_DIR . '/libraries/acf-json';
    return $path;
}


/**
 * Local JSON load point
 */
add_filter('acf/settings/load_json', __NAMESPACE__ . '\my_acf_json_load_point');
function my_acf_json_load_point($paths)
{
    // remove original path (optional)
    unset($paths[0]);
    // append path
    $paths[] =  BUILDERS_PLUGIN_DIR . '/libraries/acf-json';
    return $paths;
}

//TODO maybe register fields programmatically
