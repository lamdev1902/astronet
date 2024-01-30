<?php
namespace Calculator\Models;

use Calculator\Models\AbstractModel;
use Calculator\Helper\Data;

class DueDateModel extends AbstractModel
{
    public function __construct()
    {
        $helper = new Data();
        $this->helper = $helper;
    }
    public function calculate($data)
    {
        $result = [];

        $startDate = new \DateTime($data['today']);

        $diff = $this->weekOfPregnancy($startDate);
        $result['week_of_pregnancy'] = $diff;

        $dueDate = $startDate->modify("+280 days");

        $dueDate = $dueDate->format('M j, Y');

        $result['due_date'] = $dueDate;

        $result['timester'] = $this->timester($result['week_of_pregnancy']['week']);
        
        return $result;

    }

    public function weekOfPregnancy($dueDate)
    {
        $result = [];
        $today = new \DateTime();

        $days = $today->diff($dueDate)->days;
        
        $diffWeek = floor($days/7);
        $diffDate = $days % 7;

        $result['week'] = $diffWeek;
        $result['weekday'] = $diffWeek . 'weeks and ' . $diffDate . ' days';

        $month = $diffWeek * 4;

        $text = ( $month > 0 ) ? $month . 'months and ' . $diffDate . ' days' : $diffDate . ' days';
        
        $result['monthday'] = $text;
        return $result;
    }

    private function timester($week)
    {
        if($week <= 13)
        {
            return "You are in the first trimester.";
        }else if($week > 13 && $week <= 27)
        {
            return "You are in the seconde trimester";
        }else {
            return "You are in the third trimester";
        }
    }

}