<?php

declare(strict_types=1);

namespace Descope\SDK\Exception;

use Exception;
use Throwable;

final class TokenException extends Exception implements DescopeException
{
    /**
     * @var string
     */
    public const MSG_COULD_NOT_PARSE = 'The JWT string could not be parsed.';

    /**
     * @var string
     */
    public const MSG_SIGNATURE_INVALID = 'Cannot verify signature';
}