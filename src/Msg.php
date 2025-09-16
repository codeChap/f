<?php

namespace codechap\f;

class Msg
{
    /**
     * Message content/text
     */
    private string $content = '';

    /**
     * Image file path
     */
    private string $image = '';

    /**
     * Set a property value
     */
    public function set(string $key, string $value): self
    {
        switch ($key) {
            case 'content':
                $this->content = $value;
                break;
            case 'image':
                $this->image = $value;
                break;
            default:
                throw new \InvalidArgumentException("Unknown property: {$key}");
        }

        return $this;
    }

    /**
     * Get a property value
     */
    public function get(string $key): string
    {
        switch ($key) {
            case 'content':
                return $this->content;
            case 'image':
                return $this->image;
            default:
                throw new \InvalidArgumentException("Unknown property: {$key}");
        }
    }

    /**
     * Check if message has content
     */
    public function hasContent(): bool
    {
        return !empty($this->content);
    }

    /**
     * Check if message has an image
     */
    public function hasImage(): bool
    {
        return !empty($this->image) && file_exists($this->image);
    }
}
