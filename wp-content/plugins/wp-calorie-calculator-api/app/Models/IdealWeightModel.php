<?php
namespace Calculator\Models;

use Calculator\Models\AbstractModel;
use Calculator\Models\BmiModel;
use Calculator\Helper\Data;

class IdealWeightModel extends AbstractModel
{
    public function __construct()
    {
        $helper = new Data();
        $this->helper = $helper;

        $bmi = new BmiModel();
        $this->bmi = $bmi;
    }

    public function calculate($info)
    {
        $result = [];

        if((int)$info['height']['feet'] == 5 && !empty($info['height']['inches']))
        {
            $result['ideal_weight']['robin'] = $this->robinsonIdealWeight($info);
            $result['ideal_weight']['miller'] = $this->millerIdealWeight($info);
            $result['ideal_weight']['devine'] = $this->devineIdealWeight($info);
            $result['ideal_weight']['hamwi'] = $this->hamwiIdealWeight($info);
            $result['ideal_weight']['bmi'] = $this->bmiIdealWeight($info);
            
        }else if((int)$info['height']['feet'] == 5 && empty($info['height']['inches'])) {

            $result['ideal_weight']['bmi'] = $this->bmiIdealWeight($info);

        }else if((int)$info['height']['feet'] > 5)
        {
            $feet = (int)$info['height']['feet'] - 5;

            $inches = 0;

            if(isset($info['height']['inches'])){
                $inches = (int)$info['height']['inches'];
            }

            $info['height']['feet'] = 5;
            $info['height']['inches'] = ($feet * 12) + $inches;

            $result['ideal_weight']['robin'] = $this->robinsonIdealWeight($info);
            $result['ideal_weight']['miller'] = $this->millerIdealWeight($info);
            $result['ideal_weight']['devine'] = $this->devineIdealWeight($info);
            $result['ideal_weight']['hamwi'] = $this->hamwiIdealWeight($info);
            $result['ideal_weight']['bmi'] = $this->bmiIdealWeight($info);

        }else if((int)$info['height']['feet'] < 5)
        {   
            $result['ideal_weight']['bmi'] = $this->bmiIdealWeight($info);
        }
        
        return $result;
    }

    private function bmiIdealWeight($info)
    {
        $result = [];

        $height = $this->helper->cmConvert($info['height']);
        $idealWeight = $this->bmi->idealWeight($height/100);

        $result['title'] = "Healthy BMI Range";
        $result['pounds'] = $idealWeight[0] . ' - ' . $idealWeight[1];

        return $result;
    }

    private function robinsonIdealWeight($info)
    {
        $result = [];

        $gender = (int)$info['gender'] == 1 ? 52 : 49;
        $i = (int)$info['gender'] == 1 ? 1.9 : 1.7;

        $result['title'] = "Robinson (1983)";
        $result['pounds'] = round((($gender + ( $i * (int)$info['height']['inches']))/0.45359237),1);

        return $result;
    }

    private function millerIdealWeight($info)
    {
        $result = []; 

        $gender = (int)$info['gender'] == 1 ? 56.2 : 53.1;
        $i = (int)$info['gender'] == 1 ? 1.41 : 1.36;

        $result['title'] = "Miller (1983)";
        $result['pounds'] = round((($gender + ( $i * (int)$info['height']['inches']))/0.45359237),1);

        return $result;
    }

    private function devineIdealWeight($info)
    {
        $result = []; 
        
        $gender = (int)$info['gender'] == 1 ? 50 : 45.5;
        $i = 2.3;

        $result['title'] = "Devine (1974)";
        $result['pounds'] = round((($gender + ( $i * (int)$info['height']['inches']))/0.45359237),1);
        
        return $result;
    }

    private function hamwiIdealWeight($info)
    {
        $result = []; 

        $gender = (int)$info['gender'] == 1 ? 48 : 45.5;
        $i = (int)$info['gender'] == 1 ? 2.7 : 2.2;

        $result['title'] = "Hamwi (1964)";
        $result['pounds'] = round((($gender + ( $i * (int)$info['height']['inches']))/0.45359237),1);

        return $result;
    }
}