<?php
// phpcs:ignoreFile

declare(strict_types=1);

namespace Descope\SDK\Management\Password;

/**
 * Class UserPasswordMD5
 * 
 * Represents a user password hashed using the MD5 hashing scheme.
 * 
 */
class UserPasswordMD5
{
    public string $hash;

    /**
     * Constructor to initialize MD5 password details.
     *
     * @param string $hash The MD5 hash in plaintext format.
     */
    public function __construct(string $hash)
    {
        $this->hash = $hash;
    }

    /**
     * Convert object data to an array format.
     *
     * @return array The password data as an associative array.
     */
    public function toArray(): array
    {
        return [
            'md5' => [
                'hash' => $this->hash,
            ],
        ];
    }
}
