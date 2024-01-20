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
                        foreach($item as $i)
                        {
                            if(!is_numeric($i))
                            {
                                return false;
                            }
                        }
                    }else {
                        if(!is_numeric($item))
                        {
                            return false;
                        }
                    }
                }
            }else {
                if(!is_numeric($value)){
                    return false;
                }
            }

            
        }

        return true;
    }
}