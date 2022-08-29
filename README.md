Laravel leaf 分布式ID组件
=======

## 安装

运行下面命令进行安装

```sh
composer require peibin/laravel-leaf
```

请执行下面sql语句建立leaf数据表

```sql
CREATE
TABLE `leaf_alloc` (
  `biz_tag` varchar(128) NOT NULL DEFAULT '' COMMENT '业务key',
  `max_id` bigint(20) unsigned NOT NULL DEFAULT '1' COMMENT '当前已经分配了的最大id',
  `step` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '初始步长，也是动态调整的最小步长',
  `description` varchar(255) NOT NULL DEFAULT '' COMMENT '业务key的描述',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`biz_tag`),
  UNIQUE KEY `idx_biz_tag` (`biz_tag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Leaf分布式id号段分配表';
```

模型需实现 `IDAllocModel` 接口

模型方法 `getLeafAlloc` SQL语句：

```sql
SELECT `biz_tag`, `max_id`, `step`, `update_time`
FROM `leaf_alloc`
WHERE biz_tag = 'TagName';
```

模型方法 `updateMaxIdAndGetLeafAlloc` SQL语句：

```sql
UPDATE `leaf_alloc`
SET max_id      = max_id + step,
    update_time = Time
WHERE biz_tag = 'TagName';
```

## 使用方法

```php
// 初始化，定义成单例
$this->app->singleton(SegmentIDGenImpl::class, function ($app) {
    $idGen = new SegmentIDGenImpl($app);
    $idGen->setModel(new LeafAllocModel());
    $idGen->init();
    return $idGen;
});

// 调用方法
function segmentId(string $tag): Result
{
    $segment = app()->make(SegmentIDGenImpl::class)->get($tag);
    if ($segment->getStatus() == Result::STATUS_EXCEPTION) {
        throw new \RuntimeException('The generation number segment id is abnormal. CODE:' . $segment->getId());
    }
    return $segment;
}

$result = segmentId('test_tag');
```
