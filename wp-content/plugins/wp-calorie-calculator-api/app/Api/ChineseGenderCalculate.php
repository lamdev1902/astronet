<?php 
namespace Calculator\Api;

use Calculator\Models\ChineseGenderModel;
use Calculator\Api\AbstractApi;

class ChineseGenderCalculate extends AbstractApi{

    /**
     * Chinese Gender Model
     * @var ChineseGenderModel
     */
    protected $chineseGender;


    public function __construct(
        
    ){
        add_action('rest_api_init', array($this, 'chinese_gender_calculate_api_register_routes'));
        $chineseGender = new ChineseGenderModel();
        $this->chineseGender = $chineseGender;

    }


    public function chinese_gender_calculate_api_register_routes() {
        register_rest_route('api/v1', '/chinese-gender/', array(
            'methods' => 'POST',
            'callback' => array($this, 'chinese_gender_calculate_api_endpoint'),
        ));
    }

    public function chinese_gender_calculate_api_endpoint($request)
    {
        if($request->get_params())
        {
            $checkDd = $this->dateValidate($request['dd']);
            $checkDob = $this->dateValidate($request['dob']);

            
            if($checkDd && $checkDob)
            {
                $currentDate = date('Y-m-d');

                $dd = date($request['dd']);
                $dob = date($request['dob']);

                if($dd < $currentDate)
                {
                    return $this->_response([], 400);
                }

                if($dd < $dob)
                {
                    return $this->_response([], 400);
                }
            }else {
                return $this->_response([], 400);
            }
        }
        
        $time = $this->chineseGender->calculate($request);


        return $this->_response($time, 200);
    }
}
