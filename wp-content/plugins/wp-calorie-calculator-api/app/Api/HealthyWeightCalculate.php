<?php 
namespace Calculator\Api;

use Calculator\Models\BmiModel;
use Calculator\Api\Data\AgeInterface;
use Calculator\Api\AbstractApi;
use Calculator\Helper\Data;

class HealthyWeightCalculate extends AbstractApi {

    /**
     * Ideal Weight Model
     * @var BmiModel
     */
    protected $bmi;


    public function __construct(
        
    ){
        add_action('rest_api_init', array($this, 'healthy_weight_calculate_api_register_routes'));
        $bmi = new BmiModel();
        $this->bmi = $bmi;

        $helper = new Data();
        $this->helper = $helper;

    }


    public function healthy_weight_calculate_api_register_routes() {
        register_rest_route('api/v1', '/healthy-weight/', array(
            'methods' => 'POST',
            'callback' => array($this, 'healthy_weight_calculate_api_endpoint'),
        ));
    }

    public function healthy_weight_calculate_api_endpoint($request)
    {
        $requestValidate = $this->validate($request);

        if($requestValidate !== true)
        {
            return $requestValidate;
        }

        $height = $this->helper->cmConvert($request['info']['height']);

        $idealWeight = $this->bmi->idealWeight($height/100);
        
        $result['healthy_weight'] = "The healthy weight range for the height is ". round($idealWeight[0]) . ' lbs' . ' - ' . round($idealWeight[1]) . ' lbs'; 

        return $this->_response($result, 200);
    }
}
