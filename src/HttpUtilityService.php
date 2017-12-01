<?php

/**
 * @author: Nriagu Dubem <nriagudubem@gmail.com>
 * Date: 12/1/17
 * Time: 10:03 PM
 */
class HttpUtilityService
{
    public static function makeGetRequest(array $data, $url)
    {
        $client = new Client([
            'base_uri' => Config('amplify.paymentUrl'),
        ]);

        try {
            $response = $client->request('GET', '/merchant/verify', [
                'query' => $data
            ]);

            $responseData = $response->getBody()->getContents();

            return \GuzzleHttp\json_decode($responseData, true);

        } catch (Exception $ex) {
            return $ex->getMessage();
        }
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

            $responseData = $response->getBody()->getContents();

            return \GuzzleHttp\json_decode($responseData, true);

        } catch (Exception $ex) {
            return $ex->getMessage();
        }

    }


}