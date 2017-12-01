<?php

/**
 * @author: Nriagu Dubem <nriagudubem@gmail.com>
 * Date: 12/1/17
 * Time: 10:03 PM
 */
class HttpUtilityService
{
    public static function makeGetRequest()
    {

    }

    public static function makePostRequest($url, array $data)
    {
        $client = new Client([
            'base_uri' => Config('amplify.paymentUrl'),
        ]);

        try {
            $response = $client->post($url, [
                'form_params' => $data
            ]);

        } catch (Exception $ex) {
            return $ex->getMessage();
        }
        
        $responseData = $response->getBody()->getContents();
        
        return \GuzzleHttp\json_decode($responseData,true);
        
    }


}