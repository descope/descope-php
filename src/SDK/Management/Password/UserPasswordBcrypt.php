<?php
// phpcs:ignoreFile

declare(strict_types=1);

namespace Descope\SDK\Management\Password;

/**
 * Class UserPasswordBcrypt
 * 
 * Represents a user password hashed using the bcrypt algorithm.
 */
class UserPasswordBcrypt
{
    public string $hash;

    /**
     * Constructor to initialize Bcrypt password details.
     *
     * @param string $hash The bcrypt hash in plaintext format (e.g., "$2a$...").
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
            'bcrypt' => [
                'hash' => $this->hash,
            ],
        ];
    }
}