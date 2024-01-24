<?php
namespace Calculator;
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

require __DIR__ . '/vendor/autoload.php';

$testApi = new \Calculator\Api\AgeCalculate();
$bodyFatApi = new \Calculator\Api\BodyFatCalculate();
$chineseGenderApi = new \Calculator\Api\ChineseGenderCalculate();
$bmrApi = new \Calculator\Api\BmrCalculate();
$bmiApi = new \Calculator\Api\BmiCalculate();
$idealWeightApi = new \Calculator\Api\IdealWeightCalculate();
$healthyWeightApi = new \Calculator\Api\HealthyWeightCalculate();
$calorieApi = new \Calculator\Api\CalorieCalculate();
$leanBodyApi = new \Calculator\Api\LeanBodyMassCalculate();
$waistToHeightRatioApi = new \Calculator\Api\WaistToHeightRatioCalculate();
