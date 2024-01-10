<?php 
class BMR 
{
    public function BMRCalculate($info,$receip)
    {
        $result = [];

        $helper = new Data();

        $height = $helper->heightConvert($info);
        $weight = $helper->weightConvert($info);

        $info['height'] = $height;
        $info['weight'] = $weight;

        if($receip == 1){
            $bmr =  $this->BMRMifflinSt($info);
        }else if ($receip == 2) {
            $bmr =  $this->BMRHarrisBenedict($info);
        }else if($receip == 3) {
            $bmr =  $this->BMRKatchMcArdle($info);
        }

        $result['bmr']['calorie'] = $bmr;
        return $result;
    }

    private function BMRMifflinSt($data)
    {
        $genderNumber = $data['gender'] == 1 ? 5 : -161;

       return round( (10 * $data['weight']) + ( 6.25 * $data['height'] ) - ( 5 * $data['age'] ) + $genderNumber );
    }

    private function BMRHarrisBenedict($data)
    {
        if($data['gender'] === "1")
        {
            return round( (13.397 * $data['weight']) + ( 4.799 * $data['height'] ) - ( 5.677 * $data['age'] ) + 88.362 );
        }

        return round( (9.247 * $data['weight']) + ( 3.098 * $data['height'] ) - ( 4.330 * $data['age'] ) + 447.593 );
    }

    private function BMRKatchMcArdle($data)
    {
        return round( 370 + ( 21.6 * ( 1 - ( $data['body-fat'] / 100 ) ) * $data['weight'] ) );
    }
}