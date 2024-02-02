<?php
namespace Calculator\Models;
use Calculator\Models\AbstractModel;
use Calculator\Helper\Data;

class ArmyBodyFatModel extends AbstractModel
{

    public function __construct()
    {
        $helper = new Data();
        $this->helper = $helper;
    }

    public function calculate($info)
    {
        $result = [];
        $height = $this->helper->inchesConvert($info['height']);
        $neck = $this->helper->inchesConvert($info['neck']);
        $waist = $this->helper->inchesConvert($info['waist']);

        $ageScore = $this->ageScore($info['age']);
        if($info['gender'] == 1)
        {
            $percent = round(((86.010 * log10($waist - $neck)) - (70.041 * log10($height))) + 36.76);

        }else {
            $hip = $this->helper->inchesConvert($info['hip']);
            
            $percent = round((163.205 * log10($waist + $hip - $neck)) - (97.684 * log10($height)) - 78.387);
        }

        $receipMeet = [];

        $recruimentStandard = $this->armyRecruitmentStandard($ageScore, $info['gender'], $percent);
        $bodyFatStandard = $this->armyBodyFatStandard($ageScore, $info['gender'], $percent);
        $department =  $this->departmentOfDefenseGoal($percent);

        array_push($receipMeet, $recruimentStandard, $bodyFatStandard, $department);
        
        $result['army_bodyfat']['score'] = $percent;
        $result['army_bodyfat']['description'] = '';
        foreach($receipMeet as $key => $item){
            if(isset($item['meet']))
            {
                $result['army_bodyfat']['description'] = $item['meet'];
            }else {
                if($key == 0)
                {
                    $result['army_bodyfat']['recruitment'] = $item;
                }else if($key == 1)
                {
                    $result['army_bodyfat']['standard'] = $item;
                }else {
                    if(!isset($bodyFatStandard['meet'])){
                        $result['army_bodyfat']['department'] = $item;
                    }
                }
                $result['army_bodyfat']['description'] = $result['army_bodyfat']['description'] ? $result['army_bodyfat']['description'] : "You are not in compliance";
            }
        }
        return $result;
    }

    private function armyBodyFatStandard($ageScore, $gender, $score)
    {
        $result = [];
        $percentDefault = $this->percentDefault(2, $ageScore, $gender);
        
        if($score > $percentDefault)
        {
            $result['title'] = "Army Body Fat Standard" . " (" . $percentDefault . "%) ";
            $result['percent'] =  $score - $percentDefault;
            $result['pounds'] = round($result['percent'] / 100 * 160); 
        }else if($score <= $percentDefault)
        {
            $result['meet'] = "You meet the Army Body Fat Standard.";
        }
        

        return $result;
    }

    private function departmentOfDefenseGoal($score)
    {
        $result = [];

        if($score > 18)
        {
            $result['title'] = "Department of Defense Goal (18%)";
            $result['percent'] = $score - 18;
            $result['pounds'] = round($result['percent'] / 100 * 160); 
        }else if($score <= 18)
        {
            $result['meet'] = "You meet the Department of Defense goal.";
        }
        return $result;
    }

    private function armyRecruitmentStandard($ageScore, $gender, $score)
    {
        $result = [];
        $percentDefault = $this->percentDefault(1, $ageScore, $gender);
        if($score > $percentDefault)
        {
            $result['title'] = "Army Recruitment Standard" . " (" . $percentDefault . "%) ";
            $result['percent'] =  $score - $percentDefault;
            $result['pounds'] = round($result['percent'] / 100 * 160); 
        }
        else if($score <= $percentDefault)
        {
            $result['meet'] = "You meet the Army Recruitment Standard.";
        }
        return $result;
    }


    private function percentDefault($type, $ageScore, $gender)
    {
        if($type == 1)
        {
            $index = ($gender == 1) ? 24 : 30;
        }else {
            $index = ($gender == 1) ? 20 : 30;
        }
        $indexAge = $ageScore * 2;

        return $index + $indexAge;
    }   

    private function ageScore($age)
    {
        if($age >= 17 && $age <= 20)
        {
            return 0;
        }else if($age >= 21 && $age <= 27)
        {
            return 1;
        }else if($age >= 28 && $age <= 39)
        {
            return 2;
        }else {
            return 3;
        }
    }

}