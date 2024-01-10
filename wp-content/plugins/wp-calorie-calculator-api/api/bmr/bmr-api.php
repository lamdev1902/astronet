<?php 
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'api.php';
class Bmr_Api extends API
{
    /**
     * Validate request
     * @var Request $validate
     */
    protected $validate;

    /**
     * BMR Model
     * @var BMR $bmr
     */
    protected $bmr;

    public function __construct(){
        add_action('rest_api_init', array($this, 'bmr_api_register_routes'));
        
        $bmr = new BMR();
        $this->bmr = $bmr;

        $validate = new Request();
        $this->validate = $validate;

        $due = new Due_Date();
        $this->due = $due;
    }

    public function bmr_api_register_routes() {
        register_rest_route('api/v1', '/bmr/', array(
            'methods' => 'POST',
            'callback' => array($this, 'bmr_api_endpoint'),
        ));
    }

    public function bmr_api_endpoint($request)
    {
        $validateRequest = $this->validate->validateRequest($request);

        $data = [
            'date' => '2023-01-01',
            'average' => '28'
        ];
        
        $date = $this->due->DueDateCalculate($data);
        if(!$validateRequest['validate'])
        {
            return $this->_response([] , $validateRequest['status']);
        }
        
        $bmrResult = $this->bmr->BMRCalculate($request['info'], $request['receip']);

        return $this->_response($bmrResult, 200);
    }
}

new Bmr_Api();