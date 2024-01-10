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
           $validate['test'] = 'ab';
        }else {
            if($type === "calorie")
            {
                
                if (empty($request['type']))
                {
                    $validate['validate'] = false;
                    $validate['test'] = 'bc';
                }


                if($request['receip'] == 3)
                {
                    if(empty($request['info']['body-fat']))
                    {
                        $validate['validate'] = false;
                        $validate['test'] = 'cd';
                    }
                }

                if(empty($request['info']['activity']))
                {
                    $validate['validate'] = false;
                    $validate['test'] = 'de';
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