<?php
namespace Calculator\Models;

use Calculator\Models\AbstractModel;
use Calculator\Helper\Data;

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
    }

    public function calculate($data)
    {
        $data = $this->convert($data);

        $result = $this->bodyfatCalculate($data);

        return $data;
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

    private function bodyfatCalculate($data)
    {
        $height = $data['height'];
        $weight = $data['weight'];
        $neck = $data['neck'];
        $waist = $data['waist'];

        if($gender == 1)
        {
            $bfp = ( 495/( 1.0324 - ( 0.19077 * log10($waist - $neck) + ( 0.15456 * log10($heigth) ) ) ) ) - 450;
        }else {
            $hip = $this->helper->cmConvert($data['hip']);
            $bfp = ( 495/( 1.29579 - ( 0.35004 * log10($waist + $hip - $neck) + ( 0.22100 * log10($heigth) ) ) ) ) - 450;
        }

        $result = [];

        $result['bmr'][] = $bfp;

    }

}