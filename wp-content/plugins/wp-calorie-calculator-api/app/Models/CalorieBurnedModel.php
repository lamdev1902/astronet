<?php
namespace Calculator\Models;

use Calculator\Models\AbstractModel;
use Calculator\Helper\Data;

class CalorieBurnedModel extends AbstractModel
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

    public function calculate($info)
    {
        
    }
}