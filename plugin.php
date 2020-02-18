<?php

namespace Builders_Plugin;

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
        require_once(BUILDERS_PLUGIN_DIR . 'inc/core/member-registration.php');
        require_once(BUILDERS_PLUGIN_DIR . 'inc/core/login.php');
        require_once(BUILDERS_PLUGIN_DIR . 'inc/core/logout.php');
        require_once(BUILDERS_PLUGIN_DIR . 'inc/core/api.php');
        require_once(BUILDERS_PLUGIN_DIR . 'inc/core/react.php');
        require_once(BUILDERS_PLUGIN_DIR . 'inc/helpers/registration.php');
        require_once(BUILDERS_PLUGIN_DIR . 'inc/helpers/utilities.php');
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
        if (get_role('gym_member')) {
            remove_role('gym_member');
        }
        add_role('gym_member', 'Gym Member', array(
            'read'      => true
        ));
        if (get_role('gym_trainer')) {
            remove_role('gym_trainer');
        }
        add_role('gym_trainer', 'Gym Trainer', array(
            'read'      => true
        ));
        if (get_role('gym_admin')) {
            remove_role('gym_admin');
        }
        add_role('gym_admin', 'Gym Admin', array(
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
        $trainer = get_role('gym_trainer');
        $trainer_caps = [
            'list_gym_members'
        ];
        foreach ($trainer_caps as $c) {
            $trainer->add_cap($c);
        };

        $trainer = get_role('administrator');
        $trainer_caps = [
            'list_gym_members'
        ];
        foreach ($trainer_caps as $c) {
            $trainer->add_cap($c);
        };

        //         $admin = get_role('gym_admin');
        // $admin_caps = [

        // ];
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
        require_once(BUILDERS_PLUGIN_DIR . 'inc/helpers/dev-scripts.php');
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
     * Register plugin action hooks and filters
     *
     * @since 1.0.0
     * @access public
     */
    public function __construct()
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
//Dev only
// Plugin::enable_dev_scripts();
