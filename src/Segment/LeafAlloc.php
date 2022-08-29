<?php

declare(strict_types=1);

namespace PeibinLaravel\Leaf\Segment;

class LeafAlloc
{
    private string $key;

    private int $maxId;

    private int $step;

    private int $updateTime;

    public function getKey(): string
    {
        return $this->key;
    }

    public function setKey(string $key): static
    {
        $this->key = $key;
        return $this;
    }

    public function getMaxId(): int
    {
        return $this->maxId;
    }

    public function setMaxId(int $maxId): static
    {
        $this->maxId = $maxId;
        return $this;
    }

    public function getStep(): int
    {
        return $this->step;
    }

    public function setStep(int $step): static
    {
        $this->step = $step;
        return $this;
    }

    public function getUpdateTime(): int
    {
        return $this->updateTime;
    }

    public function setUpdateTime(int $updateTime): static
    {
        $this->updateTime = $updateTime;
        return $this;
    }
}
