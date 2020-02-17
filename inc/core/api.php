<?php

namespace Builders_Plugin\Inc\Core;

class Api
{

    /**
     * Removes `has_published_posts` from the query args so even users who have not
     * published content are returned by the request.
     *
     * @see https://developer.wordpress.org/reference/classes/wp_user_query/
     *
     * @param array           $prepared_args Array of arguments for WP_User_Query.
     * @param WP_REST_Request $request       The current request.
     *
     * @return array
     */
    public function unset_has_published_posts($prepared_args, $request)
    {
        unset($prepared_args['has_published_posts']);

        return $prepared_args;
    }

    /**
     * Registers rest fields for users
     * @link https://developer.wordpress.org/reference/functions/register_rest_field/
     */
    public function add_user_rest_fields()
    {
        // register_rest_field('user', 'gym_role', array(
        //     'get_callback'  => function ($obj_type) {
        //         $role = get_userdata($obj_type['id'])->roles[0];
        //         if ($role === 'gym_member' || $role === 'gym_trainer' || $role === 'gym_admin') {
        //             return $role;
        //         }
        //         return null;
        //     }
        // ));
    }

    // /**
    //  * Adds the user role to our token response
    //  */
    // function add_role_to_jwt_response($data, $user)
    // {
    //     $data['username'] = $user->user_login;
    //     if ($user->roles[0] === 'administrator') {
    //         $data['role'] = 'administrator';
    //     } elseif ($user->roles[0] === 'gym_trainer') {
    //         $data['role'] = 'gym_member';
    //     } else if ($user->roles[0] === 'gym_admin') {
    //         $data['role'] = 'gym_admin';
    //     }
    //     return $data;
    // }

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
        add_action('rest_api_init', array($this, 'add_user_rest_fields'));
        add_filter('rest_user_query', array($this, 'unset_has_published_posts'), 10, 2);
        // add_filter('jwt_auth_token_before_dispatch', array($this, 'add_role_to_jwt_response'), 10, 2);
    }
}
Api::instance();
