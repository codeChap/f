<?php

require 'vendor/autoload.php';

use Codechap\F\F;
use Codechap\F\Msg;

// App Credentials
$appId = trim(file_get_contents(realpath(__DIR__ . '/../../') . '/FACEBOOK_APP_ID.txt'));
$appSecret = trim(file_get_contents(realpath(__DIR__ . '/../../') . '/FACEBOOK_APP_SECRET.txt'));

// Configuration - Run setup.php to get these values
$pageId = ''; // Will be set by setup.php
$accessToken = ''; // Will be set by setup.php

// Load from .env.local if it exists
if (file_exists('.env.local')) {
    $env = parse_ini_file('.env.local');
    $pageId = $env['FACEBOOK_PAGE_ID'] ?? $pageId;
    $accessToken = $env['FACEBOOK_ACCESS_TOKEN'] ?? $accessToken;
    $pageName = $env['FACEBOOK_PAGE_NAME'] ?? 'Your Page';
    $appId = $env['FACEBOOK_APP_ID'] ?? $appId;
    $appSecret = $env['FACEBOOK_APP_SECRET'] ?? $appSecret;
    echo "Using configuration for: {$pageName}\n\n";
}

// Initialize Facebook client
$fb = new F();
$fb->set('pageId', $pageId);
$fb->set('accessToken', $accessToken);

try {
    // Example 1: Simple text post
    echo "Example 1: Simple text post\n";
    $msg = new Msg();
    $msg->set('content', 'Hello Facebook! This is a test post from the PHP F package.');

    $response = $fb->post($msg);
    echo "✓ Posted successfully! ID: " . $response['id'] . "\n\n";

    // Example 2: Post with single photo
    // Make sure test-image.jpg exists in your project directory
    if (file_exists('test-image.jpg')) {
        echo "Example 2: Post with photo\n";
        $photoMsg = new Msg();
        $photoMsg->set('content', 'Check out this amazing photo!');
        $photoMsg->set('image', 'test-image.jpg');

        $response = $fb->post($photoMsg);
        echo "✓ Photo posted! ID: " . $response['id'] . "\n\n";
    }

    // Example 3: Post multiple photos
    // Make sure these files exist
    if (file_exists('photo1.jpg') && file_exists('photo2.jpg')) {
        echo "Example 3: Multiple photos\n";

        $photo1 = new Msg();
        $photo1->set('content', 'Beautiful sunrise');
        $photo1->set('image', 'photo1.jpg');

        $photo2 = new Msg();
        $photo2->set('content', 'Amazing sunset');
        $photo2->set('image', 'photo2.jpg');

        $response = $fb->post([$photo1, $photo2]);
        echo "✓ Multi-photo post created! ID: " . $response['id'] . "\n\n";
    }

    // Example 4: Get page info
    echo "Example 4: Page information\n";
    $info = $fb->me();
    echo "✓ Page Name: " . $info['data']['name'] . "\n";
    echo "✓ Page ID: " . $info['data']['id'] . "\n\n";

} catch (\RuntimeException $e) {
    echo "❌ Facebook API Error: " . $e->getMessage() . "\n";
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "Done!\n";
