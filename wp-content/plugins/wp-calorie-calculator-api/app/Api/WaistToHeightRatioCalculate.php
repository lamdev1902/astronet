<?php 
namespace Calculator\Api;

use Calculator\Api\AbstractApi;
use Calculator\Helper\Data;

class WaistToHeightRatioCalculate extends AbstractApi
{

    public function __construct(
    ){
        add_action('rest_api_init', array($this, 'waist_to_height_ratio_calculate_api_register_routes'));

        $helper = new Data();
        $this->helper = $helper;
    }


    public function waist_to_height_ratio_calculate_api_register_routes() {
        register_rest_route('api/v1', '/waist/', array(
            'methods' => 'POST',
            'callback' => array($this, 'waist_to_height_ratio_calculate_api_endpoint'),
        ));
    }

    public function waist_to_height_ratio_calculate_api_endpoint($request)
    {

        $requestValidate  = $this->validate($request);
        
        if($requestValidate !== true)
        {
            return $requestValidate;
        }

        $height = $this->helper->cmConvert($request['info']['height']);

        $result['waist'] = round($request['info']['waist']/$height,1);
        
        return $this->_response($result, 200);
    }
}