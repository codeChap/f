<?php

/**
 * Facebook Page Setup Script
 * Simple setup for the Facebook posting package
 */

echo "================================================\n";
echo "Facebook Page Setup\n";
echo "================================================\n\n";

// App Credentials
$appId = trim(file_get_contents(realpath(__DIR__ . '/../../') . '/FACEBOOK_APP_ID.txt'));
$appSecret = trim(file_get_contents(realpath(__DIR__ . '/../../') . '/FACEBOOK_APP_SECRET.txt'));

echo "App ID: {$appId}\n\n";

echo "STEP 1: Get Your Access Token\n";
echo "==============================\n\n";

echo "1. Go to: https://developers.facebook.com/tools/explorer/\n\n";

echo "2. Select your app '{$appId}' from the dropdown\n\n";

echo "3. Click 'User or Page' → Select 'Get User Token'\n\n";

echo "4. Add these permissions:\n";
echo "   • pages_show_list\n";
echo "   • pages_manage_posts\n";
echo "   • pages_read_engagement\n";
echo "   • public_profile\n\n";

echo "5. Click 'Generate Access Token'\n\n";

echo "6. Grant permissions and select your Facebook pages\n\n";

echo "7. Copy the token\n\n";

echo "Paste your access token: ";
$userToken = trim(fgets(STDIN));

if (empty($userToken)) {
    echo "\n❌ No token provided. Exiting.\n";
    exit(1);
}

echo "\n================================================\n";
echo "STEP 2: Exchange for Long-Lived Token\n";
echo "================================================\n";

// Exchange for long-lived token
$url = "https://graph.facebook.com/v18.0/oauth/access_token?" . http_build_query([
    'grant_type' => 'fb_exchange_token',
    'client_id' => $appId,
    'client_secret' => $appSecret,
    'fb_exchange_token' => $userToken
]);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$longLivedToken = $userToken; // Default to original

if ($httpCode === 200) {
    $data = json_decode($response, true);
    if (isset($data['access_token'])) {
        $longLivedToken = $data['access_token'];
        echo "✓ Long-lived token obtained\n";
    }
} else {
    echo "Using original token\n";
}

echo "\n================================================\n";
echo "STEP 3: Get Your Pages\n";
echo "================================================\n";

// Get pages
$url = "https://graph.facebook.com/v18.0/me/accounts?" . http_build_query([
    'access_token' => $longLivedToken,
    'fields' => 'id,name,access_token'
]);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    echo "❌ Failed to get pages.\n";
    echo "Make sure you granted page permissions when generating the token.\n";
    exit(1);
}

$data = json_decode($response, true);

if (!isset($data['data']) || empty($data['data'])) {
    echo "❌ No pages found.\n";
    echo "Make sure you selected your pages when generating the token.\n";
    exit(1);
}

$pages = $data['data'];
echo "Found " . count($pages) . " page(s):\n\n";

foreach ($pages as $index => $page) {
    echo ($index + 1) . ". " . $page['name'] . " (ID: " . $page['id'] . ")\n";
}

echo "\n";

if (count($pages) === 1) {
    $selectedPage = $pages[0];
} else {
    echo "Select a page (1-" . count($pages) . "): ";
    $choice = intval(trim(fgets(STDIN))) - 1;
    $selectedPage = $pages[$choice] ?? $pages[0];
}

$pageToken = $selectedPage['access_token'];
$pageName = $selectedPage['name'];
$pageId = $selectedPage['id'];

echo "\n================================================\n";
echo "SUCCESS!\n";
echo "================================================\n\n";

echo "Page: {$pageName}\n";
echo "Page ID: {$pageId}\n\n";

// Save configuration
$envContent = "# Facebook Page Configuration\n";
$envContent .= "# Generated: " . date('Y-m-d H:i:s') . "\n";
$envContent .= "FACEBOOK_PAGE_ID={$pageId}\n";
$envContent .= "FACEBOOK_PAGE_NAME=\"{$pageName}\"\n";
$envContent .= "FACEBOOK_ACCESS_TOKEN={$pageToken}\n";
$envContent .= "FACEBOOK_APP_ID={$appId}\n";
$envContent .= "FACEBOOK_APP_SECRET={$appSecret}\n";

file_put_contents('.env.local', $envContent);
echo "✓ Configuration saved to .env.local\n\n";

// Update example.php
if (file_exists('example.php')) {
    $example = file_get_contents('example.php');
    $example = preg_replace('/\$pageId = \'[^\']*\'/', "\$pageId = '{$pageId}'", $example);
    $example = preg_replace('/\$accessToken = \'[^\']*\'/', "\$accessToken = '{$pageToken}'", $example);
    file_put_contents('example.php', $example);
    echo "✓ Updated example.php\n\n";
}

echo "Your Page Access Token:\n";
echo str_repeat('=', 50) . "\n";
echo $pageToken . "\n";
echo str_repeat('=', 50) . "\n\n";

echo "Test your setup with: php example.php\n\n";

echo "Done! 🎉\n";
