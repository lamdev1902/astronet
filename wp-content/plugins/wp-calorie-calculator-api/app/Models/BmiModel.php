<?php
namespace Calculator\Models;

use Calculator\Models\AbstractModel;
use Calculator\Helper\Data;

class BmiModel extends AbstractModel
{
    public function __construct()
    {
        $helper = new Data();
        $this->helper = $helper;
    }
    /**
     * BMI Calculate
     * @return int
     */
    public function calculate($info)
    {
        $data = [];
        $height = $this->helper->cmConvert($info['height']);
        $weight = $this->helper->kgConvert($info['weight']);

        $height = $height / 100;

        $data['weight'] = $weight;
        $data['height'] = $height;

        $result = $this->bmiResult($data);
       
        // if((int)$data['age'] > 20)
        // {
        $bmi = $this->bmiForAdults(round($result,2));

        // }else {
        //     $bmi = $this->BMIForChild($data);
        // }

        $ponderalIndex = $this->ponderalIndexCalculate($data);


        $idealWeight = $this->idealWeight($height);

        $bmi['bmi']['ideal_weight'] = $idealWeight;
        $bmi['bmi']['ponderal'] = $ponderalIndex;
        $bmi['bmi']['healthy_range'] = 'Healthy BMI range: 18.5 kg/m2 - 25 kg/m2';

        return $bmi;
    }

    private function idealWeight($height)
    {
        $result = '';
        
        $range18 = 18.5 * ($height * $height);
        $range25 = 25 * ($height * $height);

        $range18Cv = round($range18 / 0.45359237, 1);
        $range25Cv = round($range25 / 0.45359237, 1);

        $result = "Healthy weight for the height: " . $range18Cv . 'lbs' . ' - ' . $range25Cv . 'lbs';

        return $result;
    }

    private function bmiResult($data)
    {
        $bmi = $data['weight'] / ($data['height'] * $data['height']);

        return $bmi;
    }

    private function bmiForAdults($bmi)
    {
        $result = [];


        if($bmi < 18.5)
        {
            $result['bmi']['type'] = 1;
            $result['bmi']['description'] = "Underweight";
        }else if($bmi >= 18.5 && $bmi < 25)
        {
            $result['bmi']['type'] = 2;
            $result['bmi']['description'] = "Normal";
        }
        else if($bmi >= 25 && $bmi < 30){
            $result['bmi']['type'] = 3;
            $result['bmi']['description'] = "Overweight";
        }else if($bmi >= 30){
            $result['bmi']['type'] = 4;
            $result['bmi']['description'] = "Obesity";
        }

        $prime = $this->primebmiCalculate($bmi);

        $result['bmi']['bmi'] = round($bmi,1); 
        $result['bmi']['prime'] = $prime;

        return $result;

    }

    private function BMIForChild($data)
    {

    }

    private function primebmiCalculate($bmi)
    {
        return round( ($bmi/25), 2);
    }

    private function ponderalIndexCalculate($data)
    {
        $pi = [];

        $pi['type'] = $data['type'];
        $pi['pi'] = round($data['weight'] / pow($data['height'],3));

        return $pi;
    }
}