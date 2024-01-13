<?php 
namespace Calculator\Models;

abstract class AbstractModel
{
    
    public function __construct()
    {

    }

    abstract public function calculate($data);
}