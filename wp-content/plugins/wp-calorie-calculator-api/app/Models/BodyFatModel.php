<?php
namespace Calculator\Models;

use Calculator\Models\AbstractModel;
use Calculator\Helper\Data;
use Calculator\Models\BmiModel;

class BodyFatModel extends AbstractModel
{
    /**
     * @var Data $helper
     */
    protected $helper;

    public function __construct()
    {
        $helper = new Data();
        $this->helper = $helper;

        $bmi = new BmiModel();
        $this->bmi = $bmi;
    }

    public function calculate($data)
    {
        $formatData = $this->convert($data['info']);
        $weight = $data['info']['weight'];

        $result = $this->bodyfatCalculate($formatData);

        $bmiResult = $this->bmi->calculate($data['info']);

        $type = '';

        if($data['info']['gender'] == 1)
        {
            $type =$this->bodyFatCategoryMale($result['bfp']['navy_method']['percent']);
        }else 
        {
            $type =$this->bodyFatCategoryFeMale($result['bfp']['navy_method']['percent']);

        }
        $result['bfp']['category'] = [
            'title' => 'Body Fat Category',
            'type' => $type
        ];

        $result['bfp']['mass'] = [
            "title" => "Body Fat Mass",
            "pounds" => round( ($weight * ( $result['bfp']['navy_method']['percent']/100 ) ), 1)
        ];

        $result['bfp']['lean'] = [
            "pounds" => round( $weight - $result['bfp']['mass']['pounds'], 1),
            "title" => "Lean Body Mass"
        ];

        $jacksonPollock = $this->jacksonPollockIdeal($data['info']);

        $result['bfp']['jackson']  = [
            'title' => 'Ideal Body Fat for Given Age (Jackson & Pollock)',
            'percent' => $jacksonPollock
        ];

        $result['bfp']['bmi_method'] = [
            'title' => 'Body Fat (BMI Method)',
            'percent' => $this->bodyFatBMICalculate($bmiResult['bmi']['bmi'], $data['info']['gender'], $data['info']['age'])
        ];



        return $result;
    }

    private function convert($data)
    {

        try {
            $data['weight'] = $this->helper->kgConvert($data['weight']);
            $data['height'] = $this->helper->cmConvert($data['height']);
            $data['neck'] = $this->helper->cmConvert($data['neck']);
            $data['waist'] = $this->helper->cmConvert($data['waist']);

        } catch (\Throwable $th) {
            //throw $th;
        }

        return $data;
    }

    public  function bodyfatCalculate($data)
    {
        $height = $data['height'];
        $weight = $data['weight'];
        $neck = $data['neck'];
        $waist = $data['waist'];

        $gender = $data['gender'];

        if($gender == 1)
        {
            $bfp = ( 495/( 1.0324 - ( 0.19077 * log10($waist - $neck) ) + ( 0.15456 * log10($height) ) ) )  - 450;
        }else {
            $hip = $this->helper->cmConvert($data['hip']);
            $bfp = ( 495/( 1.29579 - ( 0.35004 * log10($waist + $hip - $neck) ) + ( 0.22100 * log10($height) )  ) ) - 450;
        }

        $result = [];

        $result['bfp']['navy_method'] = [
            'title' => 'Body Fat (U.S. Navy Method)',
            'percent' => round($bfp,1)
        ];

        return $result;

    }

    private function bodyFatCategoryFemale($bfp)
    {
        if($bfp < 10)
        {
            $text = 'Less than Essential Fat';
        }
        else if($bfp >= 10 && $bfp < 14 )
        {
            $text = "Essential Fat";

        }else if($bfp >= 14 && $bfp < 21)
        {
            $text = "Athletes";

        }else if($bfp >= 21 && $bfp < 25)
        {
            $text = "Fitness";

        }else if($bfp >= 25 && $bfp < 32)
        {
            $text = "Average";

        }else if($bfp >= 32)
        {
            $text = "Obese";

        }

        return $text;
    }

    private function bodyFatCategoryMale($bfp)
    {
        $text = '';
        if($bfp < 2)
        {
            $text = 'Less than Essential Fat';
        }
        if($bfp >= 2 && $bfp < 6)
        {
            $text = 'Essential';
        }else if($bfp >= 6 && $bfp < 14)
        {
            $text = 'Athletes';
        }else if($bfp >= 14 && $bfp < 18){
            $text = 'Fitness';
        }else if($bfp >= 18 && $bfp < 25)
        {
            $text = 'Average';
        }else if($bfp >= 25)
        {
            $text = 'Obese';
        }

        return $text;
    }


    private function jacksonPollockIdeal($data)
    {
        $age = $data['age'];
        $gender = $data['gender'];

        $percent = '';
        if($age >= 20 && $age < 25)
        {
            $percent = ( $gender == 1 ) ? 8.5 : 17.7;
        }else if($age >= 25 && $age < 30)
        {
            $percent = ( $gender == 1 ) ? 10.5 : 18.4;
        }else if($age >= 30 && $age < 35)
        {
            $percent = ( $gender == 1 ) ? 13.7 : 21.5;
        }else if($age >= 35 && $age < 40)
        {
            $percent = ( $gender == 1 ) ? 15.3 : 22.2;
        }else if($age >= 40 && $age < 45 )
        {
            $percent = ( $gender == 1 ) ? 16.4 : 22.9;
        }
        else if($age >= 45 && $age < 50)
        {
            $percent = ( $gender == 1 ) ? 18.9 : 25.2;
        }else if($age >= 50 && $age < 55)
        {
            $percent = ( $gender == 1 ) ? 20.9 : 26.3;
        }

        return $percent;
    }

    public function bodyFatBMICalculate($bmi, $gender, $age)
    {
        $gender = $gender == 1 ? 16.2 : 5.4;

        return  floor(1.20 * $bmi + 0.23 * $age - $gender);
    }

}