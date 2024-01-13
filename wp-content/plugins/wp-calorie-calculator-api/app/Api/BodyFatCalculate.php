<?php 
namespace Calculator\Api;

use Calculator\Models\BodyFatModel;
use Calculator\Api\AbstractApi;

class AgeCalculate extends AbstractApi 
{
    /**
     * Body Fat Model
     * @var BodyFatModel
     */
    protected $bodyFat;

    public function __construct(
        
    ){
        add_action('rest_api_init', array($this, 'body_fat_calculate_api_register_routes'));
        $bodyFat = new BodyFatModel();
        $this->bodyFat = $bodyFat;
    }


    public function body_fat_calculate_api_register_routes() {
        register_rest_route('api/v1', '/body-fat/', array(
            'methods' => 'POST',
            'callback' => array($this, 'body_fat_calculate_api_endpoint'),
        ));
    }

    public function body_fat_calculate_api_endpoint($request)
    {

        
        $time = $this->bodyFat->calculate($request['info']);

        return $this->_response($time, 200);
    }
}
