<?php 
namespace Calculator\Api;

use Calculator\Api\AbstractApi;
use Calculator\Models\CalorieModel;

class CalorieCalculate extends AbstractApi
{
    public function __construct(){
        add_action('rest_api_init', array($this, 'calorie_calculate_api_register_routes'));

        $calorie = new CalorieModel();
        $this->calorie = $calorie;
    }

    public function calorie_calculate_api_register_routes() {
        register_rest_route('api/v1', '/calorie-calculate/', array(
            'methods' => 'POST',
            'callback' => array($this, 'calorie_calculate_api_endpoint'),
        ));
    }

    public function calorie_calculate_api_endpoint($request)
    {
        $requestValidate = $this->validate($request);

        if($requestValidate !== true)
        {
            return $requestValidate;
        }

        $result = $this->calorie->calculate($request);

        if(!$result)
        {
            return $this->_response([], 400);
        }
        return $this->_response($result, 200, $request['unit']);
    }
}
