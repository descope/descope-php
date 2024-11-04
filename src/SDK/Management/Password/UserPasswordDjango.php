<?php
// phpcs:ignoreFile

declare(strict_types=1);

namespace Descope\SDK\Management\Password;

/**
 * Class UserPasswordDjango
 * 
 * Represents a user password hashed using Django's custom hashing scheme.
 */
class UserPasswordDjango
{
    public string $hash;

    /**
     * Constructor to initialize Django password details.
     *
     * @param string $hash The django hash in plaintext format (e.g., "pbkdf2_sha256$...").
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
            'django' => [
                'hash' => $this->hash,
            ],
        ];
    }
}