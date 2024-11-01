<?php
// phpcs:ignoreFile

declare(strict_types=1);

namespace Descope\SDK\Management\Password;

/**
 * Class UserPassword
 * 
 * Represents either a cleartext password or a hashed password.
 * This is used when creating or inviting users with a password.
 */
class UserPassword
{
    public ?string $cleartext;
    public ?object $hashed;

    /**
     * Constructor to initialize password details.
     * Either cleartext or hashed password should be provided, not both.
     *
     * @param string|null $cleartext Plaintext password.
     * @param object|null $hashed Hashed password object (one of the above classes).
     */
    public function __construct(?string $cleartext = null, ?object $hashed = null)
    {
        if (empty($cleartext) && $hashed === null) {
            throw new \InvalidArgumentException('Either cleartext or hashed password must be provided.');
        }
    
        if (!empty($cleartext) && $hashed !== null) {
            throw new \InvalidArgumentException('Provide only one: cleartext or hashed password.');
        }
        
        $this->cleartext = $cleartext;
        $this->hashed = $hashed;
    }

    /**
     * Convert object data to an array format.
     *
     * @return array The password data as an associative array.
     */
    public function toArray(): array
    {
        $data = [];
        if ($this->cleartext !== null) {
            $data['cleartext'] = $this->cleartext;
        }
        if ($this->hashed !== null) {
            $data['hashed'] = $this->hashed->toArray();
        }
        return $data;
    }
}
