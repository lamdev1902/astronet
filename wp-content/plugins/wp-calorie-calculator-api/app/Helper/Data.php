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
        $feet = $data['feet'];
        $inches = $data['inches'];

        return round(( $feet * 30.48 ) + ($inches * 2.54 ),1);
    }

    /**
     * Convert Pounds to Kg
     * @return int
     */
    public function kgConvert($data)
    {
        $weight = $data['weight'];
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