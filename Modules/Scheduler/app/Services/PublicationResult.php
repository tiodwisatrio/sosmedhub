<?php

namespace Modules\Scheduler\Services;

/**
 * Hasil satu langkah PublicationRunner.
 */
final class PublicationResult
{
    public const DONE = 'done';

    public const WAITING = 'waiting';

    public const FAILED = 'failed';

    private function __construct(
        public readonly string $status,
        public readonly bool $authFailed = false,
    ) {}

    public static function done(): self
    {
        return new self(self::DONE);
    }

    public static function waiting(): self
    {
        return new self(self::WAITING);
    }

    public static function failed(bool $authFailed = false): self
    {
        return new self(self::FAILED, $authFailed);
    }

    public function isWaiting(): bool
    {
        return $this->status === self::WAITING;
    }

    public function isFinal(): bool
    {
        return $this->status !== self::WAITING;
    }
}
