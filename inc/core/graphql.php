<?php

namespace Builders_Plugin\Inc\Core\GraphQl;

use Builders_Plugin\Inc\Helpers\Validation as ValidationHelper;

use WP_Error;
use WPGraphQL;

use function Builders_Plugin\Inc\Core\Utilities\sanitizeGymData;

use const Builders_Plugin\Constants\BRANCH;
use const Builders_Plugin\Constants\FULL_NAME;
use const Builders_Plugin\Constants\GYM_ADMIN;
use const Builders_Plugin\Constants\GYM_MEMBER;
use const Builders_Plugin\Constants\GYM_USER_ALLOWED_EDITABLE_FIELDS;
use const Builders_Plugin\Constants\GYM_MEMBER_FIELDS;
use const Builders_Plugin\Constants\GYM_ROLE;
use const Builders_Plugin\Constants\GYM_TRAINER;
use const Builders_Plugin\Constants\HALF_YEAR;
use const Builders_Plugin\Constants\IS_STUDENT;
use const Builders_Plugin\Constants\MEMBERSHIP_DURATION;
use const Builders_Plugin\Constants\NINETY_DAYS;
use const Builders_Plugin\Constants\ONE_YEAR;
use const Builders_Plugin\Constants\PLUGIN_PREFIX;
use const Builders_Plugin\Constants\THIRTY_DAYS;

if (!defined('ABSPATH')) exit; //Exit if accessed directly

function get_gym_member_graphql($id)
{
    return graphql([
        'query' => ' {
                        user(id: "' . $id . '", idType: DATABASE_ID) 
                        {
                            ' . GYM_MEMBER_FIELDS . '
                        }
                    }'
    ]);
}

add_action('graphql_register_types', __NAMESPACE__ . '\register_new_types');
function register_new_types()
{
    //Gym roles enum
    register_graphql_enum_type('GymRolesEnum', [
        'description' => __('List of available gym roles', PLUGIN_PREFIX),
        'values' => [
            'GYM_MEMBER' => [
                'value' => GYM_MEMBER
            ],
            'GYM_TRAINER' => [
                'value' => GYM_TRAINER
            ],
            'GYM_ADMIN' => [
                'value' => GYM_ADMIN
            ],
        ],
    ]);

    //Membership duration preset
    register_graphql_enum_type('MembershipDurationPresetsEnum', [
        'description' => __('Membership duration presets. Will be added to current date', PLUGIN_PREFIX),
        'values' => [
            'THIRTY_DAYS' => [
                'value' => THIRTY_DAYS
            ],
            'NINETY_DAYS' => [
                'value' => NINETY_DAYS
            ],
            'HALF_YEAR' => [
                'value' => HALF_YEAR
            ],
            'ONE_YEAR' => [
                'value' => ONE_YEAR
            ]
        ],
    ]);
}

# This is the action that is executed as the GraphQL Schema is being built.
add_action('graphql_register_types', __NAMESPACE__ . '\register_custom_mutations');
function register_custom_mutations()
{

    # This function registers a mutation to the Schema.
    # The first argument, in this case `exampleMutation`, is the name of the mutation in the Schema
    # The second argument is an array to configure the mutation.
    # The config array accepts 3 key/value pairs for: inputFields, outputFields and mutateAndGetPayload.
    register_graphql_mutation('updateGymUser', [

        # inputFields expects an array of Fields to be used for inputtting values to the mutation
        'inputFields' => [
            'userId' => [
                'type' => 'Int',
                'description' => __('Database ID of the user we want to update ', PLUGIN_PREFIX),
            ],
            FULL_NAME           => [
                'type'          => 'String',
                'description'   => __('Full name of the gym user to be updated', PLUGIN_PREFIX)
            ],
            MEMBERSHIP_DURATION => [
                'type'          => 'String',
                'description'   => __('Extension to the membership duration', PLUGIN_PREFIX)
            ]
        ],

        # outputFields expects an array of fields that can be asked for in response to the mutation
        # the resolve function is optional, but can be useful if the mutateAndPayload doesn't return an array
        # with the same key(s) as the outputFields
        'outputFields' => [
            FULL_NAME           => [
                'type'          => 'String',
                'description'   => __('Full name of the gym user to be updated', PLUGIN_PREFIX)
            ],
            MEMBERSHIP_DURATION => [
                'type'          => 'String',
                'description'   => __('Extension to the membership duration', PLUGIN_PREFIX)
            ]
        ],

        # mutateAndGetPayload expects a function, and the function gets passed the $input, $context, and $info
        # the function should return enough info for the outputFields to resolve with
        'mutateAndGetPayload' => function ($input, $context, $info) {
            if (!is_user_logged_in()) {
                return new WP_Error('403', 'Unauthenticated');
            }
            // Do any logic here to sanitize the input, check user capabilities, etc
            if (!current_user_can('update_gym_member')) {
                return new WP_Error('403', 'Forbidden capabilities');
            }
            if (empty($input['userId'])) {
                return new WP_Error('403', 'Required inputs are empty');
            }

            //to be returned
            $output = [];
            //for validation
            $data = [];
            $data['userId'] = $input['userId'];
            foreach (GYM_USER_ALLOWED_EDITABLE_FIELDS as $key) {
                //for each editable field that is defined in our $input, we want to validate it
                if (!empty($input[$key])) {
                    //we then build a $data associative array ... 
                    //... so we can pass it through the same validation helper when registering a gym user
                    $data[$key] = $input[$key];
                }
            }

            $result = ValidationHelper::instance()->validate_and_update_user($data);
            if (is_wp_error($result)) {
                return $result;
            }
            //if validation is successful, we'll reach below
            //we get the same $data we passed in so we're sure we only update metas that are not empty
            foreach ($data as $key => $val) {
                $safeData = sanitizeGymData($key, $val, $data['userId']);
                $success = update_user_meta($data['userId'], $key, $safeData);
                if ($success) {
                    $output[$key] = $safeData;
                }
            }

            return $output;
        }
    ]);

    # This function registers a mutation to the Schema.
    # The first argument, in this case `exampleMutation`, is the name of the mutation in the Schema
    # The second argument is an array to configure the mutation.
    # The config array accepts 3 key/value pairs for: inputFields, outputFields and mutateAndGetPayload.
    register_graphql_mutation('createGymUser', [

        # inputFields expects an array of Fields to be used for inputtting values to the mutation
        'inputFields' => [
            FULL_NAME           => [
                'type'          => 'String',
                'description'   => __('Full name of the gym user to be registered', PLUGIN_PREFIX)
            ],
            MEMBERSHIP_DURATION . '_preset' => [
                'type'          => 'MembershipDurationPresetsEnum',
                'description'   => __('Set the membership duration from preset values to be added to current date.', PLUGIN_PREFIX)
            ],
            MEMBERSHIP_DURATION . '_specific' => [
                'type'          => 'String',
                'description'   => __('A date string in ISO format representing when the membership duration should end.', PLUGIN_PREFIX)
            ],
            IS_STUDENT          => [
                'type'          => 'Boolean',
                'description'   => __('If a gym user is a student. Useful for discounts', PLUGIN_PREFIX)
            ],
            BRANCH              => [
                'type'          => 'String',
                'description'   => __('Gym branch user belongs to', PLUGIN_PREFIX)
            ],
            GYM_ROLE              => [
                'type'          => 'String',
                'description'   => __('Gym user\'s role', PLUGIN_PREFIX)
            ]
        ],

        # outputFields expects an array of fields that can be asked for in response to the mutation
        # the resolve function is optional, but can be useful if the mutateAndPayload doesn't return an array
        # with the same key(s) as the outputFields
        'outputFields' => [
            FULL_NAME           => [
                'type'          => 'String',
                'description'   => __('Full name of the gym user to be updated', PLUGIN_PREFIX)
            ],
            MEMBERSHIP_DURATION => [
                'type'          => 'String', // should now be in VALIDDATEFORMAT form
                'description'   => __('Extension to the membership duration', PLUGIN_PREFIX)
            ],
            IS_STUDENT          => [
                'type'          => 'Int', // should now be either 1 or 0
                'description'   => __('If a gym user is a student. Useful for discounts', PLUGIN_PREFIX)
            ],
            BRANCH              => [
                'type'          => 'String',
                'description'   => __('Gym branch user belongs to', PLUGIN_PREFIX)
            ],
            GYM_ROLE              => [
                'type'          => 'String',
                'description'   => __('Gym user\'s role', PLUGIN_PREFIX)
            ]
        ],

        # mutateAndGetPayload expects a function, and the function gets passed the $input, $context, and $info
        # the function should return enough info for the outputFields to resolve with
        'mutateAndGetPayload' => function ($input, $context, $info) {
            // Do any logic here to sanitize the input, check user capabilities, etc
            if (!is_user_logged_in()) {
                return new WP_Error('403', 'Unauthenticated');
            }
            if (empty($input[GYM_ROLE])) {
                return new WP_Error('403', 'Required inputs are empty');
            }

            if ($input[GYM_ROLE] === GYM_MEMBER && !current_user_can('create_' . GYM_MEMBER . '')) {
                return new WP_Error('403', 'Forbidden capabilities');
            }
            if ($input[GYM_ROLE] === GYM_TRAINER && !current_user_can('create_' . GYM_TRAINER . '')) {
                return new WP_Error('403', 'Forbidden capabilities');
            }
            if ($input[GYM_ROLE] === GYM_ADMIN && !current_user_can('create_gym_user')) {
                return new WP_Error('403', 'Forbidden capabilities');
            }

            //to be returned
            $output = [];
            //for validation
            $data = [];
            $data['userId'] = $input['userId'];
            foreach ($input as $key => $val) {
                //for each editable field that is defined in our $input, we want to validate it
                if (!empty($input[$key])) {
                    //we then build a $data associative array ... 
                    //... so we can pass it through the same validation as registering a user
                    $data[$key] = $input[$key];
                }
            }

            $result = ValidationHelper::instance()->validate_and_register_new_user($data, $input[GYM_ROLE]);
            if (is_wp_error($result)) {
                return $result;
            }

            //if validation is successful, we'll reach below
            foreach ($data as $key => $val) {
                $output[$key] = $val;
            }

            return $output;
        }
    ]);
}
