<?php if (!defined('ABSPATH')) exit; // Exit if accessed directly 
?>

<div id="RegForm" class="RegForm -gym_member">

    <?php if (count($attributes['errors']) > 0) : ?>
        <div class="Regform__notification -error">
            <?php foreach ($attributes['errors'] as $error) : ?>
                <p class="RegForm__error"> <span role="img" aria-label="registration-error">⚠️</span><?php echo $error; ?></p>
            <?php endforeach; ?>
        </div>
    <?php
    endif; ?>

    <?php if (isset($attributes['success'])) :
        switch ($attributes['gender']) {
            case 'male':
                $message = 'It\'s lit! <span role="img">🔥</span>';
                break;
            case 'female':
                $message = 'That\'s one step closer to making your body the sexiest outfit you own. <span role="img">💖</span>';
                break;
            default:
                $message = 'Let\'s get sweatin\' <span role="img">💪</span> ';
                break;
        }
    ?>
        <div class="RegForm__notification -success">
            <h2><?php _e("Registration successful!", 'builders-plugin'); ?> <span role="img">✅</span> </h2>
            <p class="RegForm__notifMsg">
                <?php echo wp_kses($message, array(
                    'span' => array(
                        'role' => array()
                    )
                )); ?></p>
        </div>
    <?php endif; ?>

    <h2 class="RegForm__title"><?php _e($attributes['title'], 'builders-plugin');  ?></h2>

    <?php $nonce = wp_create_nonce('gym_member_reg_form_nonce'); ?>
    <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" autocomplete="off">

        <input type="hidden" name="action" value="builders_do_action_register_gym_member">
        <input type="hidden" name="register_gym_member_nonce" value="<?php echo $nonce ?>" />
        <div class="RegForm__slot -fullname">
            <div class="Input">
                <label for="fullname" class="Input__label"><?php _e('Full Name', 'builders-plugin'); ?> <strong style="color: red">*</strong></label>
                <div class="Input__slot -relative">
                    <input id="fullname" type="text" name="full_name" value="" class="Input__inputEl" placeholder="Your Full Name" autocomplete="off" required>
                    <div class="Input__line"></div>
                </div>
            </div>
        </div>

        <div class="RegForm__slot -email">
            <div class="Input">
                <label for="email" class="Input__label"><?php _e('Email', 'builders-plugin'); ?> <strong style="color: red">*</strong></label>
                <div class="Input__slot -relative">
                    <input id="email" type="text" name="email" value="" class="Input__inputEl" placeholder="Your Email" autocomplete="off" required>
                    <div class="Input__line"></div>
                </div>
            </div>
        </div>

        <div class="RegForm__slot -birthdate">
            <div class="Input">
                <label for="birthdate" class="Input__label"><?php _e('Birthdate', 'builders-plugin'); ?> <strong style="color: red">*</strong></label>
                <div class="Input__slot -relative">
                    <input type="hidden" data-hidden-val="datepicker" name="birthdate" />
                    <input id="birthdate" class="RegForm__datepicker" placeholder="Select Date..." />
                </div>
            </div>
        </div>

         <div class="RegForm__slot -email">
            <div class="Input">
                <label for="student-toggle" class="Input__label"><?php _e('Student ?', 'builders-plugin'); ?> <strong style="color: red">*</strong></label>
                <div class="Input__slot -relative">
                    <input id="student-toggle" class="Input__checkboxInput" type="checkbox" name="is_student" value="1" />
                </div>
            </div>
        </div>

        <div class="RegForm__slot -gender">
            <div class="Input">
                <label for="gender" class="Input__label"><?php _e('Gender', 'builders-plugin'); ?> <strong style="color: red">*</strong></label>
                <div class="Input__slot -relative">
                    <select id="gender" class="Input__inputEl" name="gender">
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="no-say">Prefer not to say</option>
                    </select>
                    <div class="Input__line"></div>
                </div>
            </div>
        </div>

        <?php if ($attributes['recaptcha_site_key']) : ?>
            <div class="recaptcha-container">
                <div class="g-recaptcha" data-sitekey="<?php echo $attributes['recaptcha_site_key']; ?>"></div>
            </div>
        <?php endif; ?>

        <div class="RegForm__slot -submit">
            <button type="submit" class="RegForm__submitBtn" <?php disabled($attributes['disabled'], true) ?>>
                <?php esc_html_e($attributes['button_text'], 'builders-plugin'); ?>
            </button>
        </div>
    </form>
</div>