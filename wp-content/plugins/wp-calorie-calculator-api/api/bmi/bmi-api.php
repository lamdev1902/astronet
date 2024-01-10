<?php 
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'api.php';

class Bmi_Api extends API
{
    /**
     * Validate request
     * @var Request $validate
     */
    protected $validate;

    /**
     * BMI Model
     * @var BMI $bmi
     */
    protected $bmi;

    public function __construct(){
        add_action('rest_api_init', array($this, 'bmi_api_register_routes'));
        
        $bmi = new BMI();
        $this->bmi = $bmi;

        $validate = new Request();
        $this->validate = $validate;
    }

    public function bmi_api_register_routes() {
        register_rest_route('api/v1', '/bmi/', array(
            'methods' => 'POST',
            'callback' => array($this, 'bmi_api_endpoint'),
        ));
    }

    public function bmi_api_endpoint($request)
    {
        $validateRequest = $this->validate->validateRequest($request);

        if(!$validateRequest['validate'])
        {
            return $this->_response([] , $validateRequest['status']);
        }
        
        $bmiResult = $this->bmi->bmiCalculate($request['info']);

        return $this->_response($bmiResult, 200);
    }
}

new Bmi_Api();