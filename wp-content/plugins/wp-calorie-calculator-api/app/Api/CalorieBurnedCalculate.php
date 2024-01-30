<?php 
namespace Calculator\Api;

use Calculator\Models\CalorieBurnedModel;
use Calculator\Api\AbstractApi;

class CalorieBurnedCalculate extends AbstractApi{

    /**
     * Calorie Burned Model
     * @var CalorieBurnedModel
     */
    protected $calorieBurnedModel;

    public function __construct(
        
    ){
        add_action('rest_api_init', array($this, 'calorie_burned_calculate_api_register_routes'));
        $calorieBurnedModel = new CalorieBurnedModel();
        $this->calorieBurnedModel = $calorieBurnedModel;

    }


    public function calorie_burned_calculate_api_register_routes() {
        register_rest_route('api/v1', '/calorie-burned/', array(
            'methods' => 'POST',
            'callback' => array($this, 'calorie_burned_calculate_api_endpoint'),
        ));
    }

    public function calorie_burned_calculate_api_endpoint($request)
    {
        $requestValidate = $this->validate($request);

        if($requestValidate !== true)
        {
            return $requestValidate;
        }

        $result = $this->calorieBurnedModel->calculate($request['info']);


        return $this->_response($result, 200);
    }

}
