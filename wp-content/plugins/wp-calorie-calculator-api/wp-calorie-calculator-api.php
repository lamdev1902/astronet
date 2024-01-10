<?php
/*
 * Plugin Name:       WP Calorie Calculator API
 * Description:       Calorie Calculator gives you the shortcode with the flexible settings that you can place into the page, post or sidebar widget. Or actually anywhere you can place the shortcode.
 * Version:           1.0.0
 * Author:            Belov Digital Agency
 * Author URI:        https://belovdigital.agency
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wp-calorie-calculator-api
 * Domain Path:       /languages
*/

require_once(plugin_dir_path(__FILE__) . 'validate/request.php');
require_once(plugin_dir_path(__FILE__) . 'helper/data.php');
require_once(plugin_dir_path(__FILE__) . 'models/bmi.php');
require_once(plugin_dir_path(__FILE__) . 'models/bmr.php');
require_once(plugin_dir_path(__FILE__) . 'models/duedate.php');
require_once(plugin_dir_path(__FILE__) . 'api/calorie/calorie-api.php');
require_once(plugin_dir_path(__FILE__) . 'api/bmi/bmi-api.php');
require_once(plugin_dir_path(__FILE__) . 'api/bmr/bmr-api.php');


// Add an endpoint for the API
add_action('rest_api_init', 'custom_api_register_routes');

function custom_api_register_routes() {
    register_rest_route('api/v1', '/calorie-calcularor/', array(
        'methods' => 'POST',
        'callback' => 'custom_api_get_posts',
    ));
}

// Callback function to return posts
function custom_api_get_posts($req = null) {
    $result = array();

    if($req['calorie']['type'] === "0")
    {
        if(!isset($req['calorie']['info']['height']['feet'])){
            $result = validateHeight($req['calorie']['type']);
        }else {
            $result = calculatorImperial($req['calorie']['info']);
        }
    }
    if($req['calorie']['type'] === "1"){
        if(isset($req['calorie']['info']['height']['feet'])){
            $result = validateHeight($req['calorie']['type']);
        }else {
            $result = calculatorMetric($req['calorie']['info']);
        }
    }

    if($req['calorie']['type'] === ""){
        $result = unprocessableEntityResponse('type');
    }
    
    return rest_ensure_response($result);
};

function calculatorImperial($info)
{
    $activityId = $info['activity'] ? $info['activity'] : "0";
    $activityItem = get_activity($activityId);

    $feet = $info['height']['feet'] ? $info['height']['feet'] : 0;
    $inches = $info['height']['inches'] ? $info['height']['inches'] : 0;

    if((!$feet && (!$inches || $inches < 10)) ){
        return unprocessableEntityResponse('feet and inches');
    }

    if(!$info['age'] || $info['age'] < 15 || $info['age'] > 80){
        return unprocessableEntityResponse('age');
    }

    if(!$info['weight']){
        return unprocessableEntityResponse('weight');
    }
    
    $cm = ( $feet * 30.48 ) + ($inches * 2.54 );
    
    if($cm > 300) {
        return unprocessableEntityResponse('height');
    }
    unset($info['height']['feet']);
    unset($info['height']['inches']);

    $info['height'] = round($cm,1);

    $info['weight'] =  round($info['weight'] * 0.45359237, 1);

    $bmr = calculatorBMR($info);

    $result = [];

    if($activityId === "0")
    {
        $result['status'] = 200;
        $result['result'][] = [
            'goal_type' => 0,
            'name' => $activityItem[$activityId]['name'],
            'calorie' => $bmr
        ];

        return $result;
    };

    $bmi = calculatorBMI($info['height'], $info['weight']);


    $bmr = $bmr * $activityItem[$activityId]['coefficient'];

    $result = result($bmi, $bmr, 'lb');
    
    return $result;
}

function calculatorMetric($info)
{
    $activityId = $info['activity'] ? $info['activity'] : "0";
    $activityItem = get_activity($activityId);
    
    $height = $info['height'];
    $weight = $info['weight'];
    if(!$height){
        return unprocessableEntityResponse('height');
    }

    if(!$weight){
        return unprocessableEntityResponse('weight');
    }
    
    $bmr = calculatorBMR($info);

    $result = [];

    if($activityId === "0")
    {
        $result[] = [
            'goal_type' => 0,
            'name' => $activityItem[$activityId]['name'],
            'calorie' => $bmr
        ];

        return $result;
    };

    $bmi = calculatorBMI($info['height'], $info['weight']);

    $bmr = $bmr * $activityItem[$activityId]['coefficient'];

    $result = result($bmi, $bmr, 'kg');
    
    return $result;
}

function calculatorBMI($height,$weight)
{
    $height = round($height / 100, 1);

    $bmi = $weight / ($height * $height);
    
    if($bmi < 18.5) {
        return 0;
    };

    if($bmi > 25) {
        return 2;
    }

    return 1;
}

function result($bmi, $bmr, $unit)
{
    $goals = get_calculator_default_goals_loss();

    $message = "Normal weight";

    if($bmi === 0)
    {
        $message = "Under weight";
        $goals = array_filter($goals, function ($item){
            return $item['type'] != 1;
        });
    }
    
    if($bmi === 2){
        $message = "Over weight";
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
        if($goal['type'] != 0){
            $description = round($goal['coefficient'] * $param,2). ' ' . $unit .'/week';
        }
        if($goal['type'] == 1)
        {
            $calorie = $calorie - $goal['coefficient'] * 1000;
        }elseif($goal['type'] == 2){
            $calorie = $calorie + $goal['coefficient'] * 1000;
        };
        $result['result'][] = [
            'goal_type' => $goal['type'],
            'name' => $goal['name'],
            'calorie' => floor($calorie),
            'description' => $description
        ];

        
    }

    $result['status'] = 200;
    $result['message'] = $message;

    return $result;
}

function validateHeight($type){
    $message = "";
    if($type === "0")
    {
        $message = "Imperial type only accepts height units as feet and inches!";
    }
    if($type === "1")
    {
        $message = "Metric type only accepts height units as cm!";
    }
    $response['status'] = '400';
    $response['body']['result'] = [];
    $response['body'] = [
        'error' => $message 
    ];
    return $response;
}

function unprocessableEntityResponse($param)
{
    $response['status'] = '400';
    $response['body']['result'] = [];
    $response['body'] = [
        'error' => 'Invalid input - '.$param 
    ];
    return $response;
}

function calculatorBMR($info){
    $weight = $info['weight'];
    $height = $info['height'];
    
    $age = $info['age'];

    $genderNumber = $info['gender'] == 0 ? 5 : -161;
    
    return round( (10 * $weight) + ( 6.25 * $height ) - ( 5 * $age ) + $genderNumber );
}

function get_activity($activityId)
{
    $activity = get_calculator_activity();
    $activityItem = array_filter($activity, function ($item) use ($activityId) {
        return $item['id'] == $activityId;
    });

    return $activityItem;
}

function get_calculator_default_goals_loss() {
    $goals = array(
        array(
            'type'        => 0,
            'name'        => __( 'Maintain Weight', 'wp-calorie-calculator' ),
            'coefficient' => 1,
        ),
        array(
            'type'        => 1,
            'name'        => __( 'Mild Weight Loss', 'wp-calorie-calculator' ),
            'coefficient' => 0.25
        ),
        array(
            'type'        => 1,
            'name'        => __( 'Weight Loss', 'wp-calorie-calculator' ),
            'coefficient' => 0.5,
        ),
        array(
            'type'        => 1,
            'name'        => __( 'Extreme Weight Loss', 'wp-calorie-calculator' ),
            'coefficient' => 1,
        ),
        array(
            'type'        => 2,
            'name'        => __( 'Mild Weight Gain', 'wp-calorie-calculator' ),
            'coefficient' => 0.25,
        ),
        array(
            'type'        => 2,
            'name'        => __( 'Weight Gain', 'wp-calorie-calculator' ),
            'coefficient' => 0.5
        ),
        array(
            'type'        => 2,
            'name'        => __( 'Fast Weight Gain', 'wp-calorie-calculator' ),
            'coefficient' => 1
        ),
    );

    return $goals;
}

/**
 * Calculator default activity.
 *
 * @since    3.0.0
 */
function get_calculator_activity() {
    $activity = array(
        array(
            'id'          => 0,
            'name'        => esc_html__( 'Basal Metabolic Rate (BMR)', 'wp-calorie-calculator' ),
            'description' => esc_html__( 'Basal Metabolic Rate (BMR)', 'wp-calorie-calculator' ),
            'coefficient' => 1,
        ),
        array(
            'id'          => 1,
            'name'        => esc_html__( 'Sedentary: little or no exercise', 'wp-calorie-calculator' ),
            'description' => esc_html__( 'Spend most of the day sitting, with little or no exercise', 'wp-calorie-calculator' ),
            'coefficient' => 1.2,
        ),
        array(
            'id'          => 2,
            'name'        => esc_html__( 'Light: exercise 1-3 times/week', 'wp-calorie-calculator' ),
            'description' => esc_html__( 'Exercise 1-3 times/week', 'wp-calorie-calculator' ),
            'coefficient' => 1.375,
        ),
        array(
            'id'          => 3,
            'name'        => esc_html__( 'Moderate: exercise 4-5 times/week', 'wp-calorie-calculator' ),
            'description' => esc_html__( 'Exercise 4-5 times/week', 'wp-calorie-calculator' ),
            'coefficient' => 1.465,
        ),
        array(
            'id'          => 4,
            'name'        => esc_html__( 'Active: daily exercise or intense exercise 3-4 times/week', 'wp-calorie-calculator' ),
            'description' => esc_html__( 'Daily exercise or intense exercise 3-4 times/week', 'wp-calorie-calculator' ),
            'coefficient' => 1.55,
        ),
        array(
            'id'          => 5,
            'name'        => esc_html__( 'Very Active: intense exercise 6-7 times/week', 'wp-calorie-calculator' ),
            'description' => esc_html__( 'Intense exercise 6-7 times/week', 'wp-calorie-calculator' ),
            'coefficient' => 1.725,
        ),
        array(
            'id'          => 6,
            'name'        => esc_html__( 'Extra Active: very intense exercise daily, or physical job', 'wp-calorie-calculator' ),
            'description' => esc_html__( 'Very intense exercise daily, or physical job', 'wp-calorie-calculator' ),
            'coefficient' => 1.9,
        ),
    );

    return $activity;
}