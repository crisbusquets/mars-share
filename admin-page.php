<?php
function sm_scheduler_admin_menu() {
    add_menu_page(
        'Mars Share',
        'Mars Share',
        'manage_options',
        'mars-share',
        'sm_scheduler_admin_page',
        'dashicons-share',
        17
    );
}
add_action('admin_menu', 'sm_scheduler_admin_menu');

function sm_scheduler_admin_page() {
    ?>
<div class="wrap">
    <h2>Mars Share</h2>
    <form method="post" action="options.php">
        <?php
            settings_fields('sm_scheduler_options_group');
            do_settings_sections('mars-share');
            submit_button();
            ?>
    </form>
</div>
<?php
}

function sm_scheduler_settings_init() {
    register_setting('sm_scheduler_options_group', 'sm_scheduler_options');
    add_settings_section(
        'sm_scheduler_section_developers',
        __('Custom Settings', 'mars-share'),
        'sm_scheduler_section_callback',
        'mars-share'
    );

    add_settings_field(
        'sm_scheduler_custom_message',
        __('Custom Message', 'mars-share'),
        'sm_scheduler_custom_message_render',
        'mars-share',
        'sm_scheduler_section_developers'
    );
}
add_action('admin_init', 'sm_scheduler_settings_init');

function sm_scheduler_section_callback() {
    echo '<p>Enter your custom message variations below. Separate them with a pipe "|" character.</p>';
}

function sm_scheduler_custom_message_render() {
    $options = get_option('sm_scheduler_options');
    ?>
<input type='text' name='sm_scheduler_options[custom_message]'
    value='<?php echo esc_attr($options['custom_message'] ?? ''); ?>' style="width: 100%;">
<?php
}