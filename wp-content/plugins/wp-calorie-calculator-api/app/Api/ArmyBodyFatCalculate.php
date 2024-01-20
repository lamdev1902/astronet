<?php 
namespace Calculator\Api;

use Calculator\Models\BodyFatModel;
use Calculator\Api\AbstractApi;

class ArmyBodyFatCalculate extends AbstractApi 
{
    /**
     * Body Fat Model
     * @var BodyFatModel
     */
    protected $bodyFat;

    public function __construct(
        
    ){
        add_action('rest_api_init', array($this, 'army_body_fat_calculate_api_register_routes'));
        $bodyFat = new BodyFatModel();
        $this->bodyFat = $bodyFat;
    }


    public function army_body_fat_calculate_api_register_routes() {
        register_rest_route('api/v1', '/army-bodyfat/', array(
            'methods' => 'POST',
            'callback' => array($this, 'army_body_fat_calculate_api_endpoint'),
        ));
    }

    public function army_body_fat_calculate_api_endpoint($request)
    {
        $requestValidate = $this->validate($request);

        if($requestValidate !== true)
        {
            return $requestValidate;
        }

        $time = $this->bodyFat->bodyfatCalculate($request);

        return $this->_response($time, 200);
    }
}
