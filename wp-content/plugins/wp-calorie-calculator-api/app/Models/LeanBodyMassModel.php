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

        if($info['age'] == 1)
        {
            $result['lean_body_mass'][] = $this->childrendFomular($info);
        }
        $result['lean_body_mass'][] = $this->boerFomular($info);
        $result['lean_body_mass'][] = $this->jamesFomular($info);
        $result['lean_body_mass'][] = $this->humeFomular($info);

        foreach($result['lean_body_mass'] as $key => $item)
        {
            $result['lean_body_mass'][$key]['percent'] = round(($item['score']/$pounds)* 100); 
            $result['lean_body_mass'][$key]['body_fat'] = 100 - $result[$key]['percent']; 
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
        $result['score'] = $leanbody;
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
        $result['score'] = $leanbody;
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
        $result['score'] = $leanbody;
        return $result;

    }

    private function childrendFomular($info)
    {
        $result = [];

        $eECV = 0.0215 * pow($info['weight'], 0.6469) * pow($info['height'],0.7236);

        $leanbody = $eECV * 3.8;
        $leanbody = $this->helper->poundsConvert($leanbody);

        $result['title'] = 'Peters (for Children)';
        $result['score'] = $leanbody;
        return $result;

    }
}