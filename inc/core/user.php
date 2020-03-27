<?php

namespace Builders_Plugin\Inc\Core;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use const Builders_Plugin\Constants\BRANCH;
use const Builders_Plugin\Constants\FULL_NAME;
use const Builders_Plugin\Constants\GYM_ROLE;
use const Builders_Plugin\Constants\IS_STUDENT;
use const Builders_Plugin\Constants\MEMBERSHIP_DURATION;

class User
{

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
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
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
        $this->register_user_metas();
    }
}
User::instance();
