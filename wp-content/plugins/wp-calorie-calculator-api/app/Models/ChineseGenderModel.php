<?php
namespace Calculator\Models;
use Calculator\Models\AbstractModel;
use Crlt_\LunarCalendar\LunarCalendar; 

class ChineseGenderModel extends AbstractModel
{
    
    public function calculate($data)
    {
        $result = [];

        $date = new \DateTime();

        $format = $date->format('Y-m-d');   

        $stTime = strtotime($format);

        $currentDay = date("d", $stTime);
        $currentMonth = date("m", $stTime);
        $currentYear = date("y", $stTime);
        $lunarTime = $this->convertLunarTime($currentYear, $currentMonth, $currentDay);
        

        $dueTime = strtotime($data['dd']);
        $dueDay = date("d", $stTime);
        $dueMonth = date("m", $stTime);
        $dueYear = date("y", $stTime);
        $dueLunar = $this->convertLunarTime($dueYear, $dueMonth, $dueDay);

        $birthDate = strtotime($data['dob']);
        $dobDay = date("d", $birthDate);
        $dobMonth = date("m", $birthDate);
        $dobYear = date("y", $birthDate);
        $dobLunar = $this->convertLunarTime($dobYear, $dobMonth, $dobDay);


        $strLunar = $lunarTime->lunarYear .'-'.$lunarTime->lunarMonth.'-'.$lunarTime->lunarDay;
        $strDob = $dobLunar->lunarYear .'-'.$dobLunar->lunarMonth.'-'.$dobLunar->lunarDay;

        $age = $this->convertLunarAge($strLunar, $strDob);

        $monthDueLunar = $dueLunar->lunarMonth;

        $chart = $this->chineseGenderChart();

        $result['gender'] = $chart[$age][$monthDueLunar-1];

        return $result;
    }

    private function convertLunarAge($currentDate, $dob)
    {   
        $currentDate = new \DateTime($currentDate);
        $dob = new \DateTime($dob);

        $year = $currentDate->diff($dob)->y;

        return $year;

    }

    private function convertLunarTime($currentYear, $currentMonth, $currentDay)
    {
        $lunar = new LunarCalendar();
        $lunarTime = $lunar->toLunar($currentYear, $currentMonth, $currentDay);
        return json_decode($lunarTime);
    }

   
    private function chineseGenderChart()
    {
        return [
            18 => [
                'Girl', 'Boy', 'Girl', 'Boy', 'Boy', 'Boy', 'Boy', 'Boy', 'Boy', 'Boy', 'Boy', 'Boy' 
            ],
            19 => [
                'Boy', 'Girl', 'Boy', 'Girl', 'Girl', 'Boy', 'Boy', 'Boy', 'Boy', 'Boy', 'Girl', 'Girl' 
            ],
            20 => [
                'Girl', 'Boy', 'Girl', 'Boy', 'Boy', 'Boy', 'Boy', 'Boy', 'Boy', 'Girl', 'Boy', 'Boy' 
            ],
            21 => [
                'Boy', 'Girl', 'Girl', 'Boy', 'Girl', 'Girl', 'Girl', 'Girl', 'Girl', 'Girl', 'Girl', 'Girl' 
            ],
            22 => [
                'Girl', 'Boy', 'Boy', 'Boy', 'Boy', 'Girl', 'Girl', 'Boy', 'Girl', 'Girl', 'Girl', 'Girl' 
            ],
            23 => [
                'Boy', 'Boy', 'Girl', 'Girl', 'Boy', 'Girl', 'Boy', 'Girl', 'Boy', 'Boy', 'Boy', 'Girl' 
            ],
            24 => [
                'Boy', 'Girl', 'Boy', 'Boy', 'Girl', 'Boy', 'Boy', 'Girl', 'Girl', 'Girl', 'Girl', 'Girl' 
            ],
            25 => [
                'Girl', 'Boy', 'Boy', 'Girl', 'Girl', 'Boy', 'Girl', 'Boy', 'Boy', 'Boy', 'Boy', 'Boy' 
            ],
            26 => [
                'Boy', 'Girl', 'Boy', 'Boy', 'Girl', 'Boy', 'Girl', 'Boy', 'Girl', 'Girl', 'Girl', 'Girl' 
            ],
            27 => [
                'Girl', 'Boy', 'Girl', 'Girl', 'Girl', 'Girl', 'Boy', 'Boy', 'Boy', 'Boy', 'Girl', 'Boy' 
            ],
            28 => [
                'Boy', 'Girl', 'Boy', 'Boy', 'Girl', 'Girl', 'Boy', 'Boy', 'Boy', 'Boy', 'Girl', 'Girl' 
            ],
            29 => [
                'Girl', 'Boy', 'Girl', 'Girl', 'Boy', 'Boy', 'Boy', 'Boy', 'Boy', 'Girl', 'Girl', 'Girl' 
            ],
            30 => [
                'Boy', 'Girl', 'Girl', 'Boy', 'Girl', 'Girl', 'Girl', 'Girl', 'Girl', 'Girl', 'Boy', 'Boy' 
            ],
            31 => [
                'Boy', 'Girl', 'Boy', 'Girl', 'Girl', 'Girl', 'Girl', 'Girl', 'Girl', 'Girl', 'Girl', 'Boy' 
            ],
            32 => [
                'Boy', 'Girl', 'Boy', 'Boy', 'Girl', 'Girl', 'Girl', 'Girl', 'Girl', 'Girl', 'Girl', 'Boy' 
            ],
            33 => [
                'Girl', 'Boy', 'Girl', 'Girl', 'Girl', 'Girl', 'Girl', 'Boy', 'Girl', 'Girl', 'Girl', 'Boy' 
            ],
            34 => [
                'Boy', 'Girl', 'Boy', 'Boy', 'Girl', 'Girl', 'Girl', 'Girl', 'Girl', 'Girl', 'Boy', 'Boy' 
            ],
            35 => [
                'Boy', 'Boy', 'Girl', 'Boy', 'Girl', 'Girl', 'Girl', 'Boy', 'Girl', 'Girl', 'Boy', 'Boy' 
            ],
            36 => [
                'Girl', 'Boy', 'Boy', 'Girl', 'Boy', 'Girl', 'Girl', 'Girl', 'Boy', 'Boy', 'Boy', 'Boy' 
            ],
            37 => [
                'Boy', 'Girl', 'Boy', 'Boy', 'Girl', 'Boy', 'Girl', 'Boy', 'Girl', 'Boy', 'Girl', 'Boy' 
            ],
            38 => [
                'Girl', 'Boy', 'Girl', 'Girl', 'Boy', 'Girl', 'Boy', 'Girl', 'Boy', 'Girl', 'Boy', 'Girl' 
            ],
            39 => [
                'Boy', 'Girl', 'Boy', 'Boy', 'Boy', 'Girl', 'Girl', 'Boy', 'Girl', 'Boy', 'Girl', 'Girl' 
            ],
            40 => [
                'Girl', 'Boy', 'Girl', 'Boy', 'Girl', 'Boy', 'Boy', 'Girl', 'Boy', 'Girl', 'Boy', 'Girl' 
            ],
            41 => [
                'Boy', 'Girl', 'Boy', 'Girl', 'Boy', 'Girl', 'Boy', 'Boy', 'Girl', 'Boy', 'Girl', 'Boy' 
            ],
            42 => [
                'Girl', 'Boy', 'Girl', 'Boy', 'Girl', 'Boy', 'Girl', 'Boy', 'Boy', 'Girl', 'Boy', 'Girl' 
            ],
            43 => [
                'Boy', 'Girl', 'Boy', 'Girl', 'Boy', 'Girl', 'Boy', 'Girl', 'Boy', 'Boy', 'Boy', 'Boy' 
            ],
            44 => [
                'Boy', 'Boy', 'Girl', 'Boy', 'Boy', 'Boy', 'Girl', 'Boy', 'Girl', 'Boy', 'Girl', 'Girl' 
            ],
            45 => [
                'Girl', 'Boy', 'Boy', 'Girl', 'Girl', 'Girl', 'Boy', 'Girl', 'Boy', 'Girl', 'Boy', 'Boy' 
            ]
        ];
    }
}