<?php 
namespace Calculator\Api;

use Calculator\Models\AbsiModel;
use Calculator\Api\AbstractApi;

class AbsiCalculate extends AbstractApi 
{
    /**
     * Absi Model
     * @var AbsiModel
     */
    protected $absiModel;


    public function __construct(
        
    ){
        add_action('rest_api_init', array($this, 'absi_calculate_api_register_routes'));
        $absiModel = new AbsiModel();
        $this->absiModel = $absiModel;

    }


    public function absi_calculate_api_register_routes() {
        register_rest_route('api/v1', '/absi-calculate/', array(
            'methods' => 'POST',
            'callback' => array($this, 'absi_calculate_api_endpoint'),
        ));
    }

    public function absi_calculate_api_endpoint($request)
    {

        $requestValidate = $this->validate($request);

        if($requestValidate !== true)
        {
            return $requestValidate;
        }

        $result = $this->absiModel->calculate($request['info']);

        return $this->_response($result, 200);
    }
}
