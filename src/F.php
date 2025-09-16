<?php

namespace Codechap\F;

class F
{
    /**
     * Facebook Page ID
     */
    private string $pageId = '';

    /**
     * Facebook Page Access Token
     */
    private string $accessToken = '';

    /**
     * Facebook Graph API Version
     */
    private string $apiVersion = 'v18.0';

    /**
     * Facebook Graph API Base URL
     */
    private const API_BASE_URL = 'https://graph.facebook.com/';

    /**
     * Set configuration values
     */
    public function set(string $key, string $value): self
    {
        switch ($key) {
            case 'pageId':
                $this->pageId = $value;
                break;
            case 'accessToken':
                $this->accessToken = $value;
                break;
            case 'apiVersion':
                $this->apiVersion = $value;
                break;
            default:
                throw new \InvalidArgumentException("Unknown configuration key: {$key}");
        }

        return $this;
    }

    /**
     * Post to Facebook page
     * 
     * @param Msg|array $content Single Msg object or array of Msg objects for multi-photo posts
     * @return array Response from Facebook API
     */
    public function post($content): array
    {
        if (!$this->pageId || !$this->accessToken) {
            throw new \RuntimeException('Page ID and Access Token are required');
        }

        // Handle single message
        if ($content instanceof Msg) {
            return $this->postSingle($content);
        }

        // Handle array of messages (multi-photo post)
        if (is_array($content)) {
            return $this->postMultiple($content);
        }

        throw new \InvalidArgumentException('Content must be a Msg object or array of Msg objects');
    }

    /**
     * Post a single message with optional photo
     */
    private function postSingle(Msg $msg): array
    {
        $message = $msg->get('content');
        $image = $msg->get('image');

        if ($image && file_exists($image)) {
            // Post with photo
            return $this->postPhoto($message, [$image]);
        } else {
            // Text-only post
            return $this->postText($message);
        }
    }

    /**
     * Post multiple photos with a message
     */
    private function postMultiple(array $messages): array
    {
        $combinedMessage = '';
        $images = [];

        foreach ($messages as $msg) {
            if (!($msg instanceof Msg)) {
                throw new \InvalidArgumentException('All array items must be Msg objects');
            }

            $content = $msg->get('content');
            if ($content) {
                $combinedMessage .= ($combinedMessage ? "\n\n" : '') . $content;
            }

            $image = $msg->get('image');
            if ($image && file_exists($image)) {
                $images[] = $image;
            }
        }

        if (!empty($images)) {
            return $this->postPhoto($combinedMessage, $images);
        } else {
            return $this->postText($combinedMessage);
        }
    }

    /**
     * Post text-only message to Facebook page
     */
    private function postText(string $message): array
    {
        $endpoint = self::API_BASE_URL . $this->apiVersion . '/' . $this->pageId . '/feed';

        $data = [
            'message' => $message,
            'access_token' => $this->accessToken
        ];

        return $this->makeRequest($endpoint, $data);
    }

    /**
     * Post photo(s) with message to Facebook page
     */
    private function postPhoto(string $message, array $imagePaths): array
    {
        if (count($imagePaths) === 1) {
            // Single photo post
            $endpoint = self::API_BASE_URL . $this->apiVersion . '/' . $this->pageId . '/photos';

            $data = [
                'message' => $message,
                'access_token' => $this->accessToken,
                'source' => new \CURLFile($imagePaths[0])
            ];

            return $this->makeRequest($endpoint, $data, true);
        } else {
            // Multiple photos post
            $photoIds = [];

            // First, upload each photo without publishing
            foreach ($imagePaths as $imagePath) {
                $endpoint = self::API_BASE_URL . $this->apiVersion . '/' . $this->pageId . '/photos';

                $data = [
                    'access_token' => $this->accessToken,
                    'published' => 'false',
                    'source' => new \CURLFile($imagePath)
                ];

                $response = $this->makeRequest($endpoint, $data, true);
                if (isset($response['id'])) {
                    $photoIds[] = ['media_fbid' => $response['id']];
                }
            }

            // Then create a post with all the photos
            if (!empty($photoIds)) {
                $endpoint = self::API_BASE_URL . $this->apiVersion . '/' . $this->pageId . '/feed';

                $data = [
                    'message' => $message,
                    'access_token' => $this->accessToken,
                    'attached_media' => json_encode($photoIds)
                ];

                return $this->makeRequest($endpoint, $data);
            }

            throw new \RuntimeException('Failed to upload photos');
        }
    }

    /**
     * Get user/page information
     */
    public function me(): array
    {
        if (!$this->accessToken) {
            throw new \RuntimeException('Access Token is required');
        }

        $endpoint = self::API_BASE_URL . $this->apiVersion . '/me';
        
        $params = http_build_query([
            'access_token' => $this->accessToken,
            'fields' => 'id,name'
        ]);

        $ch = curl_init($endpoint . '?' . $params);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new \RuntimeException('cURL error: ' . $error);
        }

        $result = json_decode($response, true);

        if ($httpCode >= 400 || isset($result['error'])) {
            $errorMessage = $result['error']['message'] ?? 'Unknown error occurred';
            throw new \RuntimeException('Facebook API error: ' . $errorMessage);
        }

        return ['data' => $result];
    }

    /**
     * Make HTTP request to Facebook API
     */
    private function makeRequest(string $endpoint, array $data, bool $isMultipart = false): array
    {
        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $isMultipart ? $data : http_build_query($data));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        if (!$isMultipart) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/x-www-form-urlencoded'
            ]);
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new \RuntimeException('cURL error: ' . $error);
        }

        $result = json_decode($response, true);

        if ($httpCode >= 400 || isset($result['error'])) {
            $errorMessage = $result['error']['message'] ?? 'Unknown error occurred';
            throw new \RuntimeException('Facebook API error: ' . $errorMessage);
        }

        return $result;
    }
}