<?php

declare(strict_types=1);

namespace PeibinLaravel\Leaf\Contracts;

use PeibinLaravel\Leaf\Segment\LeafAlloc;

interface IDAllocModel
{
    public function getLeafAlloc(string $tag): ?LeafAlloc;

    public function updateMaxIdAndGetLeafAlloc(string $tag): ?LeafAlloc;
}
