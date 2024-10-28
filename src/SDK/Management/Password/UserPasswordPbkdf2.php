<?php
// phpcs:ignoreFile

declare(strict_types=1);

namespace Descope\SDK\Management\Password;

/**
 * Class UserPasswordPbkdf2
 * 
 * Represents a user password hashed using the PBKDF2 algorithm.
 */
class UserPasswordPbkdf2
{
    public string $hash;
    public string $salt;
    public int $iterations;
    public string $variant;

    /**
     * Constructor to initialize PBKDF2 password details.
     *
     * @param string $hash Base64-encoded hash.
     * @param string $salt Base64-encoded salt.
     * @param int $iterations Number of iterations (usually in the thousands).
     * @param string $variant Hash variant (sha1, sha256, or sha512).
     */
    public function __construct(
        string $hash,
        string $salt,
        int $iterations,
        string $variant
    ) {
        $this->hash = $hash;
        $this->salt = $salt;
        $this->iterations = $iterations;
        $this->variant = $variant;
    }

    /**
     * Convert object data to an array format.
     *
     * @return array The password data as an associative array.
     */
    public function toArray(): array
    {
        return [
            'pbkdf2' => [
                'hash' => $this->hash,
                'salt' => $this->salt,
                'iterations' => $this->iterations,
                'type' => $this->variant,
            ],
        ];
    }
}