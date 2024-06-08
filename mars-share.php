<?php
/*
Plugin Name: Social Media Scheduler
Plugin URI: http://yourwebsite.com/social-media-scheduler
Description: Automatically share and schedule posts to social media with unique messages.
Version: 1.0
Author: Your Name
Author URI: http://yourwebsite.com
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

// Load environment variables using Dotenv
require_once __DIR__ . '/vendor/autoload.php'; // Make sure the path matches where Composer's autoload.php is located relative to this file.
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Include the admin page, encryption functions, and scheduling functions
include_once dirname(__FILE__) . '/encryption.php';
include_once dirname(__FILE__) . '/admin-page.php';
include_once dirname(__FILE__) . '/scheduler-functions.php';

// Activation hook to schedule the daily event
register_activation_hook(__FILE__, 'sm_scheduler_activate');
function sm_scheduler_activate() {
    if (! wp_next_scheduled('sm_scheduler_daily_event')) {
        wp_schedule_event(time(), 'daily', 'sm_scheduler_daily_event');
    }
}

// Deactivation hook to clear scheduled events
register_deactivation_hook(__FILE__, 'sm_scheduler_deactivate');
function sm_scheduler_deactivate() {
    wp_clear_scheduled_hook('sm_scheduler_daily_event');
}