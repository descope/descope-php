<?php
// phpcs:ignoreFile

declare(strict_types=1);

namespace Descope\SDK\Management\Password;

/**
 * Class UserPasswordSha
 * 
 * Represents a user password hashed using the SHA algorithm.
 */
class UserPasswordSha
{
    public string $hash;
    public string $type;

    /**
     * Constructor to initialize SHA password details.
     *
     * @param string $hash The SHA hash in plaintext format.
     * @param string $type The SHA variant.
     */
    public function __construct(
        string $hash,
        string $type
    ) {
        $this->hash = $hash;
        $this->type = $type;
    }

    /**
     * Convert object data to an array format.
     *
     * @return array The password data as an associative array.
     */
    public function toArray(): array
    {
        return [
            'sha' => [
                'hash' => $this->hash,
                'type' => $this->type,
            ],
        ];
    }
}
