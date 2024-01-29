<?php 
namespace Calculator\Api;

use Calculator\Models\ArmyBodyFatModel;
use Calculator\Api\AbstractApi;

class ArmyBodyFatCalculate extends AbstractApi 
{
    /**
     * Army Body Fat Model
     * @var ArmyBodyFatModel
     */
    protected $armyBodyFat;

    public function __construct(
        
    ){
        add_action('rest_api_init', array($this, 'army_body_fat_calculate_api_register_routes'));
        $armyBodyFat = new ArmyBodyFatModel();
        $this->armyBodyFat = $armyBodyFat;
    }


    public function army_body_fat_calculate_api_register_routes() {
        register_rest_route('api/v1', '/armybodyfat-calculate/', array(
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

        $result = $this->armyBodyFat->calculate($request['info']);

        return $this->_response($result, 200);
    }
}
