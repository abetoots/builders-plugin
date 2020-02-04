<?php settings_errors(); ?>
<form method="post" action="options.php" class='BuildersPlugin__adminForm'>
    <?php settings_fields('builders-plugin-settings'); ?>
    <?php do_settings_sections('builders_plugin_do_settings_section'); ?>
    <?php submit_button(); ?>
</form>