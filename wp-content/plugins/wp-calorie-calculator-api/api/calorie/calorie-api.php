<?php 
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'api.php';
class Calorie_Api extends API
{
    public function __construct(){
        add_action('rest_api_init', array($this, 'test_api_register_routes'));
        $validate = new Request();
        $this->validate = $validate;
    }

    public function test_api_register_routes() {
        register_rest_route('api/v1', '/calorie/', array(
            'methods' => 'POST',
            'callback' => array($this, 'test_api_endpoint'),
        ));
    }

    public function test_api_endpoint($request)
    {
        $validate = $this->validate->validateRequest($request, "calorie");
        if(!$validate['validate'])
        {
            return $this->_response([] , $validate['status']);
        }

        $helper = new Data();
        $bmi = new BMI();

        $info = $request['info'];

        $receip = $request['receip'];

        $bmiResult = $bmi->bmiCalculate($info);

        $bmr = new BMR();

        $bmrResult = $bmr->BMRCalculate($info,$receip);

        $activity = $this->get_activity($request['info']['activity']);

        $activityItem = reset($activity);
        if($request['info']['activity'] != 1)
        {
            
            $bmrResult = $bmrResult['bmr']['calorie'] * $activityItem['coefficient'];
            $result = $this->result($bmiResult, round($bmrResult), 'lb');
        }else {
            $result[] = [
                'goal_type' => 1,
                'name' => $activityItem['name'],
                'calorie' => $bmrResult['bmr']['calorie']
            ];
        }

        $unit = '';
        
        if($request['unit'] == 2)
        {
            $result = $helper->kilojoulesConvert($result);
            $unit = 'kilojoules';
        }
        return $this->_response($result, 200, $unit);
    }

    /**
     * Level of activity
     * 
     * @return array
     */
    private function get_activity($id)
    {
        $activity = $this->get_calculator_activity();
        $activityItem = array_filter($activity, function ($item) use ($id) {
            return $item['id'] == $id;
        });

        return $activityItem;
    }
    

    private function get_calculator_activity() {
        $activity = array(
            array(
                'id'          => 1,
                'name'        => esc_html__( 'Basal Metabolic Rate (BMR)', 'wp-calorie-calculator' ),
                'description' => esc_html__( 'Basal Metabolic Rate (BMR)', 'wp-calorie-calculator' ),
                'coefficient' => 1,
            ),
            array(
                'id'          => 2,
                'name'        => esc_html__( 'Sedentary: little or no exercise', 'wp-calorie-calculator' ),
                'description' => esc_html__( 'Spend most of the day sitting, with little or no exercise', 'wp-calorie-calculator' ),
                'coefficient' => 1.2,
            ),
            array(
                'id'          => 3,
                'name'        => esc_html__( 'Light: exercise 1-3 times/week', 'wp-calorie-calculator' ),
                'description' => esc_html__( 'Exercise 1-3 times/week', 'wp-calorie-calculator' ),
                'coefficient' => 1.375,
            ),
            array(
                'id'          => 4,
                'name'        => esc_html__( 'Moderate: exercise 4-5 times/week', 'wp-calorie-calculator' ),
                'description' => esc_html__( 'Exercise 4-5 times/week', 'wp-calorie-calculator' ),
                'coefficient' => 1.465,
            ),
            array(
                'id'          => 5,
                'name'        => esc_html__( 'Active: daily exercise or intense exercise 3-4 times/week', 'wp-calorie-calculator' ),
                'description' => esc_html__( 'Daily exercise or intense exercise 3-4 times/week', 'wp-calorie-calculator' ),
                'coefficient' => 1.55,
            ),
            array(
                'id'          => 6,
                'name'        => esc_html__( 'Very Active: intense exercise 6-7 times/week', 'wp-calorie-calculator' ),
                'description' => esc_html__( 'Intense exercise 6-7 times/week', 'wp-calorie-calculator' ),
                'coefficient' => 1.725,
            ),
            array(
                'id'          => 7,
                'name'        => esc_html__( 'Extra Active: very intense exercise daily, or physical job', 'wp-calorie-calculator' ),
                'description' => esc_html__( 'Very intense exercise daily, or physical job', 'wp-calorie-calculator' ),
                'coefficient' => 1.9,
            ),
        );
    
        return $activity;
    }

    private function get_calculator_default_goals(){
        $goals = array(
            array(
                'type'        => 1,
                'name'        => __( 'Maintain Weight', 'wp-calorie-calculator' ),
                'coefficient' => 1,
            ),
            array(
                'type'        => 2,
                'name'        => __( 'Mild Weight Loss', 'wp-calorie-calculator' ),
                'coefficient' => 0.25
            ),
            array(
                'type'        => 2,
                'name'        => __( 'Weight Loss', 'wp-calorie-calculator' ),
                'coefficient' => 0.5,
            ),
            array(
                'type'        => 2,
                'name'        => __( 'Extreme Weight Loss', 'wp-calorie-calculator' ),
                'coefficient' => 1,
            ),
            array(
                'type'        => 3,
                'name'        => __( 'Mild Weight Gain', 'wp-calorie-calculator' ),
                'coefficient' => 0.25,
            ),
            array(
                'type'        => 3,
                'name'        => __( 'Weight Gain', 'wp-calorie-calculator' ),
                'coefficient' => 0.5
            ),
            array(
                'type'        => 3,
                'name'        => __( 'Fast Weight Gain', 'wp-calorie-calculator' ),
                'coefficient' => 1
            ),
        );
    
        return $goals;
    }

    private function result($bmi, $bmr, $unit)
    {
        $goals = $this->get_calculator_default_goals();
    
    
        if($bmi['bmi']['type'] == 1)
        {
            $goals = array_filter($goals, function ($item){
                return $item['type'] != 2;
            });
        }
    
        if($unit === "lb")
        {
            $param = 2;
        }else {
            $param = 1;
        }
        
        foreach($goals as $key => $goal){
            $calorie = $bmr;
            $description = "";
            if($goal['type'] != 1){
                $description = round($goal['coefficient'] * $param,2). ' ' . $unit .'/week';
            }
            if($goal['type'] == 2)
            {
                $calorie = $calorie - $goal['coefficient'] * 1000;
            }elseif($goal['type'] == 3){
                $calorie = $calorie + $goal['coefficient'] * 1000;
            };
            
            $result[] = [
                'goal_type' => $goal['type'],
                'name' => $goal['name'],
                'calorie' => round($calorie),
                'description' => $description
            ];
    
            
        }
    
        return $result;
    }
}

new Calorie_Api();