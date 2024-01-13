<?php 
namespace Calculator\Api\Data\Information\Height;

interface HeightInterface
{
    /**
     * Feet
     * @param string|int
     * @return $this
     */
    public function getFeet();
    
    /**
     * Inches
     * @param string|int
     * @return $this
     */
    public function getInches();
}