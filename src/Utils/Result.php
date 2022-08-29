<?php

declare(strict_types=1);

namespace PeibinLaravel\Leaf\Utils;

class Result
{
    public const STATUS_EXCEPTION = 0;

    public const STATUS_SUCCESS = 1;

    public function __construct(protected int $id, protected int $status)
    {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getStatus(): int
    {
        return $this->status;
    }
}
