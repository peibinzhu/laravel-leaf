<?php

declare(strict_types=1);

namespace PeibinLaravel\Leaf;

interface IDGen
{
    public function init(): bool;

    public function get(string $key): mixed;
}
