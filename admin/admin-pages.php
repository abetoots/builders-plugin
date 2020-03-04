<?php

use const Builders_Plugin\Constants\PLUGIN_PREFIX;

settings_errors(); ?>
<form method="post" action="options.php" class='BuildersPlugin__adminForm'>
    <?php settings_fields('builders-plugin-settings'); ?>
    <?php do_settings_sections('' . PLUGIN_PREFIX . '_do_settings_section'); ?>
    <?php submit_button(); ?>
</form>