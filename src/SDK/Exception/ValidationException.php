<?php

declare(strict_types=1);

namespace Descope\SDK\Exception;

use Exception;
use Throwable;

final class ValidationException extends Exception implements DescopeException
{
    /**
     * @var string
     */
    public const MSG_REFRESH_TOKEN_MISSING = 'Refresh token cannot be null or empty.';

    /**
     * @var string
     */
    public const MSG_SESSION_TOKEN_MISSING = 'Session token cannot be null or empty.';

    /**
     * @var string
     */
    public const MSG_INVALID_TOKEN_TYPE = 'Invalid token type provided.';

    /**
     * Factory method to create an exception for a missing refresh token.
     *
     * @return self
     */
    public static function forMissingRefreshToken(): self
    {
        return new self(self::MSG_REFRESH_TOKEN_MISSING);
    }

    /**
     * Factory method to create an exception for a missing session token.
     *
     * @return self
     */
    public static function forMissingSessionToken(): self
    {
        return new self(self::MSG_SESSION_TOKEN_MISSING);
    }

    /**
     * Factory method to create an exception for an invalid token type.
     *
     * @param string $tokenType The invalid token type.
     * @return self
     */
    public static function forInvalidTokenType(string $tokenType): self
    {
        return new self(sprintf('%s: %s', self::MSG_INVALID_TOKEN_TYPE, $tokenType));
    }
}
