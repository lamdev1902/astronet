<?php 
namespace Calculator\Api;

use Calculator\Models\DueDateModel;
use Calculator\Api\Data\AgeInterface;
use Calculator\Api\AbstractApi;

class DueDateCalculate extends AbstractApi
{

    /**
     * Due Date Model
     * @var DueDateModel
     */
    protected $dueDate;

    public function __construct(
        
    ){
        add_action('rest_api_init', array($this, 'due_date_calculate_api_register_routes'));
        $dueDate = new DueDateModel();
        $this->dueDate = $dueDate;

    }


    public function due_date_calculate_api_register_routes() {
        register_rest_route('api/v1', '/due-date/', array(
            'methods' => 'POST',
            'callback' => array($this, 'due_date_calculate_api_endpoint'),
        ));
    }

    public function due_date_calculate_api_endpoint($request)
    {

        $today = $this->dateValidate($request['today']);

        if(!$today)
        {
            return $this->_response([], 400);
        }

        $requestParams = $request->get_params();

        unset($requestParams['today']);

        $requestValidate = $this->infoValidate($request);

        if($requestValidate !== true)
        {
            return $requestValidate;
        }

        

        $result = $this->dueDate->calculate($request->get_params());


        return $this->_response($result, 200);
    }

}
