<?php
// phpcs:ignoreFile

declare(strict_types=1);

namespace Descope\SDK\Management;

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
