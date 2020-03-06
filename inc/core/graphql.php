<?php
//TODO comments
namespace Builders_Plugin\Inc\Core\GraphQl;

use Builders_Plugin\Inc\Helpers\Validation as ValidationHelper;
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
    return \graphql([
        'query' => ' {
                        user(id: "' . $id . '", idType: DATABASE_ID) 
                        {
                            ' . GYM_MEMBER_FIELDS . '
                        }
                    }'
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
    \register_graphql_mutation('updateGymUser', [

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
                throw new \RuntimeException('Unauthenticated user', 403);
            }
            // Do any logic here to sanitize the input, check user capabilities, etc
            if (!current_user_can('update_gym_member')) {
                throw new \RuntimeException('Forbidden capabilities', 403);
            }
            if (empty($input['userId'])) {
                throw new \RuntimeException('Required inputs are empty', 403);
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
                foreach ($result->get_error_codes() as $error_code) {
                    throw new \RuntimeException($result->get_error_message($error_code));
                }
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
    \register_graphql_mutation('createGymUser', [

        # inputFields expects an array of Fields to be used for inputtting values to the mutation
        'inputFields' => [
            FULL_NAME           => [
                'type'          => 'String',
                'description'   => __('Full name of the gym user to be registered', PLUGIN_PREFIX)
            ],
            MEMBERSHIP_DURATION . '_preset' => [
                'type'          => 'String',
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
            ],
            'userId'              => [
                'type'          => 'Int',
                'description'   => __('The gym user\'s database ID', PLUGIN_PREFIX)
            ],
            'id'                => [
                'type'          => 'ID',
                'description'   => __('A unique identifier', PLUGIN_PREFIX)
            ]
        ],

        # mutateAndGetPayload expects a function, and the function gets passed the $input, $context, and $info
        # the function should return enough info for the outputFields to resolve with
        'mutateAndGetPayload' => function ($input, $context, $info) {
            // Do any logic here to sanitize the input, check user capabilities, etc
            if (!is_user_logged_in()) {
                throw new \RuntimeException('Unauthenticated user', 403);
            }
            if (empty($input[GYM_ROLE])) {
                throw new \RuntimeException('Required inputs are empty', 403);
            }

            if ($input[GYM_ROLE] !== GYM_MEMBER && $input[GYM_ROLE] !== GYM_TRAINER && $input[GYM_ROLE] !== GYM_ADMIN) {
                throw new \RuntimeException('Forbidden gym roles. Try ' . GYM_MEMBER . ', ' . GYM_TRAINER . ' , ' . GYM_ADMIN . ' ', 403);
            }

            if ($input[MEMBERSHIP_DURATION . '_preset'] !== THIRTY_DAYS && MEMBERSHIP_DURATION . '_preset' !== HALF_YEAR && MEMBERSHIP_DURATION . '_preset' !== NINETY_DAYS && MEMBERSHIP_DURATION . '_preset' !== ONE_YEAR) {
                throw new \RuntimeException('Forbidden membership duration. Try ' . THIRTY_DAYS . ', ' . NINETY_DAYS . ', ' . HALF_YEAR . ', ' . ONE_YEAR . ' ', 403);
            }

            if ($input[GYM_ROLE] === GYM_MEMBER && !current_user_can('create_' . GYM_MEMBER . '')) {
                throw new \RuntimeException('Forbidden capabilities', 403);
            }
            if ($input[GYM_ROLE] === GYM_TRAINER && !current_user_can('create_' . GYM_TRAINER . '')) {
                throw new \RuntimeException('Forbidden capabilities', 403);
            }
            if ($input[GYM_ROLE] === GYM_ADMIN && !current_user_can('create_gym_user')) {
                throw new \RuntimeException('Forbidden capabilities', 403);
            }

            //to be returned
            $output = [];
            //for validation
            $data = [];
            $data['userId'] = $input['userId'];
            $data['username'] = $input[FULL_NAME];
            foreach ($input as $key => $val) {

                if (!empty($input[$key])) {

                    //handle 'membership_duration_preset' and 'membership_duration_specific' cases
                    //by our control flow below, 'membership_duration_specific' will always have priority if it is set
                    if ($key === MEMBERSHIP_DURATION . '_preset' && !empty($val)) {
                        $data[MEMBERSHIP_DURATION] = $val;
                        continue;
                    }

                    if ($key === MEMBERSHIP_DURATION . '_specific' && !empty($val)) {
                        $data[MEMBERSHIP_DURATION] = $val;
                        continue;
                    }
                    //we then build a $data associative array ... 
                    //... so we can pass it through the same validation as registering a user
                    $data[$key] = $val;
                }
            }

            $result = ValidationHelper::instance()->validate_and_register_new_user($data, $input[GYM_ROLE]);
            if (is_wp_error($result)) {
                foreach ($result->get_error_codes() as $error_code) {
                    throw new \Error($result->get_error_message($error_code), 403);
                }
            }

            //if validation is successful, we'll reach below
            foreach ($data as $key => $val) {
                $output[$key] = $val;
            }

            $output['userId'] = $result;

            return $output;
        }
    ]);
}
