<?php 
namespace Calculator\Api;

use Calculator\Models\IdealWeightModel;
use Calculator\Api\Data\AgeInterface;
use Calculator\Api\AbstractApi;

class IdealWeightCalculate extends AbstractApi {

    /**
     * Ideal Weight Model
     * @var IdealWeightModel
     */
    protected $idealWeight;

    public function __construct(
        
    ){
        add_action('rest_api_init', array($this, 'ideal_weight_calculate_api_register_routes'));
        $idealWeight = new IdealWeightModel();
        $this->idealWeight = $idealWeight;
    }


    public function ideal_weight_calculate_api_register_routes() {
        register_rest_route('api/v1', '/ideal-weight/', array(
            'methods' => 'POST',
            'callback' => array($this, 'ideal_weight_calculate_api_endpoint'),
        ));
    }

    public function ideal_weight_calculate_api_endpoint($request)
    {

        
        $result = $this->idealWeight->calculate($request['info']);


        return $this->_response($result, 200);
    }
}
