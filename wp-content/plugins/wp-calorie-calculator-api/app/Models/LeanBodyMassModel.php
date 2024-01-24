<?php 
namespace Calculator\Models;

use Calculator\Models\AbstractModel;
use Calculator\Models\BmiModel;
use Calculator\Helper\Data;

class LeanBodyMassModel extends AbstractModel
{

    /**
     * @var BodyFatModel
     */
    protected $bodyFat;

    public function __construct()
    {
        $helper = new Data();
        $this->helper = $helper;
    }

    public function calculate($info)
    {
        $result = [];

        $pounds = $info['weight'];
        $info['height'] = $this->helper->cmConvert($info['height']);
        $info['weight'] = $this->helper->kgConvert($info['weight']);

        if($info['age'] < 14)
        {
            $result[] = $this->childrendFomular($info);
        }
        $result[] = $this->boerFomular($info);
        $result[] = $this->jamesFomular($info);
        $result[] = $this->humeFomular($info);

        foreach($result as $key => $item)
        {
            $result[$key]['percent'] = round(($item['lean_body']/$pounds)* 100); 
            $result[$key]['body_fat'] = 100 - $result[$key]['percent']; 
        }

        return $result;
    }

    private function boerFomular($info)
    {
        if($info['gender'] == 1)
        {
            $leanbody = 0.407*$info['weight'] + 0.267*$info['height'] - 19.2;
        }else {
            $leanbody = 0.252*$info['weight'] + 0.473*$info['height'] - 48.3;
        }

        $leanbody = $this->helper->poundsConvert($leanbody);

        $result['title'] = 'Boer';
        $result['lean_body'] = $leanbody;
        return $result;

    }

    private function jamesFomular($info)
    {
        $result = [];
        if($info['gender'] == 1)
        {
            $leanbody =  (1.1 * $info['weight']) - 128 * (pow(($info['weight']/$info['height']),2));
        }else {
            $leanbody =  (1.07 * $info['weight']) - 148 * (pow(($info['weight']/$info['height']),2));
        }

        $leanbody = $this->helper->poundsConvert($leanbody);

        $result['title'] = 'James';
        $result['lean_body'] = $leanbody;
        return $result;
    }

    private function humeFomular($info)
    {
        $result = [];
        if($info['gender'] == 1)
        {
            $leanbody = 0.32810*$info['weight'] + 0.33929*$info['height'] - 29.5336;
        }else {
            $leanbody = 0.29569*$info['weight'] + 0.41813*$info['height'] - 43.2933;
        }

        $leanbody = $this->helper->poundsConvert($leanbody);

        $result['title'] = 'Hume';
        $result['lean_body'] = $leanbody;
        return $result;

    }

    private function childrendFomular($info)
    {
        $result = [];
        $index = 0.6469 * pow($info['height'], 0.7236);
        $index = 45.5;

        $leanbody = 0.0215 * pow(73,$index);

        if($gender == 2)
        {
            $leanbody = 3.8 * $leanbody;
        }

        $leanbody = $this->helper->poundsConvert($leanbody);

        $result['title'] = 'Childrend';
        $result['lean_body'] = $leanbody;
        return $result;

    }
}