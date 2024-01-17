<?php 
namespace Calculator\Api;

use Calculator\Api\AbstractApi;
use Calculator\Models\BmiModel;

class BmiCalculate extends AbstractApi
{

    /**
     * BMI Model
     * @var BmiModel $bmi
     */
    protected $bmi;

    public function __construct(){
        add_action('rest_api_init', array($this, 'bmi_calculate_api_register_routes'));
        
        $bmi = new BmiModel();
        $this->bmi = $bmi;
    }

    public function bmi_calculate_api_register_routes() {
        register_rest_route('api/v1', '/bmi-calculate/', array(
            'methods' => 'POST',
            'callback' => array($this, 'bmi_calculate_api_endpoint'),
        ));
    }

    public function bmi_calculate_api_endpoint($request)
    {
        
        $bmiResult = $this->bmi->calculate($request['info']);

        return $this->_response($bmiResult, 200);
    }
}
