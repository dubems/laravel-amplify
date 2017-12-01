<?php

/**
 * @author: Nriagu Dubem <nriagudubem@gmail.com>
 * Date: 11/4/17
 * Time: 11:28 PM
 */
class Amplify
{
    /**
     * @var
     */
    private $merchantId;
    /**
     * @var
     */
    private $apikey;
    /**
     * @var
     */
    private $paymentUrl;
    /**
     * @var
     */
    private $redirectUrl;

    private $response;

    public function __construct()
    {
        $this->setAPIKey();
        $this->setMerchantId();
    }

    public function setMerchantId()
    {
        $this->merchantId = Config::get('amplify.merchantId');
    }

    public function setAPIKey()
    {
        $this->apikey = Config::get('amplify.apiKey');
    }

    public function setRedirectUrl()
    {
        $this->redirectUrl = Config('amplify.redirectUrl');
    }

    public function getMerchantId()
    {
        return $this->merchantId;
    }

    public function getApiKey()
    {
        return $this->apikey;
    }

    public function getPaymentUrl()
    {
        return $this->paymentUrl;
    }

    public function getRedirectUrl()
    {
        return $this->redirectUrl;
    }

    /** Initiate payment
     * Make a request to Amplify to return paymentUrl
     * @return $this
     */
    public function initiatePayment()
    {
        $url = '/merchant/transact';
        $data = [
            'merchantId' => $this->getMerchantId(),
            'apiKey' => $this->getApiKey(),
            'transID' => $this->generateTransId(),
            'customerEmail' => request()->email,
            'customerName' => request()->name,
            'Amount' => request()->amount,
            'redirectUrl' => $this->getRedirectUrl(),
            'paymentDescription' => request()->description,
            'planId' => request()->planId
        ];

        array_filter($data);
        $this->response = HttpUtilityService::makePostRequest($url, $data);

        return $this;
    }

    /** Get paymentURl from Amplify
     * Makes a request to Amplify initiate payment Request
     * @return $this
     */
    public function getAuthorizationUrl()
    {
        $this->response = $this->initiatePayment();
        $this->paymentUrl = $this->response['PaymentUrl'];

        return $this;
    }

    /**Redirect to the paymentUrl
     * @return mixed
     */
    public function redirect()
    {
        return redirect($this->paymentUrl);
    }

    /**
     * Verify the transaction
     */
    public function transactionIsVerified()
    {
        $url = '/merchant/verify';
        $data = ['transactionRef' => request()->tran_response, 'merchantId' => request()->merchantId];

        $this->response = HttpUtilityService::makeGetRequest($data, $url);
    }

    /**Handle payment Callback
     * @return mixed
     * @throws Exception
     */
    public function handlePaymentCallback()
    {
        if ($this->transactionIsVerified()) return $this->response;

        throw new Exception("Transaction was not verified successfully");

    }

}
