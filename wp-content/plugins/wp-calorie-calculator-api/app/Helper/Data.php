<?php 
namespace Calculator\Helper;

class Data
{
    /**
     * Convert Feet && Inches to CM
     * @return int
     */
    public function cmConvert($data)
    {
        $feet = (int)$data['feet'];
        $inches = (int)$data['inches'];

        return round(( $feet * 30.48 ) + ($inches * 2.54 ),2);
    }

    /**
     * Convert Pounds to Kg
     * @return int
     */
    public function kgConvert($weight)
    {
        return round($weight * 0.45359237,1);
    }


    /**
     * Convert Calorie to Kilojoules
     * 
     */
    public function kilojoulesConvert($data)
    {
        foreach($data as $key => $item)
        {
            $kilojoules = floor($item['calorie']*4.1868);
            $data[$key]['calorie'] = $kilojoules;
        }

        return $data;
    }

}