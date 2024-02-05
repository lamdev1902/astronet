<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit59e74e0a806cda5594fea4eb9938ef2f
{
    public static $prefixLengthsPsr4 = array (
        'C' => 
        array (
            'Crlt_\\LunarCalendar\\' => 20,
            'Calculator\\' => 11,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Crlt_\\LunarCalendar\\' => 
        array (
            0 => __DIR__ . '/..' . '/crlt/lunar-calendar/src',
        ),
        'Calculator\\' => 
        array (
            0 => __DIR__ . '/../..' . '/app',
        ),
    );

    public static $classMap = array (
        'Calculator\\Api\\AbsiCalculate' => __DIR__ . '/../..' . '/app/Api/AbsiCalculate.php',
        'Calculator\\Api\\AbstractApi' => __DIR__ . '/../..' . '/app/Api/AbstractApi.php',
        'Calculator\\Api\\AgeCalculate' => __DIR__ . '/../..' . '/app/Api/AgeCalculate.php',
        'Calculator\\Api\\ArmyBodyFatCalculate' => __DIR__ . '/../..' . '/app/Api/ArmyBodyFatCalculate.php',
        'Calculator\\Api\\BmiCalculate' => __DIR__ . '/../..' . '/app/Api/BmiCalculate.php',
        'Calculator\\Api\\BmrCalculate' => __DIR__ . '/../..' . '/app/Api/BmrCalculate.php',
        'Calculator\\Api\\BodyFatCalculate' => __DIR__ . '/../..' . '/app/Api/BodyFatCalculate.php',
        'Calculator\\Api\\CalorieBurnedCalculate' => __DIR__ . '/../..' . '/app/Api/CalorieBurnedCalculate.php',
        'Calculator\\Api\\CalorieCalculate' => __DIR__ . '/../..' . '/app/Api/CalorieCalculate.php',
        'Calculator\\Api\\ChineseGenderCalculate' => __DIR__ . '/../..' . '/app/Api/ChineseGenderCalculate.php',
        'Calculator\\Api\\Data\\AgeInterface' => __DIR__ . '/../..' . '/app/Api/Data/AgeInterface.php',
        'Calculator\\Api\\DueDateCalculate' => __DIR__ . '/../..' . '/app/Api/DueDateCalculate.php',
        'Calculator\\Api\\HealthyWeightCalculate' => __DIR__ . '/../..' . '/app/Api/HealthyWeightCalculate.php',
        'Calculator\\Api\\IdealWeightCalculate' => __DIR__ . '/../..' . '/app/Api/IdealWeightCalculate.php',
        'Calculator\\Api\\LeanBodyMassCalculate' => __DIR__ . '/../..' . '/app/Api/LeanBodyMassCalculate.php',
        'Calculator\\Api\\RequestValidate' => __DIR__ . '/../..' . '/app/Api/RequestValidate.php',
        'Calculator\\Api\\TdeeCalculate' => __DIR__ . '/../..' . '/app/Api/TdeeCalculate.php',
        'Calculator\\Api\\WaistToHeightRatioCalculate' => __DIR__ . '/../..' . '/app/Api/WaistToHeightRatioCalculate.php',
        'Calculator\\Helper\\Data' => __DIR__ . '/../..' . '/app/Helper/Data.php',
        'Calculator\\Models\\AbsiModel' => __DIR__ . '/../..' . '/app/Models/AbsiModel.php',
        'Calculator\\Models\\AbstractModel' => __DIR__ . '/../..' . '/app/Models/AbstractModel.php',
        'Calculator\\Models\\AgeModel' => __DIR__ . '/../..' . '/app/Models/AgeModel.php',
        'Calculator\\Models\\ArmyBodyFatModel' => __DIR__ . '/../..' . '/app/Models/ArmyBodyFatModel.php',
        'Calculator\\Models\\BmiModel' => __DIR__ . '/../..' . '/app/Models/BmiModel.php',
        'Calculator\\Models\\BmrModel' => __DIR__ . '/../..' . '/app/Models/BmrModel.php',
        'Calculator\\Models\\BodyFatModel' => __DIR__ . '/../..' . '/app/Models/BodyFatModel.php',
        'Calculator\\Models\\CalorieBurnedModel' => __DIR__ . '/../..' . '/app/Models/CalorieBurnedModel.php',
        'Calculator\\Models\\CalorieModel' => __DIR__ . '/../..' . '/app/Models/CalorieModel.php',
        'Calculator\\Models\\ChineseGenderModel' => __DIR__ . '/../..' . '/app/Models/ChineseGenderModel.php',
        'Calculator\\Models\\DueDateModel' => __DIR__ . '/../..' . '/app/Models/DueDateModel.php',
        'Calculator\\Models\\IdealWeightModel' => __DIR__ . '/../..' . '/app/Models/IdealWeightModel.php',
        'Calculator\\Models\\LeanBodyMassModel' => __DIR__ . '/../..' . '/app/Models/LeanBodyMassModel.php',
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
        'Crlt_\\LunarCalendar\\LunarCalendar' => __DIR__ . '/..' . '/crlt/lunar-calendar/src/LunarCalendar.php',
        'Crlt_\\LunarCalendar\\Utils' => __DIR__ . '/..' . '/crlt/lunar-calendar/common/Utils.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit59e74e0a806cda5594fea4eb9938ef2f::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit59e74e0a806cda5594fea4eb9938ef2f::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit59e74e0a806cda5594fea4eb9938ef2f::$classMap;

        }, null, ClassLoader::class);
    }
}
