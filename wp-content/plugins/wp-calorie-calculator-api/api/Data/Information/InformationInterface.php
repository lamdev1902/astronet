<?php
namespace Calculator\Api\Data\Information;

use Calculator\Api\Data\Information\Height\HeightInterface;

interface InformationInterface
{
    /**
     * Get age
     * @param string|int
     * @return $this
     */
    public function getAge();

    /**
     * Get Weight
     * @param string|int
     * @return $this
     */
    public function getWeight();

    /**
     * Get height
     * 
     * @return HeightInterface[]
     */
    public function getHeight();

    /**
     * Get gender
     * @param string|int
     * @return $this
     */
    public function getGender();
    
}