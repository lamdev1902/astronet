<?php 
namespace Calculator\Api;

use Calculator\Api\AbstractApi;
use Calculator\Models\BmrModel;

class BmrCalculate extends AbstractApi
{
    /**
     * BMR Model
     * @var BmrModel $bmr
     */
    protected $bmr;

    public function __construct(){
        add_action('rest_api_init', array($this, 'bmr_calculate_api_register_routes'));
        
        $bmr = new BmrModel();
        $this->bmr = $bmr;
    }

    public function bmr_calculate_api_register_routes() {
        register_rest_route('api/v1', '/bmr-calculate/', array(
            'methods' => 'POST',
            'callback' => array($this, 'bmr_calculate_api_endpoint'),
        ));
    }

    public function bmr_calculate_api_endpoint($request)
    {
        
        $result = $this->bmr->calculate($request);


        return $this->_response($result, 200);
    }
}