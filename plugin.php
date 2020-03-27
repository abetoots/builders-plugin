<?php

namespace Builders_Plugin;


use const Builders_Plugin\Constants\GYM_ADMIN;
use const Builders_Plugin\Constants\GYM_MEMBER;
use const Builders_Plugin\Constants\GYM_TRAINER;


/**
 * Class Plugin
 *
 * Main Plugin class
 * @since 1.0.0
 */
class Plugin
{

    /**
     * Include Class Files
     *
     * @since 1.0.0
     * @access private
     */
    private function include_class_files()
    {
        require_once(BUILDERS_PLUGIN_DIR . 'inc/core/constants.php');
        require_once(BUILDERS_PLUGIN_DIR . 'inc/core/member-registration.php');
        require_once(BUILDERS_PLUGIN_DIR . 'inc/core/login.php');
        require_once(BUILDERS_PLUGIN_DIR . 'inc/core/logout.php');
        require_once(BUILDERS_PLUGIN_DIR . 'inc/core/graphql.php');
        require_once(BUILDERS_PLUGIN_DIR . 'inc/core/user.php');
        require_once(BUILDERS_PLUGIN_DIR . 'inc/core/utilities.php');
        require_once(BUILDERS_PLUGIN_DIR . 'inc/helpers/validation.php');
        require_once(BUILDERS_PLUGIN_DIR . 'inc/helpers/acf.php');
        require_once(BUILDERS_PLUGIN_DIR . 'admin/settings.php');
    }

    /**
     *Register initial role definitions
     *  
     * @since 1.0.0
     * @access private
     */
    public function define_initial_roles()
    {
        //Avoid nasty bugs
        if (get_role(GYM_MEMBER)) {
            remove_role(GYM_MEMBER);
        }
        add_role(GYM_MEMBER, 'Gym Member', array(
            'read'      => true
        ));
        if (get_role(GYM_TRAINER)) {
            remove_role(GYM_TRAINER);
        }
        add_role(GYM_TRAINER, 'Gym Trainer', array(
            'read'      => true
        ));
        if (get_role(GYM_ADMIN)) {
            remove_role(GYM_ADMIN);
        }
        add_role(GYM_ADMIN, 'Gym Admin', array(
            'read'      => true
        ));
    }

    /**
     * Add same custom capabilities to the defined roles
     * 
     * @since 1.0.0
     * @access private
     * 
     * @uses get_role();
     * @uses add_cap();
     */
    public function add_custom_caps()
    {
        $trainer = get_role(GYM_TRAINER);
        $trainer_caps = [
            'list_users',
            'list_gym_member',
            'create_gym_member',
        ];
        foreach ($trainer_caps as $c) {
            $trainer->add_cap($c);
        };


        $gymAdmin = get_role(GYM_ADMIN);
        $gym_admin_caps = [
            'list_users',
            'list_gym_member',
            'create_gym_member',
            'create_gym_trainer',
            'update_gym_member'
        ];
        foreach ($gym_admin_caps as $c) {
            $gymAdmin->add_cap($c);
        };

        $admin = get_role('administrator');
        $admin_caps = [
            'list_gym_member',
            'create_gym_member',
            'create_gym_trainer',
            'create_gym_user',
            'update_gym_member'
        ];
        foreach ($admin_caps as $c) {
            $admin->add_cap($c);
        };
    }

    /**
     * Include libraries
     *
     * @since 1.0.0
     * @access private
     */
    private function include_libraries()
    {
        if (!class_exists('ACF') || !function_exists('get_field')) {
            include_once(BUILDERS_ACF_DIR . 'acf.php');
        }
    }

    /**
     * Scripts only meant to run on development
     * Dev scripts are git ignored
     */
    public static function enable_dev_scripts()
    {
        require_once(BUILDERS_PLUGIN_DIR . 'inc/core/dev-scripts.php');
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
        $this->include_class_files();
        $this->include_libraries();

        //Add gym roles
        add_action("init", array($this, 'define_initial_roles'));
        // Add role capabilities, priority must be after the initial role definition.
        add_action('init', array($this, 'add_custom_caps'), 11);
    }
}
// Instantiate Plugin Class
Plugin::instance();
if (in_array($_SERVER['REMOTE_ADDR'], array('127.0.0.1', '::1'))) {
    //Dev only
    Plugin::enable_dev_scripts();
}
