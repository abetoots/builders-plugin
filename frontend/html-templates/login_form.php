<?php if (!defined('ABSPATH')) exit; // Exit if accessed directly 
?>

<div id="LoginForm" class="LoginForm">

    <!-- Show errors if there are any -->
    <?php if (count($attributes['errors']) > 0) : ?>
        <?php foreach ($attributes['errors'] as $error) : ?>
            <p class="LoginForm__error">
                <?php echo esc_html($error); ?>
            </p>
    <?php endforeach;
    endif; ?>

    <!-- Show logged out message if user just logged out -->
    <?php if ($attributes['logged_out']) : ?>
        <p class="LoginForm__info -loggedOut">
            <?php _e('You have signed out. <br> Would you like to sign in again?', 'builders-plugin'); ?>
        </p>
    <?php endif; ?>

    <h2 class="LoginForm__title"><?php _e($attributes['title'], 'builders-plugin') ?></h2>

    <form method="post" action="<?php echo esc_url(wp_login_url()); ?>" autocomplete="on">
        <div class="LoginForm__username">
            <label for="user_login"><?php _e('Email or Username', 'builders-plugin'); ?></label>
            <div>
                <input type="text" name="log" id="user_login" class="LoginForm__input" placeholder="Email or Username">
            </div>

        </div>
        <div class="LoginForm__password">
            <label for="user_pass"><?php _e('Password', 'builders-plugin'); ?></label>
            <div>
                <input type="password" name="pwd" id="user_pass" class="LoginForm__input" placeholder="Password">
            </div>
        </div>
        <div class="LoginForm__submit">
            <button type="submit" class="LoginForm__submitBtn"><?php esc_html_e($attributes['button_text'], 'builders-plugin'); ?></button>
        </div>
    </form>

    <!-- //TODO maybe implement our own lost password user flow -->
    <a class="forgot-password" href="<?php echo esc_url(wp_lostpassword_url()); ?>">
        <?php _e('Forgot your password?', 'builders-plugin'); ?>
    </a>
</div>