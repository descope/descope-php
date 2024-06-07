<?php

declare(strict_types=1);

namespace Descope\SDK\Exception;

use Exception;
use Throwable;

final class AuthException extends Exception implements DescopeException
{
    private ?int $statusCode;
    private ?string $errorType;
    private ?string $errorMessage;

    public function __construct(
        ?int $statusCode = null,
        ?string $errorType = null,
        ?string $errorMessage = null,
        array $additionalParams = [],
        Throwable $previous = null
    ) {
        $this->statusCode = $statusCode;
        $this->errorType = $errorType;
        $this->errorMessage = $errorMessage;
        parent::__construct($errorMessage, 0, $previous);
    }

    public function __toString(): string
    {
        return json_encode(
            [
            'statusCode' => $this->statusCode,
            'errorType' => $this->errorType,
            'errorMessage' => $this->errorMessage
            ]
        );
    }
}

final class RateLimitException extends Exception implements DescopeException
{
    private ?int $statusCode;
    private ?string $errorType;
    private ?string $errorDescription;
    private ?string $errorMessage;
    private array $rateLimitParameters;

    public function __construct(
        ?int $statusCode = null,
        ?string $errorType = null,
        ?string $errorDescription = null,
        ?string $errorMessage = null,
        array $rateLimitParameters = [],
        array $additionalParams = [],
        Throwable $previous = null
    ) {
        $this->statusCode = $statusCode;
        $this->errorType = $errorType;
        $this->errorDescription = $errorDescription;
        $this->errorMessage = $errorMessage;
        $this->rateLimitParameters = $rateLimitParameters;
        parent::__construct($errorMessage, 0, $previous);
    }

    public function __toString(): string
    {
        return json_encode(
            [
            'statusCode' => $this->statusCode,
            'errorType' => $this->errorType,
            'errorDescription' => $this->errorDescription,
            'errorMessage' => $this->errorMessage,
            'rateLimitParameters' => $this->rateLimitParameters
            ]
        );
    }
}
