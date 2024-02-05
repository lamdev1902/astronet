<?php
namespace Calculator\Models;

use Calculator\Models\AbstractModel;
use Calculator\Helper\Data;

class BodyAdiposityIndexModel extends AbstractModel
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

        $height = $this->helper->metersConvert($data['height']);

        $hip = $this->helper->cmConvert($data['hip']);

        $percent = ($hip / pow($height,1.5)) - 18;

        $result['bai']['percent'] = round($percent,1);
        return $result;
    }
}