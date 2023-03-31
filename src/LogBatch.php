<?php

namespace AwStudio\Logbook;

use Ramsey\Uuid\Uuid;

class LogBatch
{
    public ?string $uuid = null;

    public int $transactions = 0;

    public ?string $name = null;

    protected function generateUuid(): string
    {
        return Uuid::uuid4()->toString();
    }

    public function getUuid(): ?string
    {
        return $this->uuid;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function start($name = null): void
    {
        if (! $this->isOpen()) {
            $this->uuid = $this->generateUuid();
            $this->name = $name;
        }

        $this->transactions++;
    }

    public function isOpen(): bool
    {
        return $this->transactions > 0;
    }

    public function end(): void
    {
        $this->transactions = max(0, $this->transactions - 1);

        if ($this->transactions === 0) {
            $this->uuid = null;
        }
    }
}
