<?php 
namespace Calculator\Api;

use Calculator\Api\RequestValidate;

abstract class AbstractApi extends RequestValidate
{
    protected function _response($data, $status, $unit = '')
    {
        $result = [];
        $result['status'] = $status;
        $result['result'] = $data;

        if($unit) {
            $result['result']['unit'] = $unit;
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

    protected function validate($request)
    {
        if($request->get_params())
        {
            if(!$this->infoValidate($request->get_params()))
            {
                return $this->_response([], 400);
            }
        }else {
            return $this->_response([], 400);
        }

        return true;
    }
}