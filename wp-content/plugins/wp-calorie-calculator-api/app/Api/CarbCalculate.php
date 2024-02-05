<?php 
namespace Calculator\Api;

use Calculator\Api\AbstractApi;
use Calculator\Models\BmiModel;

class BmiCalculate extends AbstractApi
{

    public function __construct(){
        add_action('rest_api_init', array($this, 'carb_calculate_api_register_routes'));
        

    }

    public function carb_calculate_api_register_routes() {
        register_rest_route('api/v1', '/carb-calculate/', array(
            'methods' => 'POST',
            'callback' => array($this, 'carb_calculate_api_endpoint'),
        ));
    }

    public function carb_calculate_api_endpoint($request)
    {
        $validate  = $this->validate($request);
        
        if($validate !== true)
        {
            return $validate;
        }
        $bmiResult = $this->bmi->calculate($request['info']);

        return $this->_response($bmiResult, 200, $request['unit']);
    }
}
