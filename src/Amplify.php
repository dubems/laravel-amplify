<?php

/**
 * @author: Nriagu Dubem <nriagudubem@gmail.com>
 * Date: 11/4/17
 * Time: 11:28 PM
 */
namespace Dubems\Amplify;

use Exception;
use Illuminate\Support\Facades\Config;

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

    /**
     *
     */
    public function __construct()
    {
        $this->setAPIKey();
        $this->setMerchantId();
        $this->setRedirectUrl();
    }

    /**
     *
     */
    public function setAPIKey()
    {
        $this->apikey = Config::get('amplify.apiKey');
    }

    /**
     *
     */
    public function setMerchantId()
    {
        $this->merchantId = Config::get('amplify.merchantId');
    }

    /**
     * Generate Transaction ID for each transaction
     */
    public function generateTransId()
    {
        $numberPool = range(0, 9);
        $alphaPool = range('a', 'z');
        $upperCaseAlphaPool = range('A', 'Z');
        $arrayPool = array_merge($numberPool, $alphaPool, $upperCaseAlphaPool);
        shuffle($arrayPool);
        $result = array_slice($arrayPool, 0, 16);
        $tranxId = implode('', $result);

        return $tranxId;
    }

    /** Get paymentURl from Amplify
     *
     * Makes a request to Amplify initiate payment Request
     * @return $this
     * @throws Exception
     */
    public function getAuthorizationUrl()
    {
        $this->initiatePayment();
        if (is_array($this->response) && array_has($this->response, 'PaymentUrl')) {
            $this->paymentUrl = $this->response['PaymentUrl'];
            return $this;

        } else {
            throw new Exception($this->response);
        }
    }

    /** Initiate payment
     *
     * Make a request to Amplify to return paymentUrl
     * @return $this
     */
    public function initiatePayment()
    {
        $uri = '/merchant/transact';
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
        $this->response = HttpUtilityService::makePostRequest($uri, $data);

        return $this;
    }

    /**
     * Return redirect url
     */
    public function getRedirectUrl()
    {
        return $this->redirectUrl;
    }

    public function setRedirectUrl()
    {
        $this->redirectUrl = Config::get('amplify.redirectUrl');
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

    /**Verify the Transaction
     */
    public function transactionIsVerified()
    {
        $uri = '/merchant/verify';
        $data = ['transactionRef' => request()->tran_response, 'merchantId' => request()->merchantId];
        $this->response = HttpUtilityService::makeGetRequest($data, $uri);

        if (is_array($this->response) && array_has($this->response, "StatusDesc")) {
            return $this->response["StatusDesc"] == 'Approved' ? true : false;

        } else {
            throw new Exception($this->response);
        }
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
        $uri = '/merchant/returning/charge';

        $payload = [
            'merchantId' => $this->merchantId,
            'apiKey' => $this->apikey,
            'transactionRef' => $data["transactionRef"],
            'authCode' => $data["authCode"],
            'Amount' => $data["amount"],
            'paymentDescription' => $data["paymentDescription"],
            'customerEmail' => $data["customerEmail"]
        ];

        return HttpUtilityService::makePostRequest($uri, $payload);
    }

    /** Create Subscription
     *
     * @param $data
     * $data[planName=>plan,frequency=>frequency]
     * @return mixed|string
     */
    public function createSubscription(array $data)
    {
        if ($this->validateFrequency($data)) {
            $uri = '/merchant/plan';
            $payload = [
                'merchantId' => $this->merchantId,
                'apiKey' => $this->apikey,
                'planName' => $data["planName"],
                'frequency' => $data["frequency"]

            ];

            $this->response = HttpUtilityService::makePostRequest($uri, $payload);
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
     * @param array $data : ['planName'=>XYZ,'frequency'=>'Weekly']
     * @return mixed|null|string
     * @throws Exception
     */
    public function updateSubscription($planId, array $data)
    {
        if ($this->validateFrequency($data)) {
            $uri = '/merchant/plan';
            $queryParams = ['PlanId' => $planId];

            $payload = [
                'merchantId' => $this->merchantId,
                'apiKey' => $this->apikey,
                'planName' => $data["planName"],
                'frequency' => $data["frequency"]

            ];

            $this->response = HttpUtilityService::makePutRequest($uri, $queryParams, $payload);
        }

        return $this->response ? $this->response : null;
    }


    /** Unsubscribe a customer from a plan
     *
     * @param array $data : ['transactionRef'=>2123ss,'customerEmail'=>'nriagudubem@gmail.com','planId'=>1234]
     * @return mixed|string
     */
    public function unsubscribeCustomer(array $data)
    {
        $uri = '/merchant/subscription/cancel';
        $payload = [
            'merchantId' => $this->merchantId,
            'apiKey' => $this->apikey,
            'transactionRef' => $data['transactionRef'],
            'customerEmail' => $data['customerEmail'],
            'planId' => $data['planId']
        ];

        return HttpUtilityService::makePostRequest($uri, $payload);
    }

    /**Fetch Subscription
     *
     * @param $id
     * @return mixed|string
     * @throws Exception
     */
    public function fetchSubscription($id)
    {
        $uri = '/merchant/plan';
        if ($id) {
            $data = ['planId' => $id];
            return HttpUtilityService::makeGetRequest($data, $uri);
        }

        throw new Exception('Kindly provide the subscription id');
    }

    /**Fetch all subscription
     *
     * @return mixed|string
     */
    public function fetchAllSubscription()
    {
        $uri = '/merchant/plan';
        $payload = [
            'merchantId' => $this->merchantId,
            'apiKey' => $this->apikey
        ];

        return HttpUtilityService::makeGetRequest($payload, $uri);
    }

    /**
     * Delete Subscription
     * @param $planId
     * @return mixed|string
     * @throws Exception
     */
    public function deleteSubscription($planId)
    {
        $uri = '/merchant/plan';
        if($planId){
            $params = ['planId' => $planId,
                'merchantId' => $this->merchantId,
                'apiKey' => $this->apikey
            ];

            return HttpUtilityService::makeDeleteRequest($uri,$params);
        }

        throw new Exception('Kindly provide the planId');
    } 

}
