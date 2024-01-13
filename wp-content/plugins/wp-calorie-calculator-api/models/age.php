<?php 
namespace Calculator\Models;

class Age 
{
    public function ageCalculate($info)
    {
        $result = [];

        $from = new DateTime($info['from']);
        $to = new DateTime( $info['to']);

        $time = $to->diff($from);

        $year = $time->y;
        $month = $time->m;
        $day = $time->d;


        $fulltime = $year . ' years ' . $month . ' months ' . $day . ' days';

        $monthAndDay = ( $year * 12 + $month ) . ' months ' . $day . ' days ';
        
        $fullDay = $time->days;

        $weekDay = $this->weekDayCalculate($fullDay);

        $hours = $fullDay * 24;
        $mins = $hours * 60;
        $seconds = $mins * 60;

        $result['age']['fullday'] = $fulltime;
        $result['age']['monthday'] = $monthAndDay;
        $result['age']['weekday'] = $weekDay;
        $result['age']['day'] = $fullDay;
        $result['age']['hours'] = $hours;
        $result['age']['min'] = $mins;
        $result['age']['second'] = $seconds;

        return $result;
    }

    private function weekDayCalculate($days)
    {
        $week = floor($days / 7);
        $day = $days % 7;

        return $week . ' weeks ' . $day . ' days ';
    }

}