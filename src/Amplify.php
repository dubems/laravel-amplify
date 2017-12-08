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

    public function setAPIKey()
    {
        $this->apikey = Config::get('amplify.apiKey');
    }

    public function setMerchantId()
    {
        $this->merchantId = Config::get('amplify.merchantId');
    }

    /** Get paymentURl from Amplify
     *
     * Makes a request to Amplify initiate payment Request
     * @return $this
     */
    public function getAuthorizationUrl()
    {
        $this->response = $this->initiatePayment();
        $this->paymentUrl = $this->response['PaymentUrl'];

        return $this;
    }

    /** Initiate payment
     *
     * Make a request to Amplify to return paymentUrl
     * @return $this
     */
    public function initiatePayment()
    {
        $url = '/merchant/transact';
        $data = [
            'merchantId' => $this->merchantId,
            'apiKey' => $this->apikey,
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

    public function getRedirectUrl()
    {
        return $this->redirectUrl;
    }

    public function setRedirectUrl()
    {
        $this->redirectUrl = Config('amplify.redirectUrl');
    }

    /**Redirect to the paymentUrl
     * @return mixed
     */
    public function redirect()
    {
        return redirect($this->paymentUrl);
    }

    /**Handle payment Callback
     *
     * @return mixed
     * @throws Exception
     */
    public function handlePaymentCallback()
    {
        if ($this->transactionIsVerified()) return $this->response;

        throw new Exception("Transaction was not verified successfully");

    }

    /**
     * Verify the transaction
     */
    public function transactionIsVerified()
    {
        $url = '/merchant/verify';
        $data = ['transactionRef' => request()->tran_response, 'merchantId' => request()->merchantId];
        $this->response = HttpUtilityService::makeGetRequest($data, $url);

        return $this->response["StatusDesc"] == 'Approved' ? true : false;

    }

    /** Charge returning customers
     *
     * @param array $data
     * $data = ['transactionRef'=> 'q343sfd',
     * 'authCode' => 'w343ddd',
     * 'amount' => '400',
     * 'paymentDescription' => 'Description for payment',
     * 'customerEmail' => 'nriagudubem@gmail.com']
     * @return mixed|string
     */
    public function chargeReturningCustomer(array $data)
    {
        $url = '/merchant/returning/charge';

        $data = [
            'merchantId' => $this->merchantId,
            'apiKey' => $this->apikey,
            'transactionRef' => $data["transactionRef"],
            'authCode' => $data["authCode"],
            'Amount' => $data["amount"],
            'paymentDescription' => $data["paymentDescription"],
            'customerEmail' => $data["customerEmail"]
        ];

        return HttpUtilityService::makePostRequest($url, $data);
    }

    /** Create Subscription
     *
     * @param $data
     * @return mixed|string
     */
    public function createSubscription(array $data)
    {
        if ($this->validateFrequency($data)) {
            $url = '/merchant/plan';
            $data = [
                'merchantId' => $this->merchantId,
                'apiKey' => $this->apikey,
                'planName' => $data["planName"],
                'frequency' => $data["frequency"]

            ];

            $this->response = HttpUtilityService::makePostRequest($url, $data);
        }

        return $this->response ? $this->response : null;
    }

    /** Validate Frequency of Subscription
     *
     * @param array $data
     * @return bool
     * @throws Exception
     */
    protected function validateFrequency(array $data)
    {
        $allFrequency = ['Weekly', 'Monthly', 'Three_Months', 'Six_Months', 'Annually', 'Custom'];
        $frequency = $data["frequency"];

        if ($frequency && in_array($frequency, $allFrequency)) {

            return true;
        } else {
            throw new Exception('The supplied Subscription frequency is not valid');
        }
    }

    /**Update subscription
     *
     * @param $planId
     * @param array $data
     * @return mixed|null|string
     * @throws Exception
     */
    public function updateSubscription($planId, array $data)
    {
        if ($this->validateFrequency($data)) {
            $url = '/merchant/plan';
            $queryParams = ['PlanId' => $planId];

            $data = [
                'merchantId' => $this->merchantId,
                'apiKey' => $this->apikey,
                'planName' => $data["planName"],
                'frequency' => $data["frequency"]

            ];

            $this->response = HttpUtilityService::makePutRequest($url, $queryParams, $data);
        }

        return $this->response ? $this->response : null;
    }

}
