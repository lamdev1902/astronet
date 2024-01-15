<?php 
namespace Calculator\Api;

use Calculator\Models\ChineseGenderModel;
use Calculator\Api\AbstractApi;

class AgeCalculate extends AbstractApi{

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

        
        $time = $this->chineseGender->calculate($request);


        return $this->_response($time, 200);
    }
}
