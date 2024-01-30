<?php 
namespace Calculator\Api;

use Calculator\Api\AbstractApi;
use Calculator\Models\BmiModel;
use Calculator\Models\CalorieModel;

class TdeeCalculate extends AbstractApi
{

    /**
     * BMI Model
     * @var BmiModel $bmi
     */
    protected $bmi;

    public function __construct(){
        add_action('rest_api_init', array($this, 'tdee_calculate_api_register_routes'));
        
        $bmi = new BmiModel();
        $this->bmi = $bmi;

        $calorie = new CalorieModel();
        $this->calorie = $calorie;

    }

    public function tdee_calculate_api_register_routes() {
        register_rest_route('api/v1', '/tdee-calculate/', array(
            'methods' => 'POST',
            'callback' => array($this, 'tdee_calculate_api_endpoint'),
        ));
    }

    public function tdee_calculate_api_endpoint($request)
    {
        $validate  = $this->validate($request);
        
        if($validate !== true)
        {
            return $validate;
        }
        $bmiResult = $this->bmi->calculate($request['info']);

        $calorieResult = $this->calorie->calculate($request);


        if(isset($calorieResult['calorie']['zigzag_schedule_1']))
        {
            unset($calorieResult['calorie']['zigzag_schedule_1']);
        }

        if(isset($calorieResult['calorie']['zigzag_schedule_2']))
        {
            unset($calorieResult['calorie']['zigzag_schedule_2']);
        }

        $maintain = $calorieResult['calorie']['loss'][0];

        
        unset($calorieResult['calorie']['loss'][0]);
        $calorieResult['calorie']['loss'] = array_values($calorieResult['calorie']['loss']);
        $calorieResult['calorie']['maintain'] = $maintain;

        unset($bmiResult['bmi']['prime']);
        unset($bmiResult['bmi']['ideal_weight']);
        unset($bmiResult['bmi']['propose']);
        unset($bmiResult['bmi']['ponderal']);

        $result['bmi'] = $bmiResult['bmi'];
        $result['calorie'] = $calorieResult['calorie'];
        return $this->_response($result, 200, $request['unit']);
    }
}
