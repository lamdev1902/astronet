<?php
namespace Calculator\Models;

use Calculator\Models\AbstractModel;
use Calculator\Helper\Data;

class CalorieBurnedModel extends AbstractModel
{
    /**
     * @var Data $helper
     */
    protected $helper;

    public function __construct()
    {
        $helper = new Data();
        $this->helper = $helper;
    }

    public function calculate($info)
    {

        $id = $info['activity'];

        $activityItem = $this->activityDefault($id);
        $result = [];

        $weight = $this->helper->kgConvert($info['weight']);

        $minutes = $info['duration']['hours'] ? $info['duration']['hours'] * 60 + $info['duration']['minutes'] : $info['duration']['minutes'];

        $result['burned']['calorie'] = round(($weight * $minutes * $activityItem[0]['met'])/200);
        $result['burned']['title'] = $activityItem[0]['title'];

        return $result;
    }

    private function activityBurned($id)
    {
        $activity =  [
            [
                'id' => 1,
                'title' => "Walking: slow",
                "met" => 7
            ],
            [
                'id' => 2,
                'title' => "Walking: moderate",
                "met" => 9
            ],
            [
                'id' => 3,
                'title' => "Walking: fast",
                "met" => 12
            ],
            [
                'id' => 4,
                'title' => "Walking: very fast",
                "met" => 16
            ],
            [
                'id' => 5,
                'title' => "Hiking: cross-country",
                "met" => 20
            ],
            [
                'id' => 6,
                'title' => "Running: slow",
                "met" => 27
            ],
            [
                'id' => 7,
                'title' => "Running: moderate",
                "met" => 35
            ],
            [
                'id' => 8,
                'title' => "Running: fast",
                "met" => 42
            ],
            [
                'id' => 9,
                'title' => "Running: very fast",
                "met" => 53
            ],
            [
                'id' => 10,
                'title' => "Running: cross-country",
                "met" => 30
            ],
            [
                'id' => 11,
                'title' => "Cycling: slow",
                "met" => 27
            ],
            [
                'id' => 12,
                'title' => "Cycling: moderate",
                "met" => 34
            ],
            [
                'id' => 13,
                'title' => "Cycling: fast",
                "met" => 41
            ],
            [
                'id' => 14,
                'title' => "Cycling: very fast",
                "met" => 56
            ],
            [
                'id' => 15,
                'title' => "Cycling: BMX or mountain",
                "met" => 29
            ],
            [
                'id' => 16,
                'title' => "Swimming: moderate",
                "met" => 20
            ],
            [
                'id' => 17,
                'title' => "Swimming: laps, vigorous",
                "met" => 34
            ]
        ];

        $items =  array_filter($activity, function ($item) use ($id) {
            return $item['id'] == $id;
        });

        return array_values($items);
    }

    private function activityGym($id)
    {
        $activity =  [
            [
                'id' => 1,
                'title' => "Aerobics, Step: high impact",
                "met" => 35
            ],
            [
                'id' => 2,
                'title' => "Aerobics, Step: moderate",
                "met" => 9
            ],
            [
                'id' => 3,
                'title' => "Aerobics, Step: fast",
                "met" => 12
            ],
            [
                'id' => 4,
                'title' => "Aerobics, Step: very fast",
                "met" => 16
            ],
            [
                'id' => 5,
                'title' => "Hiking: cross-country",
                "met" => 20
            ],
            [
                'id' => 6,
                'title' => "Running: slow",
                "met" => 27
            ],
            [
                'id' => 7,
                'title' => "Running: moderate",
                "met" => 35
            ],
            [
                'id' => 8,
                'title' => "Running: fast",
                "met" => 42
            ],
            [
                'id' => 9,
                'title' => "Running: very fast",
                "met" => 53
            ],
            [
                'id' => 10,
                'title' => "Running: cross-country",
                "met" => 30
            ],
            [
                'id' => 11,
                'title' => "Cycling: slow",
                "met" => 27
            ],
            [
                'id' => 12,
                'title' => "Cycling: moderate",
                "met" => 34
            ],
            [
                'id' => 13,
                'title' => "Cycling: fast",
                "met" => 41
            ],
            [
                'id' => 14,
                'title' => "Cycling: very fast",
                "met" => 56
            ],
            [
                'id' => 15,
                'title' => "Cycling: BMX or mountain",
                "met" => 29
            ],
            [
                'id' => 16,
                'title' => "Swimming: moderate",
                "met" => 20
            ],
            [
                'id' => 17,
                'title' => "Swimming: laps, vigorous",
                "met" => 34
            ]
        ];

        $items =  array_filter($activity, function ($item) use ($id) {
            return $item['id'] == $id;
        });

        return array_values($items);
    }
}