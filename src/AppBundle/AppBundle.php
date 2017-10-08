<?php

namespace AppBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class AppBundle extends Bundle
{
    public static function sendHttpRequest($url, $type = "GET", $params = array(), $headers = array(),$data_type="array",$username=false,$password=false)
    {

        $ch = curl_init();

        if($data_type=="array"){

            $fields_string = http_build_query($params);

        }else if($data_type=="json") {

            if(is_array($params)){
                $fields_string = json_encode($params);
            }else {
                $fields_string=$params;
            }


            $headers=array_merge($headers,[
                    'Content-Type:application/json;charset-utf-8',
                    'Accept:application/json;charset-utf-8'
                ]
            );

        }
        else if($data_type=="file") {

            //$fields_string = http_build_query($params);

            $fields_string=$params;
            // $fields_string = json_encode($params);

            curl_setopt($ch, CURLOPT_SAFE_UPLOAD, false);

        }

        if (isset($proxy)) {    // If the $proxy variable is set, then
            curl_setopt($ch, CURLOPT_PROXY, $proxy);    // Set CURLOPT_PROXY with proxy in $proxy variable
        }

        //curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        if($username&&$password)
        curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");



        if ($type == "GET") {
            curl_setopt($ch, CURLOPT_URL, $url . "?" . $fields_string);
        } else if ($type == "POST") {
            // print_r($fields_string);
            // exit;
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
            curl_setopt($ch, CURLOPT_URL, $url);

        } else if ($type == "PUT") {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
            curl_setopt($ch, CURLOPT_URL, $url);
        } else if ($type == "DELETE") {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
            curl_setopt($ch, CURLOPT_URL, $url);
        }


        $response = curl_exec($ch);




        if ($response === false) {
            $error = curl_errno($ch);
            $message = curl_error($ch);
            curl_close($ch);
            //return false;

        }

        // Check status code
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $message = curl_error($ch);
        curl_close($ch);


        return ($response);

    }

}
