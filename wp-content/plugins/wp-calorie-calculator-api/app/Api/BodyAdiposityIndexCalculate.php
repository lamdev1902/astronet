<?php 
namespace Calculator\Api;

use Calculator\Models\BodyAdiposityIndexModel;
use Calculator\Api\AbstractApi;

class BodyAdiposityIndexCalculate extends AbstractApi 
{
    /**
     * Body Adiposity Index Model
     * @var BodyAdiposityIndexModel
     */
    protected $bodyAdiposityIndex;


    public function __construct(
        
    ){
        add_action('rest_api_init', array($this, 'body_adiposity_index_calculate_api_register_routes'));
        $bodyAdiposityIndex = new BodyAdiposityIndexModel();
        $this->bodyAdiposityIndex = $bodyAdiposityIndex;

    }


    public function body_adiposity_index_calculate_api_register_routes() {
        register_rest_route('api/v1', '/body-adiposity-index-calculate/', array(
            'methods' => 'POST',
            'callback' => array($this, 'body_adiposity_index_calculate_api_endpoint'),
        ));
    }

    public function body_adiposity_index_calculate_api_endpoint($request)
    {

        $requestValidate = $this->validate($request);

        if($requestValidate !== true)
        {
            return $requestValidate;
        }

        $result = $this->bodyAdiposityIndex->calculate($request['info']);

        return $this->_response($result, 200);
    }
}
