<?php

declare(strict_types=1);

namespace Descope\SDK\Management;

class UserPasswordPHPass
{
    private string $hash;
    private string $salt;
    private int $iterations;

    public function __construct(
        string $hash,
        string $salt,
        int $iterations
    ) {
        /**
         * The hash and salt should be base64 strings using standard encoding with padding.
         * The iterations cost value is an integer, usually in the thousands.
         */
        $this->hash = $hash;
        $this->salt = $salt;
        $this->iterations = $iterations;
    }

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

class UserPasswordBcrypt
{
    private string $hash;

    public function __construct(string $hash)
    {
        /**
         * The bcrypt hash in plaintext format, for example "$2a$..."
         */
        $this->hash = $hash;
    }

    public function toArray(): array
    {
        return [
            'bcrypt' => [
                'hash' => $this->hash,
            ],
        ];
    }
}

class UserPasswordFirebase
{
    private string $hash;
    private string $salt;
    private string $saltSeparator;
    private string $signerKey;
    private int $memory;
    private int $rounds;

    public function __construct(
        string $hash,
        string $salt,
        string $saltSeparator,
        string $signerKey,
        int $memory,
        int $rounds
    ) {
        /**
         * The hash, salt, salt separator, and signer key should be base64 strings using
         * standard encoding with padding.
         * The memory cost value is an integer, usually between 12 to 17.
         * The rounds cost value is an integer, usually between 6 to 10.
         */
        $this->hash = $hash;
        $this->salt = $salt;
        $this->saltSeparator = $saltSeparator;
        $this->signerKey = $signerKey;
        $this->memory = $memory;
        $this->rounds = $rounds;
    }

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

class UserPasswordPbkdf2
{
    private string $hash;
    private string $salt;
    private int $iterations;
    private string $variant;

    public function __construct(
        string $hash,
        string $salt,
        int $iterations,
        string $variant
    ) {
        /**
         * The hash and salt should be base64 strings using standard encoding with padding.
         * The iterations cost value is an integer, usually in the thousands.
         * The hash variant should be either "sha1", "sha256", or "sha512".
         */
        $this->hash = $hash;
        $this->salt = $salt;
        $this->iterations = $iterations;
        $this->variant = $variant;
    }

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

class UserPasswordDjango
{
    private string $hash;

    public function __construct(string $hash)
    {
        /**
         * The django hash in plaintext format, for example "pbkdf2_sha256$..."
         */
        $this->hash = $hash;
    }

    public function toArray(): array
    {
        return [
            'django' => [
                'hash' => $this->hash,
            ],
        ];
    }
}

class UserPassword
{
    private ?string $cleartext;
    private ?object $hashed;

    public function __construct(?string $cleartext = null, ?object $hashed = null)
    {
        /**
         * Set a UserPassword on UserObj objects when calling invite_batch to create or invite users
         * with a cleartext or prehashed password. Note that only one of the two options should be set.
         */
        $this->cleartext = $cleartext;
        $this->hashed = $hashed;
    }

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