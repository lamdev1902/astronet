<?php 
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'api.php';

use Calculator\Models\Age;

class Age_Api extends API
{
    /**
     * Validate request
     * @var Request $validate
     */
    protected $validate;

    /**
     * Age Model
     * @var Age $age
     */
    protected $age;

    public function __construct(){
        add_action('rest_api_init', array($this, 'age_api_register_routes'));
        
        $age = new Age();
        $this->age = $age;

        $validate = new Request();
        $this->validate = $validate;

        $helper = new Data();
        $this->helper = $helper;
    }

    public function age_api_register_routes() {
        register_rest_route('api/v1', '/age/', array(
            'methods' => 'POST',
            'callback' => array($this, 'age_api_endpoint'),
        ));
    }

    public function age_api_endpoint($request)
    {
        $data = [
            'from' => '1998-02-21',
            'to' => '2024-01-11'
        ];
        
        $time = $this->age->ageCalculate($data);
        // $validateRequest = $this->validate->validateRequest($request);

        
        // if(!$validateRequest['validate'])
        // {
        //     return $this->_response([] , $validateRequest['status']);
        // }
        
        // $result = $this->age->ageCalculate($request['info']);

        return $this->_response($time, 200);
    }
}

new Age_Api();