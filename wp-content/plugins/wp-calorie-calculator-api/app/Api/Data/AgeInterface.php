<?php 
namespace Calculator\Api\Data;

interface AgeInterface
{


    /**
     * Get birth date
     * @return string
     */
    public function getFromDate();

    /**
     * set birth date
     * @param string $from
     * @return $this
     */
    public function setFromDate($from);

    /**
     * Get to date
     * @return string
     */
    public function getToDate();

    /**
     * set to date
     * @param string $to
     * @return $this
     */
    public function setToDate($to);
}