<?php

namespace ba;

use DateTime;
use Throwable;
use DateTimeZone;
use DateTimeInterface;

/**
 * 日期时间处理类
 * @form https://gitee.com/karson/fastadmin/blob/develop/extend/fast/Date.php
 */
class Date
{
    private const YEAR   = 31536000;
    private const MONTH  = 2592000;
    private const WEEK   = 604800;
    private const DAY    = 86400;
    private const HOUR   = 3600;
    private const MINUTE = 60;

    /**
     * 计算两个时区间相差的时长,单位为秒
     *
     * [!!] A list of time zones that PHP supports can be found at
     * <http://php.net/timezones>.
     * @param string          $remote timezone that to find the offset of
     * @param string|null     $local  timezone used as the baseline
     * @param string|int|null $now    UNIX timestamp or date string
     * @return  int
     * @throws Throwable
     * @example $seconds = self::offset('America/Chicago', 'GMT');
     */
    public static function offset(string $remote, ?string $local = null, string|int|null $now = null): int
    {
        if ($local === null) {
            // Use the default timezone
            $local = date_default_timezone_get();
        }
        if (is_int($now)) {
            // Convert the timestamp into a string
            $now = date(DateTimeInterface::RFC2822, $now);
        }
        // Create timezone objects
        $zone_remote = new DateTimeZone($remote);
        $zone_local  = new DateTimeZone($local);
        // Create date objects from timezones
        $time_remote = new DateTime($now, $zone_remote);
        $time_local  = new DateTime($now, $zone_local);
        // Find the offset
        return $zone_remote->getOffset($time_remote) - $zone_local->getOffset($time_local);
    }

    /**
     * 计算两个时间戳之间相差的时间
     *
     * $span = self::span(60, 182, 'minutes,seconds'); // array('minutes' => 2, 'seconds' => 2)
     * $span = self::span(60, 182, 'minutes'); // 2
     *
     * @param int      $remote timestamp to find the span of
     * @param int|null $local  timestamp to use as the baseline
     * @param string   $output formatting string
     * @return  bool|array|string    associative list of all outputs requested|when only a single output is requested
     * @from https://github.com/kohana/ohanzee-helpers/blob/master/src/Date.php
     */
    public static function span(int $remote, ?int $local = null, string $output = 'years,months,weeks,days,hours,minutes,seconds'): bool|array|string
    {
        // Normalize output
        $output = trim(strtolower($output));
        if (!$output) {
            // Invalid output
            return false;
        }
        // Array with the output formats
        $output = preg_split('/[^a-z]+/', $output);
        // Convert the list of outputs to an associative array
        $output = array_combine($output, array_fill(0, count($output), 0));
        // Make the output values into keys
        extract(array_flip($output), EXTR_SKIP);
        if ($local === null) {
            // Calculate the span from the current time
            $local = time();
        }
        // Calculate timespan (seconds)
        $timespan = abs($remote - $local);
        if (isset($output['years'])) {
            $timespan -= self::YEAR * ($output['years'] = (int)floor($timespan / self::YEAR));
        }
        if (isset($output['months'])) {
            $timespan -= self::MONTH * ($output['months'] = (int)floor($timespan / self::MONTH));
        }
        if (isset($output['weeks'])) {
            $timespan -= self::WEEK * ($output['weeks'] = (int)floor($timespan / self::WEEK));
        }
        if (isset($output['days'])) {
            $timespan -= self::DAY * ($output['days'] = (int)floor($timespan / self::DAY));
        }
        if (isset($output['hours'])) {
            $timespan -= self::HOUR * ($output['hours'] = (int)floor($timespan / self::HOUR));
        }
        if (isset($output['minutes'])) {
            $timespan -= self::MINUTE * ($output['minutes'] = (int)floor($timespan / self::MINUTE));
        }
        // Seconds ago, 1
        if (isset($output['seconds'])) {
            $output['seconds'] = $timespan;
        }
        if (count($output) === 1) {
            // Only a single output was requested, return it
            return array_pop($output);
        }
        // Return array
        return $output;
    }

    /**
     * 格式化 UNIX 时间戳为人易读的字符串
     *
     * @param int  $remote Unix 时间戳
     * @param ?int $local  本地时间戳
     * @return string 格式化的日期字符串
     */
    public static function human(int $remote, ?int $local = null): string
    {
        $timeDiff = (is_null($local) ? time() : $local) - $remote;
        $tense    = $timeDiff < 0 ? 'after' : 'ago';
        $timeDiff = abs($timeDiff);
        $chunks   = [
            [60 * 60 * 24 * 365, 'year'],
            [60 * 60 * 24 * 30, 'month'],
            [60 * 60 * 24 * 7, 'week'],
            [60 * 60 * 24, 'day'],
            [60 * 60, 'hour'],
            [60, 'minute'],
            [1, 'second'],
        ];

        $count = 0;
        $name  = '';
        for ($i = 0, $j = count($chunks); $i < $j; $i++) {
            $seconds = $chunks[$i][0];
            $name    = $chunks[$i][1];
            if (($count = floor($timeDiff / $seconds)) != 0) {
                break;
            }
        }
        return __("%d $name%s $tense", [$count, $count > 1 ? 's' : '']);
    }

    /**
     * 获取一个基于时间偏移的Unix时间戳
     *
     * @param string   $type     时间类型，默认为day，可选minute,hour,day,week,month,quarter,year
     * @param int      $offset   时间偏移量 默认为0，正数表示当前type之后，负数表示当前type之前
     * @param string   $position 时间的开始或结束，默认为begin，可选前(begin,start,first,front)，end
     * @param int|null $year     基准年，默认为null，即以当前年为基准
     * @param int|null $month    基准月，默认为null，即以当前月为基准
     * @param int|null $day      基准天，默认为null，即以当前天为基准
     * @param int|null $hour     基准小时，默认为null，即以当前年小时基准
     * @param int|null $minute   基准分钟，默认为null，即以当前分钟为基准
     * @return int 处理后的Unix时间戳
     */
    public static function unixTime(string $type = 'day', int $offset = 0, string $position = 'begin', ?int $year = null, ?int $month = null, ?int $day = null, ?int $hour = null, ?int $minute = null): int
    {
        $year     = is_null($year) ? date('Y') : $year;
        $month    = is_null($month) ? date('m') : $month;
        $day      = is_null($day) ? date('d') : $day;
        $hour     = is_null($hour) ? date('H') : $hour;
        $minute   = is_null($minute) ? date('i') : $minute;
        $position = in_array($position, ['begin', 'start', 'first', 'front']);

        return match ($type) {
            'minute' => $position ? mktime($hour, $minute + $offset, 0, $month, $day, $year) : mktime($hour, $minute + $offset, 59, $month, $day, $year),
            'hour' => $position ? mktime($hour + $offset, 0, 0, $month, $day, $year) : mktime($hour + $offset, 59, 59, $month, $day, $year),
            'day' => $position ? mktime(0, 0, 0, $month, $day + $offset, $year) : mktime(23, 59, 59, $month, $day + $offset, $year),
            // 使用固定的 this week monday 而不是 $offset weeks monday 的语法才能确保准确性
            'week' => $position ? strtotime('this week monday', mktime(0, 0, 0, $month, $day + ($offset * 7), $year)) : strtotime('this week sunday 23:59:59', mktime(0, 0, 0, $month, $day + ($offset * 7), $year)),
            'month' => $position ? mktime(0, 0, 0, $month + $offset, 1, $year) : mktime(23, 59, 59, $month + $offset, self::daysInMonth($month + $offset, $year), $year),
            'quarter' => $position ?
                mktime(0, 0, 0, 1 + ((ceil(date('n', mktime(0, 0, 0, $month, $day, $year)) / 3) + $offset) - 1) * 3, 1, $year) :
                mktime(23, 59, 59, (ceil(date('n', mktime(0, 0, 0, $month, $day, $year)) / 3) + $offset) * 3, self::daysInMonth((ceil(date('n', mktime(0, 0, 0, $month, $day, $year)) / 3) + $offset) * 3, $year), $year),
            'year' => $position ? mktime(0, 0, 0, 1, 1, $year + $offset) : mktime(23, 59, 59, 12, 31, $year + $offset),
            default => mktime($hour, $minute, 0, $month, $day, $year),
        };
    }

    /**
     * 获取给定月份的天数 （28 到 31）
     */
    public static function daysInMonth(int $month, ?int $year = null): int
    {
        return (int)date('t', mktime(0, 0, 0, $month, 1, $year));
    }
}