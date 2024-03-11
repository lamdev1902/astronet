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

        $bmi['bmi']['ideal_weight'] = $idealWeight[0] . ' lbs' . ' - ' . $idealWeight[1] . ' lbs';
        if($bmi['bmi']['type'] < 4)
        {
            $gain = round($idealWeight[0] - (int)$info['weight'],2);
            $bmi['bmi']['propose']['title'] = 'Gain';
            $bmi['bmi']['propose']['result'] =  $gain;
        }elseif ($bmi['bmi']['type'] > 4){
            $lose = round((int)$info['weight'] - $idealWeight[1],2);

            $bmi['bmi']['propose']['title'] = 'Lose';
            $bmi['bmi']['propose']['result'] = $lose;
        }
        $bmi['bmi']['ponderal'] = $ponderalIndex;
        $bmi['bmi']['healthy_range'] = '18.5 kg/m2 - 25 kg/m2';

        return $bmi;
    }

    public function idealWeight($height)
    {
        $result = '';
        
        $range18 = 18.5 * ($height * $height);
        $range25 = 25 * ($height * $height);

        $range18Cv = round($range18 / 0.45359237, 1);
        $range25Cv = round($range25 / 0.45359237, 1);


        return $result = [
            $range18Cv, $range25Cv
        ];
    }

    private function bmiResult($data)
    {
        $bmi = $data['weight'] / ($data['height'] * $data['height']);

        return $bmi;
    }

    private function bmiForAdults($bmi)
    {
        $result = [];

        if($bmi < 16)
        {
            $result['bmi']['type'] = 1;
            $result['bmi']['description'] = "Severe Thinness";
        }elseif($bmi < 17){
            $result['bmi']['type'] = 2;
            $result['bmi']['description'] = "Moderate Thinness";
        }elseif($bmi < 18.5)
        {
            $result['bmi']['type'] = 3;
            $result['bmi']['description'] = "Mild Thinness";
        }else if($bmi >= 18.5 && $bmi < 25)
        {
            $result['bmi']['type'] = 4;
            $result['bmi']['description'] = "Normal";
        }
        else if($bmi >= 25 && $bmi < 30){
            $result['bmi']['type'] = 5;
            $result['bmi']['description'] = "Overweight";
        }else if($bmi >= 30 && $bmi < 35){
            $result['bmi']['type'] = 6;
            $result['bmi']['description'] = "Obesity Class 1";
        }else if($bmi >= 35 && $bmi < 40){
            $result['bmi']['type'] = 6;
            $result['bmi']['description'] = "Obesity Class 2";
        }else if($bmi >= 40){
            $result['bmi']['type'] = 6;
            $result['bmi']['description'] = "Obesity Class 3";
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

        $pi = round($data['weight'] / pow($data['height'],3),1);

        return $pi;
    }
}