<?php

/**
 * @author: Nriagu Dubem <nriagudubem@gmail.com>
 * Date: 12/1/17
 * Time: 10:03 PM
 */

namespace Dubems\Amplify;

use Exception;
use Illuminate\Support\Facades\Config;
use GuzzleHttp\Client;

class HttpUtilityService
{
    /** Make get request
     *
     * @param array $data
     * @param $url
     * @return mixed|string
     */
    public static function makeGetRequest(array $data, $url)
    {
        $client = new Client([
            'base_uri' => Config::get('amplify.paymentUrl'),
        ]);

        try {
            $response = $client->request('GET', $url, [
                'query' => $data
            ]);

            $responseData = $response->getBody()->getContents();

            return \GuzzleHttp\json_decode($responseData, true);

        } catch (Exception $ex) {
            return $ex->getMessage();
        }
    }

    /**Make post request
     *
     * @param $url
     * @param array $data
     * @return mixed|string
     */
    public static function makePostRequest($url, array $data)
    {
        $client = new Client([
            'base_uri' => Config::get('amplify.paymentUrl'),
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

    /**Make put request
     *
     * @param $url
     * @param $queryParam
     * @param array $data
     * @return mixed|string
     */
    public static function makePutRequest($url, $queryParam, array $data)
    {
        $client = new Client([
            'base_uri' => Config::get('amplify.paymentUrl'),
        ]);

        try {
            $response = $client->request('PUT', $url, [
                'query' => $queryParam,
                'form_params' => $data
            ]);

            $responseData = $response->getBody()->getContents();

            return \GuzzleHttp\json_decode($responseData, true);

        } catch (Exception $ex) {
            return $ex->getMessage();
        }
    }

    /**Make delete Request
     *
     * @param $url
     * @param $queryParam
     * @return mixed|string
     */
    public static function makeDeleteRequest($url, $queryParam)
    {
        $client = new Client([
            'base_uri' => Config::get('amplify.paymentUrl'),
        ]);

        try {
            $response = $client->request('DELETE', $url, [
                'query' => $queryParam,
            ]);

            $responseData = $response->getBody()->getContents();

            return \GuzzleHttp\json_decode($responseData, true);

        } catch (Exception $ex) {
            return $ex->getMessage();
        }
    }

}