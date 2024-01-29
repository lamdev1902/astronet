<?php 
namespace Calculator\Api;

use Calculator\Api\AbstractApi;
use Calculator\Models\LeanBodyMassModel;

class LeanBodyMassCalculate extends AbstractApi{

    /**
     * Lean Body Mass Model
     * @var LeanBodyMassModel
     */
    protected $leanBody;

    public function __construct(
        
    ){
        add_action('rest_api_init', array($this, 'lean_body_mass_calculate_api_register_routes'));
        $leanBody = new LeanBodyMassModel();
        $this->leanBody = $leanBody;

    }


    public function lean_body_mass_calculate_api_register_routes() {
        register_rest_route('api/v1', '/leanbodymass-calculate/', array(
            'methods' => 'POST',
            'callback' => array($this, 'lean_body_mass_calculate_api_endpoint'),
        ));
    }

    public function lean_body_mass_calculate_api_endpoint($request)
    {

        $requestValidate  = $this->validate($request);
        
        if($requestValidate !== true)
        {
            return $requestValidate;
        }

        $result = $this->leanBody->calculate($request['info']);


        return $this->_response($result, 200);
    }

}
