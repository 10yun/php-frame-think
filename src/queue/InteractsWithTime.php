<?php

namespace shiyunQueue;

use DateInterval;
use DateTimeInterface;
use DateTime;

trait InteractsWithTime
{
    /**
     * 获取到给定DateTime的秒数
     *
     * @param DateTimeInterface|DateInterval|int $delay
     * @return int
     */
    protected function secondsUntil($delay): int
    {
        $delay = $this->parseDateInterval($delay);
        // 添加实际秒数
        if ($delay instanceof DateTimeInterface) {
            $timestamp = max(0, $delay->getTimestamp() - $this->currentTime());
        } else {
            $timestamp = (int) $delay;
        }
        return $timestamp;
    }

    /**
     * 获取“available at”UNIX时间戳
     *
     * @param DateTimeInterface|DateInterval|int $delay
     * @return int
     */
    protected function availableAt($delay = 0): int
    {
        $delay = $this->parseDateInterval($delay);

        if ($delay instanceof DateTimeInterface) {
            $timestamp = $delay->getTimestamp();
        } else {
            $interval = new DateInterval("PT{$delay}S"); // 创建间隔对象，单位为秒
            $future = (new DateTime())->add($interval); // 计算间隔后的日期和时间
            $timestamp = $future->getTimestamp(); // 获取时间戳
        }
        return $timestamp;
    }

    /**
     * 如果给定的值是一个间隔，请将其转换为DateTime实例
     *
     * @param DateTimeInterface|DateInterval|int $delay
     * @return DateTimeInterface|int
     */
    protected function parseDateInterval($delay)
    {
        if ($delay instanceof DateInterval) {
            $delay = (new DateTime())->add($delay);
        }
        return $delay;
    }

    /**
     * 获取当前系统时间作为UNIX时间戳
     * 当前日期和时间
     * @return int
     */
    protected function currentTime(): int
    {
        return (new DateTime())->getTimestamp();
    }
}
