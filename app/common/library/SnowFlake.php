<?php

namespace app\common\library;

/**
 * 雪花ID生成类
 */
class SnowFlake
{
    /**
     * 起始时间戳
     * @var int
     */
    private const EPOCH = 1672502400000;

    /**
     * @var int
     */
    private const max41bit = 1099511627775;

    /**
     * 机器节点 10bit
     * @var int
     */
    protected static int $machineId = 1;

    /**
     * 序列号
     * @var int
     */
    protected static int $count = 0;

    /**
     * 最后一次生成ID的时间偏移量
     * @var int
     */
    protected static int $last = 0;

    /**
     * 设置机器节点
     * @param int $mId 机器节点id
     * @return void
     */
    public static function setMachineId(int $mId): void
    {
        self::$machineId = $mId;
    }

    /**
     * 生成雪花ID
     * @return float|int
     */
    public static function generateParticle(): float|int
    {
        // 当前时间 42bit
        $time = (int)floor(microtime(true) * 1000);

        // 时间偏移量
        $time -= self::EPOCH;

        // 起始时间戳加上时间偏移量并转为二进制
        $base = decbin(self::max41bit + $time);

        // 追加节点机器id
        if (!is_null(self::$machineId)) {
            $machineId = str_pad(decbin(self::$machineId), 10, "0", STR_PAD_LEFT);
            $base      .= $machineId;
        }

        // 序列号
        if ($time == self::$last) {
            self::$count++;
        } else {
            self::$count = 0;
        }

        // 追加序列号部分
        $sequence = str_pad(decbin(self::$count), 12, "0", STR_PAD_LEFT);
        $base     .= $sequence;

        // 保存生成ID的时间偏移量
        self::$last = $time;

        // 返回64bit二进制数的十进制标识
        return bindec($base);
    }
}
