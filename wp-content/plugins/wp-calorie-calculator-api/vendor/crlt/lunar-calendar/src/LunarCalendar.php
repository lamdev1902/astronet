<?php
/**
 * Created by PhpStorm.
 * User: crlt_
 * Date: 2018/1/12
 * Time: 下午3:32
 */

namespace Crlt_\LunarCalendar;


//include ("common/Utils.php");


/**
 * Class LunarCalendar
 * @package Crlt_\LunarCalendar
 */
class LunarCalendar
{

    /**
     * LunarCalendar constructor.
     */
    public function __construct()
    {
        date_default_timezone_set("PRC");
    }


    /**
     * @param int $year 公历年
     * @param int $month 公历月
     * @param int $day 公历日
     * @return array
     */
    public function toLunar($year, $month, $day)
    {
        $utils=new Utils();

        $dateStr = "{$year}-{$month}-{$day}";
        $date = $utils->makeDate($dateStr);

        /**
         * ?
         */
        list($year, $month, $day) = explode('-', $date->format('Y-n-j'));


        /**
         * 时间范围约定
         *
         * 1900 1.31 ~ 2100 12.31
         */
        if ($year < 1900 || $year > 2100) {
            throw new InvalidArgumentException("不支持的年份：{$year}");
        }
        if ($year == 1900 && $month == 1 && $day < 31) {
            throw new InvalidArgumentException("不支持的日期:{$year}-{$month}-{$day}");
        }


        $offset = $utils->dateDiff($date, '1900-1-31')->days;

        for ($i = 1900; $i < 2101 && $offset > 0; ++$i) {
            $daysOfYear = $utils->daysOfYear($i);
            $offset -= $daysOfYear;
        }

        if ($offset < 0) {
            $offset += $daysOfYear;
            --$i;
        }
        /**
         * 农历年
         */
        $lunarYear = $i;


        $leap = $utils->leapMonth($i); // 闰哪个月
        $isLeap = false;

        /**
         * 本年的天数减去本年之前几个月的天数就是当月第几天
         */
        for ($i = 1; $i < 13 && $offset > 0; ++$i) {

            /**
             * 闰月情况
             */
            if ($leap > 0 && $i == ($leap + 1) && !($isLeap)) {
                --$i;
                $isLeap = true;
                $daysOfMonth = $utils->leapDays($lunarYear); // 计算农历月天数
            }
            else {
                $daysOfMonth= $utils->lunarDays($lunarYear, $i);
            }

            // 解除闰月
            if ($isLeap == true && $i == ($leap + 1)) {
                $isLeap = false;
            }

            $offset -= $daysOfMonth;
        }

        // offset为0时，并且刚才计算的月份是闰月，要校正
        if ($offset == 0 && $leap > 0 && $i == $leap + 1) {
            if ($isLeap) {
                $isLeap = false;
            } else {
                $isLeap = true;
                --$i;
            }
        }

        if ($offset < 0) {
            $offset += $daysOfMonth;
            --$i;
        }

        // 农历月
        $lunarMonth = $i;
        // 农历日
        $lunarDay=$offset+1;

        // 月柱 1900 年 1 月小寒以前为 丙子月(60进制12)
        $firstNode = $utils->getTerm($lunarYear, ($month * 2 - 1)); // 返回当月「节气」为几日开始
        $secondNode = $utils->getTerm($lunarYear, ($month * 2)); // 返回当月「节气」为几日开始

        $ganZhiMonth = $utils->toGanZhi(($year - 1900) * 12 + $month + 11);



        if ($day >= $firstNode) {
            $ganZhiMonth = $utils->toGanZhi(($year - 1900) * 12 + $month + 12);
        }

        // 获取该天的节气
        $term = null;
        if ($firstNode == $day) {
            $term = $utils->GetSolarTermName($month * 2 - 2);
        }

        if ($secondNode == $day) {
            $term = $utils->GetSolarTermName($month * 2 - 1);
        }

        $dayCyclical = $utils->dateDiff("{$year}-{$month}-01", '1900-01-01')->days + 10;
        $ganZhiDay = $utils->toGanZhi($dayCyclical + $day - 1);

	return json_encode([
            'lunarYear' => $lunarYear,
            'lunarMonth' => str_pad($lunarMonth, 2, '0', STR_PAD_LEFT),
            'lunarDay' => str_pad($lunarDay, 2, '0', STR_PAD_LEFT),
            'lunarMonthChinese' => ($isLeap ? '闰' : '').$utils->toChinaMonth($lunarMonth),
            'lunarDayChinese' => $utils->toChinaDay($lunarDay),
            'ganzhiYear' => $utils->ganZhiYear($lunarYear),
            'ganzhiMonth' => str_pad($ganZhiMonth, 2, '0', STR_PAD_LEFT),
            'ganzhiDay' => str_pad($ganZhiDay, 2, '0', STR_PAD_LEFT),
            'chineseZodiac' => $utils->getChineseZodiac($lunarYear),
            'term' => $term,
            'is_leap' => $isLeap,
        ]);
    }




}
