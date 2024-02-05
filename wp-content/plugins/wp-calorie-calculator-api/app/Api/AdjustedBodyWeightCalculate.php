<?php 
namespace Calculator\Api;

use Calculator\Models\AdjustedBodyWeightModel;
use Calculator\Api\Data\AgeInterface;
use Calculator\Api\AbstractApi;

class AdjustedBodyWeightCalculate extends AbstractApi {

    /**
     * Ideal Weight Model
     * @var AdjustedBodyWeightModel
     */
    protected $adjustedBodyWeight;

    public function __construct(
        
    ){
        add_action('rest_api_init', array($this, 'adjusted_body_weight_calculate_api_register_routes'));
        $adjustedBodyWeight = new AdjustedBodyWeightModel();
        $this->adjustedBodyWeight = $adjustedBodyWeight;

    }


    public function adjusted_body_weight_calculate_api_register_routes() {
        register_rest_route('api/v1', '/adjusted-body-weight-calculate/', array(
            'methods' => 'POST',
            'callback' => array($this, 'adjusted_body_weight_calculate_api_endpoint'),
        ));
    }

    public function adjusted_body_weight_calculate_api_endpoint($request)
    {
        $requestValidate = $this->validate($request);

        if($requestValidate !== true)
        {
            return $requestValidate;
        }
        
        $result = $this->adjustedBodyWeight->calculate($request['info']);


        return $this->_response($result, 200);
    }
}
