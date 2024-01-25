<?php
namespace Calculator\Api;

class RequestValidate
{
    public function dateValidate($date)
    {
        return strtotime($date);
    }

    public function infoValidate($data)
    {
        foreach($data as $value)
        {  
            if(is_array($value))
            {
                foreach($value as $item)
                {
                    if(is_array($item)){
                        foreach($item as $key => $i)
                        {
                            if($key != 'inches'){
                                if(!is_numeric($i) || $i <= 0)
                                {
                                    return false;
                                }
                            }else {
                                if(!is_numeric($i))
                                {
                                    return false;
                                }

                                if($i < 0)
                                {
                                    return false;
                                }
                            }
                        }
                    }else {
                        if(!is_numeric($item) || $item <= 0)
                        {
                            return false;
                        }
                    }
                }
            }else {
                if(!is_numeric($value) || $value <= 0){
                    return false;
                }
            }

        }

        return true;
    }
}