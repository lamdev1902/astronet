<?php
class Request 
{
    public function validateRequest($request, $type = '')
    {
        $validate = [
            'status' => 200,
            'validate' => true,
            'message' => ""
        ];

        if (
            !isset($request['info']) || (
                empty($request['info']['age']) ||
                empty($request['info']['gender']) ||
                empty($request['info']['weight']) ||
                empty($request['info']['height']) ||
                empty($request['info']['height']['feet']))
        ) {
           $validate['validate'] = false;  
        }else {
            if($type === "calorie")
            {
                
                if (empty($request['type']))
                {
                    $validate['validate'] = false;
                }


                if($request['receip'] == 3)
                {
                    if(!empty($request['info']['body-fat']))
                    {
                        $validate['validate'] = false;
                    }
                }

                if(empty($request['info']['activity']))
                {
                    $validate['validate'] = false;
                }
            }
        }

        if(!$validate['validate'])
        {
            $validate['status'] = 400;
        }

        return $validate;
    }
}