<?php
require_once dirname(__FILE__) . '/vendor/autoload.php'; // Ensure the path to autoload.php is correct based on your folder structure

// Function to post to Twitter using the TwitterOAuth library
function sm_scheduler_post_to_twitter($post, $message) {
    // Accessing API keys and tokens from environment variables
    $consumerKey = $_ENV['TWITTER_CONSUMER_KEY'];
    $consumerSecret = $_ENV['TWITTER_CONSUMER_SECRET'];
    $accessToken = $_ENV['TWITTER_ACCESS_TOKEN'];
    $accessTokenSecret = $_ENV['TWITTER_ACCESS_TOKEN_SECRET'];

    $twitter = new Abraham\TwitterOAuth\TwitterOAuth($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret);
    
    $status = $message . ' ' . get_permalink($post);
    $result = $twitter->post("statuses/update", ["status" => $status]);

    if ($twitter->getLastHttpCode() == 200) {
        // Tweet posted successfully
        return true;
    } else {
        // Handle errors here
        return false;
    }
}

// Function to post to LinkedIn
function sm_scheduler_post_to_linkedin($post, $message) {
    $accessToken = $_ENV['LINKEDIN_ACCESS_TOKEN']; // Access token from environment variable
    $url = 'https://api.linkedin.com/v2/shares';

    $headers = [
        'Authorization: Bearer ' . $accessToken,
        'Content-Type: application/json',
        'X-Restli-Protocol-Version: 2.0.0'
    ];

    $body = json_encode([
        'content' => [
            'contentEntities' => [
                [
                    'entityLocation' => get_permalink($post),
                    'thumbnails' => [
                        ['resolvedUrl' => get_the_post_thumbnail_url($post)]
                    ]
                ]
            ],
            'title' => get_the_title($post)
        ],
        'distribution' => [
            'linkedInDistributionTarget' => []
        ],
        'owner' => 'urn:li:person:cbusquets', // Replace with your actual LinkedIn Profile ID
        'text' => [
            'text' => $message
        ]
    ]);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if (!$error) {
        return json_decode($response, true);
    } else {
        return $error;
    }
}

// WP Cron hook for scheduled posting
add_action('sm_scheduler_daily_event', 'sm_scheduler_post_to_social_media');
function sm_scheduler_post_to_social_media() {
    $options = get_option('sm_scheduler_options');
    $messages = explode('|', $options['custom_message']);
    $random_message = $messages[array_rand($messages)];

    // Fetch a random post that hasn't been shared recently
    $post = sm_scheduler_fetch_random_post();

    if ($post) {
        // Attempt to post to Twitter
        if (sm_scheduler_post_to_twitter($post, $random_message)) {
            update_post_meta($post->ID, 'sm_last_shared_on_twitter', time());
        }

        // Attempt to post to LinkedIn
        if (sm_scheduler_post_to_linkedin($post, $random_message)) {
            update_post_meta($post->ID, 'sm_last_shared_on_linkedin', time());
        }
    }
}

// Function to fetch a random post
function sm_scheduler_fetch_random_post() {
    $args = [
        'post_type' => 'post',
        'posts_per_page' => 1,
        'orderby' => 'rand',
        'meta_query' => [
            [
                'key' => 'sm_last_shared_on_twitter',
                'value' => time() - WEEK_IN_SECONDS * 2, // Adjust the interval as needed
                'compare' => '<',
                'type' => 'NUMERIC'
            ],
            [
                'key' => 'sm_last_shared_on_linkedin',
                'value' => time() - WEEK_IN_SECONDS * 2, // Adjust the interval as needed
                'compare' => '<',
                'type' => 'NUMERIC'
            ]
        ]
    ];
    $query = new WP_Query($args);
    if ($query->have_posts()) {
        $query->the_post();
        return $query->post;
    } else {
        return null;
    }
}