<?php 
class BMI 
{

    /**
     * BMI Calculate
     * @return int
     */
    public function bmiCalculate($info)
    {
        $helper = new Data();

        $data = [];
        if(isset($info['height']['feet']))
        {
            $inches = isset($info['height']['inches']) ? $info['height']['inches'] : 0;
            $totalInches = $info['height']['feet'] * 12 + $inches;

            $data['weight'] = $info['weight'];
            $data['height'] = $totalInches;
            $data['type'] = 1;

        }else {
            $height = $helper->heightConvert($data);
            $weight = $helper->weightConvert($data);

            $height = round($height / 100, 1);

            $data['weight'] = $weight;
            $data['height'] = $height;
            $data['type'] = 2;
        }
        

        $result = $this->bmiResult($data);
       
        // if((int)$data['age'] > 20)
        // {
        $bmi = $this->bmiForAdults(round($result,2));

        // }else {
        //     $bmi = $this->BMIForChild($data);
        // }

        $ponderalIndex = $this->ponderalIndexCalculate($data);

        $bmi['bmi']['ponderal'] = $ponderalIndex;
        $bmi['bmi']['healthy_range'] = 'Healthy BMI range: 18.5 kg/m2 - 25 kg/m2';

        return $bmi;
    }

    private function bmiResult($data)
    {
        if($data['type'] == 2)
        {
            $bmi = $data['weight'] / ($data['height'] * $data['height']);
        }else {
            $bmi = 703 * ($data['weight'] / ( $data['height'] * $data['height'] ));
        }

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
        if($data['type'] == 2)
        {
            $pi['type'] = $data['type'];
            $pi['pi'] = round($data['weight'] / pow($data['height'],3));
        }else {
            $pi['type'] = $data['type'];
            $pi['pi'] = round($data['height'] / pow($data['weight'],1/3));
        }

        return $pi;
    }
}