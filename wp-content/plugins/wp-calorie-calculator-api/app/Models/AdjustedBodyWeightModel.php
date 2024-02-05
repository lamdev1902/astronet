<?php
namespace Calculator\Models;

use Calculator\Models\AbstractModel;
use Calculator\Models\BmiModel;
use Calculator\Helper\Data;

class AdjustedBodyWeightModel extends AbstractModel
{
    public function __construct()
    {
        $helper = new Data();
        $this->helper = $helper;

        $idealWeightModel = new IdealWeightModel();
        $this->idealWeightModel = $idealWeightModel;
    }

    public function calculate($info)
    {
        $idealWeight = $this->idealWeightModel->calculate($info);
        
        $idealWeightResult = $idealWeight['ideal_weight']['robin']['pounds'];

        $weight = $info['weight'];

        $adjusted = round($idealWeightResult + 0.4 * ($weight - $idealWeightResult),2);

        $result['adjusted']['ideal_weight'] = $idealWeightResult;

        $result['adjusted']['body_weight'] = $adjusted;
        return $result;
    }
}