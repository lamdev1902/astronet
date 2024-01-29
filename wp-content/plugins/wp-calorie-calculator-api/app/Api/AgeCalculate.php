<?php 
namespace Calculator\Api;

use Calculator\Models\AgeModel;
use Calculator\Api\Data\AgeInterface;
use Calculator\Api\AbstractApi;

class AgeCalculate extends AbstractApi{

    /**
     * Age Model
     * @var Age
     */
    protected $age;

    public function __construct(
        
    ){
        add_action('rest_api_init', array($this, 'age_calculate_api_register_routes'));
        $age = new AgeModel();
        $this->age = $age;

    }


    public function age_calculate_api_register_routes() {
        register_rest_route('api/v1', '/age-calculate/', array(
            'methods' => 'POST',
            'callback' => array($this, 'age_calculate_api_endpoint'),
        ));
    }

    public function age_calculate_api_endpoint($request)
    {

        $checkAgeOfTheDate = $this->dateValidate($request['ageat']);
        $checkDob = $this->dateValidate($request['dob']);

        if($checkAgeOfTheDate && $checkDob){
            $ageOfTheDate = new \DateTime($request['ageat']);
            $dob = new \DateTime($request['dob']);

            if($ageOfTheDate < $dob)
            {
                return $this->_response([], 400);
            }
        }else {
            return $this->_response([], 400);
        }

        $time = $this->age->calculate($request);


        return $this->_response($time, 200);
    }

}
