<?php

declare(strict_types=1);

namespace PeibinLaravel\Leaf\Segment;

use Illuminate\Contracts\Cache\Factory;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Contracts\Container\Container;
use PeibinLaravel\Leaf\Contracts\IDAllocModel;
use PeibinLaravel\Leaf\IDGen;
use PeibinLaravel\Leaf\Utils\Result;

class SegmentIDGenImpl implements IDGen
{
    /** @var int IDCache未初始化成功时的异常码 */
    public const EXCEPTION_ID_IDCACHE_INIT_FALSE = -1;

    /** @var int key不存在时的异常码 */
    public const EXCEPTION_ID_KEY_NOT_EXISTS = -2;

    private Repository $cahce;

    private ?IDAllocModel $model;

    private bool $initOK;

    public function __construct(Container $container)
    {
        $this->initOK = false;
        $this->cahce = $container->get(Factory::class)->store('redis');
    }

    public function init(): bool
    {
        $this->initOK = true;
        return $this->initOK;
    }

    public function get(string $key): Result
    {
        // TODO: 在并发情况下有可能出现重复，待后面完善

        if (!$this->initOK) {
            return new Result(self::EXCEPTION_ID_IDCACHE_INIT_FALSE, Result::STATUS_EXCEPTION);
        }

        $id = $this->getSegmentFromCache($key) ?: $this->getSegmentFromDb($key);
        if (!$id) {
            return new Result(self::EXCEPTION_ID_KEY_NOT_EXISTS, Result::STATUS_EXCEPTION);
        }

        return new Result($id, Result::STATUS_SUCCESS);
    }

    private function getSegmentFromCache(string $key): ?int
    {
        $idKey = $this->getIdKey($key);
        $leafAllocKey = $this->getLeafAllocKey($key);
        $currentId = $this->cahce->get($idKey);
        /** @var LeafAlloc|null $conf */
        $leafAlloc = $this->cahce->get($leafAllocKey);
        if (!$currentId || !$leafAlloc) {
            return null;
        }

        $currentId = (int)$this->cahce->get($idKey);
        $this->cahce->increment($idKey);
        if ($currentId < $leafAlloc->getMaxId()) {
            return $currentId;
        }

        $this->updateSegmentFromDb($key);
        return $this->getSegmentFromCache($key);
    }

    private function updateSegmentFromDb(string $key): void
    {
        /** @var LeafAlloc|null $leafAlloc */
        $leafAlloc = $this->model->updateMaxIdAndGetLeafAlloc($key);
        if (!$leafAlloc) {
            return;
        }

        $this->updateSegmentFromCache($key, $leafAlloc->getMaxId(), $leafAlloc);
    }

    private function updateSegmentFromCache(string $key, int $maxId, LeafAlloc $leafAlloc): void
    {
        $initId = $this->cahce->get($this->getIdKey($key)) ?: $maxId;
        $this->cahce->set($this->getIdKey($key), $initId);
        $this->cahce->set($this->getLeafAllocKey($key), $leafAlloc);
    }

    private function getSegmentFromDb(string $key): ?int
    {
        $this->updateSegmentFromDb($key);
        return $this->getSegmentFromCache($key);
    }

    private function getIdKey(string $key): string
    {
        return 'LS_LEAF_ID_' . $key;
    }

    private function getLeafAllocKey(string $key): string
    {
        return 'LS_LEAF_ALLOC_' . $key;
    }

    public function getModel(): ?IDAllocModel
    {
        return $this->model;
    }

    public function setModel(IDAllocModel $model): void
    {
        $this->model = $model;
    }
}
