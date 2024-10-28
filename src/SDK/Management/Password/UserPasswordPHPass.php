<?php
// phpcs:ignoreFile

declare(strict_types=1);

namespace Descope\SDK\Management\Password;

/**
 * Class UserPasswordPHPass
 * 
 * Represents a user password hashed using the PHPass algorithm.
 * This includes the hash, salt, and iterations used to generate the hash.
 */
class UserPasswordPHPass
{
    public string $hash;
    public string $salt;
    public int $iterations;

    /**
     * Constructor to initialize PHPass password details.
     *
     * @param string $hash Base64-encoded password hash.
     * @param string $salt Base64-encoded salt value.
     * @param int $iterations The number of iterations.
     */
    public function __construct(
        string $hash,
        string $salt,
        int $iterations
    ) {
        $this->hash = $hash;
        $this->salt = $salt;
        $this->iterations = $iterations;
    }

     /**
     * Convert object data to an array format.
     *
     * @return array The password data as an associative array.
     */
    public function toArray(): array
    {
        return [
            'phpass' => [
                'hash' => $this->hash,
                'salt' => $this->salt,
                'iterations' => $this->iterations
            ],
        ];
    }
}