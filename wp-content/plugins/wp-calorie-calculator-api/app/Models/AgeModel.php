<?php 
namespace Calculator\Models;

class AgeModel
{
    
    public function calculate($data)
    {
        $result = [];

        $from = new \DateTime($data['dob']);
        $to = new \DateTime( $data['ageat']);

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
        $result['age']['day'] = number_format($fullDay);
        $result['age']['hours'] = number_format($hours);
        $result['age']['minutes'] = number_format($mins);
        $result['age']['seconds'] = number_format($seconds);

        return $result;
    }

    private function weekDayCalculate($days)
    {
        $week = floor($days / 7);
        $day = $days % 7;

        return $week . ' weeks ' . $day . ' days ';
    }

}