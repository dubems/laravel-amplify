<?php
/**
 * @author: Nriagu Dubem <nriagudubem@gmail.com>
 * Date: 11/4/17
 * Time: 11:31 PM
 */

return [

    /**
     * Merchant ID gotten from your Amplify dashboard
     */

    'merchantId'=> getenv('AMPLIFY_MERCHANT_ID'),

    /**
     *  API Key from amplify dashboard
     */
    'apiKey' => getenv('AMPLIFY_API_KEY'),

    /**
     * Amplify payment Url
     */
    'paymentUrl' => getenv('AMPLIFY_PAYMENT_URL')
    
];