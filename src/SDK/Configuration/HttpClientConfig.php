<?php

declare(strict_types=1);

namespace Descope\SDK\Configuration;

final class HttpClientConfig
{
    public const DEFAULT_REQUEST_TIMEOUT_SECONDS = 60.0;
    public const DEFAULT_CONNECT_TIMEOUT_SECONDS = 10.0;

    private $requestTimeout;
    private $managementRequestTimeout;
    private $connectTimeout;

    public function __construct(
        float $requestTimeout = self::DEFAULT_REQUEST_TIMEOUT_SECONDS,
        ?float $managementRequestTimeout = null,
        float $connectTimeout = self::DEFAULT_CONNECT_TIMEOUT_SECONDS
    ) {
        $this->requestTimeout = self::validateTimeout('requestTimeout', $requestTimeout);
        $this->managementRequestTimeout = self::validateTimeout(
            'managementRequestTimeout',
            $managementRequestTimeout ?? $requestTimeout
        );
        $this->connectTimeout = self::validateTimeout('connectTimeout', $connectTimeout);
    }

    public static function fromArray(array $config): self
    {
        return new self(
            self::configValue($config, 'requestTimeout', self::DEFAULT_REQUEST_TIMEOUT_SECONDS),
            isset($config['managementRequestTimeout'])
                ? self::configValue($config, 'managementRequestTimeout')
                : null,
            self::configValue($config, 'connectTimeout', self::DEFAULT_CONNECT_TIMEOUT_SECONDS)
        );
    }

    public function requestTimeout(bool $managementRequest = false): float
    {
        return $managementRequest ? $this->managementRequestTimeout : $this->requestTimeout;
    }

    public function connectTimeout(): float
    {
        return $this->connectTimeout;
    }

    public function requestOptions(float $remainingTime): array
    {
        $remainingTime = max(0.001, $remainingTime);

        return [
            'timeout' => $remainingTime,
            'connect_timeout' => min($this->connectTimeout(), $remainingTime),
        ];
    }

    private static function configValue(array $config, string $key, ?float $default = null): float
    {
        $value = $config[$key] ?? $default;
        if (is_string($value) && is_numeric($value)) {
            $value = (float) $value;
        }
        if (!is_int($value) && !is_float($value)) {
            throw new \InvalidArgumentException(sprintf('%s must be a positive number of seconds.', $key));
        }

        return (float) $value;
    }

    private static function validateTimeout(string $name, float $timeout): float
    {
        if (!is_finite($timeout) || $timeout <= 0) {
            throw new \InvalidArgumentException(sprintf('%s must be a positive number of seconds.', $name));
        }

        return $timeout;
    }
}
