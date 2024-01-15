<?php
namespace Calculator\Models;
use Calculator\Models\AbstractModel;

class ChineseGenderModel extends AbstractModel
{
    public function calculate($data)
    {
        $result = [];

        $dueDate = strtotime($data['due_date']);
        $birthDate = new \DateTime($data['birth_date']);

        $month = date("m", $dueDate);

        $currentDate = new \DateTime();

        $time = $currentDate->diff($birthDate);

        $year = $time->y;
    }

    private function genderCalculate($data)
    {
        
    }
}