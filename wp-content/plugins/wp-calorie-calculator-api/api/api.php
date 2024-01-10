<?php 
abstract class API 
{
    protected function _response($data, $status, $unit = '')
    {
        $result = [];
        $result['status'] = $status;
        $result['result'] = $data;

        if($unit)
        {
            $result['unit'] = 1;
        }
        $result['message'] = $this->_status($status);


        return rest_ensure_response($result);
    }


    private function _status($code)
    {
        $status = array(
            200 => "Success",
            400 => "Please check the values!",
            404 => "Not Found",
            500 => "Internal Server Error"
        );

        return $status[$code] ? $status[$code] : $status[500];
    }

}