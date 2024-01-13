<?php 
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'api.php';
class Body_Fat_Api extends API
{
    /**
     * Validate request
     * @var Request $validate
     */
    protected $validate;

    /**
     * Body Fat Model
     * @var Body_Fat $bodyFat
     */
    protected $bodyFat;

    public function __construct(){
        add_action('rest_api_init', array($this, 'body_fat_api_register_routes'));
        
        $bodyFat = new Body_Fat();
        $this->bodyFat = $bodyFat;

        $validate = new Request();
        $this->validate = $validate;

        $helper = new Data();
        $this->helper = $helper;
    }

    public function body_fat_api_register_routes() {
        register_rest_route('api/v1', '/body-fat/', array(
            'methods' => 'POST',
            'callback' => array($this, 'body_fat_api_endpoint'),
        ));
    }

    public function body_fat_api_endpoint($request)
    {
        $data = [
            'from' => '1998-02-21',
            'to' => '2024-01-11'
        ];
        
        $time = $this->bodyFat->ageCalculate($data);
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