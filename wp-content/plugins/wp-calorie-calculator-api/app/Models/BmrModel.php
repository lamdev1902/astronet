<?php
namespace Calculator\Models;

use Calculator\Models\AbstractModel;
use Calculator\Helper\Data;

class BmrModel extends AbstractModel
{

    public function __construct()
    {
        $helper = new Data();
        $this->helper = $helper;
    }
    public function calculate($request)
    {
        $result = [];

        $height = $this->helper->cmConvert($request['info']['height']);
        $weight = $this->helper->kgConvert($request['info']['weight']);

        $info = $request['info'];

        $info['height'] = $height;
        $info['weight'] = $weight;


        if($request['receip'] == 1){
            $bmr =  $this->BMRMifflinSt($info);
        }else if ($request['receip'] == 2) {
            $bmr =  $this->BMRHarrisBenedict($info);
        }else if($request['receip'] == 3) {
            if(!isset($info['body-fat'])){
                return false;
            }
            $bmr =  $this->BMRKatchMcArdle($info);
        }

        $result['bmr']['calorie'] = $bmr;

        if($request['unit'] == 2)
        {
            $result = $this->helper->kilojoulesConvert($result);
        }
        
        return $result;
    }

    private function BMRMifflinSt($data)
    {
        $genderNumber = $data['gender'] == 1 ? 5 : -161;

       return floor( (10 * $data['weight']) + ( 6.25 * $data['height'] ) - ( 5 * $data['age'] ) + $genderNumber );
    }

    private function BMRHarrisBenedict($data)
    {
        if($data['gender'] === "1")
        {
            return floor( (13.397 * $data['weight']) + ( 4.799 * $data['height'] ) - ( 5.677 * $data['age'] ) + 88.362 );
        }

        return floor( (9.247 * $data['weight']) + ( 3.098 * $data['height'] ) - ( 4.330 * $data['age'] ) + 447.593 );
    }

    private function BMRKatchMcArdle($data)
    {
        return floor( 370 + ( 21.6 * ( 1 - ( $data['body-fat'] / 100 ) ) * $data['weight'] ) );
    }

}