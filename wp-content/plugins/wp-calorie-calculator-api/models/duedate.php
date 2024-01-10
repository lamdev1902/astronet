<?php
class Due_Date
{

    public function DueDateCalculate($data)
    {
        $lastMenstrualDate = new DateTime($data['date']);

        $average_length = $data['average'];

        $lastMenstrualDate->modify('+' . ((int)$average_length + 280) . ' days');

        return $lastMenstrualDate->format('Y-m-d');
    }
}