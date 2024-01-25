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
        $height = $this->helper->cmConvert($info['height']);
        $neck = $this->helper->cmConvert($info['neck']);
        $waist = $this->helper->cmConvert($info['waist']);

        $a = $waist - 41.91;
        if($info['gender'] == 1)
        {
            $result['army'] = round((86.010 * log10($a)) - (70.041 * log10($height)) + 36.76);
        }else {
            $hip = $this->helper->cmConvert($info['hip']);
            
            $result['army'] = round((163.205 * log10($waist + $hip - $neck )) - (97.684 * log10($height)) - 78.387);
        }

        return $result;
    }
}