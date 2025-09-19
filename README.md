# PHP Facebook Page Post Library

A simple PHP library for posting to Facebook Pages using the Graph API. This package allows you to post text messages and photos to your Facebook page.

## Requirements

- PHP >= 8.2
- cURL extension
- JSON extension
- Facebook App with Page access
- Long-lived Page Access Token

## Installation

```bash
composer require codechap/f
composer require codechap/f @dev
```

## Quick Start

### 1. Setup

Run the setup script to configure your Facebook page:

```bash
php setup.php
```

This will guide you through:
- Getting an access token from Facebook
- Selecting your Facebook page
- Saving the configuration

### 2. Post to Facebook

```php
<?php

require 'vendor/autoload.php';

use Codechap\F\F;
use Codechap\F\Msg;

// Initialize Facebook client
$fb = new F();
$fb->set('pageId', 'YOUR_PAGE_ID');
$fb->set('accessToken', 'YOUR_PAGE_ACCESS_TOKEN');

// Create a simple text post
$msg = new Msg();
$msg->set('content', 'Hello Facebook!');

$response = $fb->post($msg);
echo "Posted! ID: " . $response['id'];
```

## Examples

### Text Post

```php
$msg = new Msg();
$msg->set('content', 'Hello from PHP!');
$fb->post($msg);
```

### Photo Post

```php
$msg = new Msg();
$msg->set('content', 'Check out this photo!');
$msg->set('image', 'path/to/photo.jpg');
$fb->post($msg);
```

### Multiple Photos

```php
$photo1 = new Msg();
$photo1->set('image', 'photo1.jpg');

$photo2 = new Msg();
$photo2->set('image', 'photo2.jpg');

$photo3 = new Msg();
$photo3->set('content', 'Photo gallery');
$photo3->set('image', 'photo3.jpg');

$fb->post([$photo1, $photo2, $photo3]);
```

### Get Page Info

```php
$info = $fb->me();
echo "Page: " . $info['data']['name'];
```

## Getting Access Token

### Method 1: Using Setup Script (Recommended)

```bash
php setup.php
```

Follow the prompts to get your token from Facebook Graph API Explorer.

### Method 2: Manual Setup

1. Go to [Facebook Graph API Explorer](https://developers.facebook.com/tools/explorer/)
2. Select your app from the dropdown
3. Click "User or Page" → "Get User Token"
4. Add these permissions:
   - `pages_show_list`
   - `pages_manage_posts`
   - `pages_read_engagement`
   - `public_profile`
5. Click "Generate Access Token"
6. Grant permissions and select your pages
7. Copy the token

### Extend Token Lifetime

To get a long-lived token (60+ days):

1. Go to [Access Token Debugger](https://developers.facebook.com/tools/debug/accesstoken/)
2. Paste your token
3. Click "Extend Access Token"
4. Use the extended token in your code

## Facebook App Credentials

This package is configured to work with an app:

- **App ID**: `YOUR_APP_ID_HERE`
- **App Secret**: `YOUR_APP_SECRET_HERE`

You can also use your own Facebook app by updating these values in `setup.php`.

## API Reference

### F Class

Main class for Facebook operations.

#### Methods

- `set(string $key, string $value)` - Set configuration (pageId, accessToken, apiVersion)
- `post($content)` - Post content to Facebook (accepts Msg or array of Msg)
- `me()` - Get page information

### Msg Class

Message content handler.

#### Methods

- `set(string $key, string $value)` - Set content or image path
- `get(string $key)` - Get content or image path
- `hasContent()` - Check if message has text
- `hasImage()` - Check if message has an image

## Configuration

After running `setup.php`, your configuration is saved to `.env.local`:

```ini
FACEBOOK_PAGE_ID=YOUR_PAGE_ID
FACEBOOK_PAGE_NAME="Your Page Name"
FACEBOOK_ACCESS_TOKEN=YOUR_TOKEN
FACEBOOK_APP_ID=YOUR_APP_ID
FACEBOOK_APP_SECRET=YOUR_APP_SECRET_HERE
```

## Error Handling

```php
try {
    $fb->post($msg);
} catch (\RuntimeException $e) {
    // Facebook API errors
    echo "API Error: " . $e->getMessage();
} catch (\Exception $e) {
    // Other errors
    echo "Error: " . $e->getMessage();
}
```

## Image Requirements

- Maximum size: 10MB
- Supported formats: JPEG, PNG, GIF, WEBP
- Minimum dimensions: 200x200 pixels

## Rate Limits

Facebook enforces rate limits on API calls. Be mindful of:
- Posting frequency
- Number of API calls per hour
- Batch operations when posting multiple items

## Security

- **Never commit access tokens** to version control
- Use environment variables or `.env.local` files
- Add `.env.local` to `.gitignore`
- Regenerate tokens periodically
- Keep app credentials secure

## Troubleshooting

### No pages found or Missing pages
- Make sure you're an admin of at least one Facebook page
- Grant all required permissions when generating the token
- Select your pages in the authorization popup
- **Note:** Pages belonging to different Business Manager accounts or business assets may not all appear together. You can only access pages within the same business context in a single token

### Token expired
- Tokens expire after 60 days
- Run `setup.php` again to get a new token
- Consider implementing automatic token refresh

### Permission errors
- Ensure your app has the required permissions
- Regenerate token with all permissions granted
- Check that your page is published and accessible

## License

MIT License - see LICENSE file for details.
