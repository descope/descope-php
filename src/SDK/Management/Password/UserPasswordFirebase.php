<?php
// phpcs:ignoreFile

declare(strict_types=1);

namespace Descope\SDK\Management\Password;

/**
 * Class UserPasswordFirebase
 * 
 * Represents a user password hashed using Firebase's custom hashing scheme.
 */
class UserPasswordFirebase
{
    public string $hash;
    public string $salt;
    public string $saltSeparator;
    public string $signerKey;
    public int $memory;
    public int $rounds;

    /**
     * Constructor to initialize Firebase password details.
     *
     * @param string $hash Base64-encoded hash.
     * @param string $salt Base64-encoded salt.
     * @param string $saltSeparator Base64-encoded salt separator.
     * @param string $signerKey Base64-encoded signer key.
     * @param int $memory Memory cost (between 12 and 17).
     * @param int $rounds Rounds cost (between 6 and 10).
     */
    public function __construct(
        string $hash,
        string $salt,
        string $saltSeparator,
        string $signerKey,
        int $memory,
        int $rounds
    ) {
        $this->hash = $hash;
        $this->salt = $salt;
        $this->saltSeparator = $saltSeparator;
        $this->signerKey = $signerKey;
        $this->memory = $memory;
        $this->rounds = $rounds;
    }

    /**
     * Convert object data to an array format.
     *
     * @return array The password data as an associative array.
     */
    public function toArray(): array
    {
        return [
            'firebase' => [
                'hash' => $this->hash,
                'salt' => $this->salt,
                'saltSeparator' => $this->saltSeparator,
                'signerKey' => $this->signerKey,
                'memory' => $this->memory,
                'rounds' => $this->rounds,
            ],
        ];
    }
}