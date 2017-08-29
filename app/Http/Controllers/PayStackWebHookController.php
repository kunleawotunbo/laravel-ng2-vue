<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Config;
use App\Quote;


class PayStackWebHookController extends Controller {

    /**
     * Transaction Verification Successful
     */
    const VS = 'Verification successful';
    /**
     *  Invalid Transaction reference
     */
    const ITF = "Invalid transaction reference";
    /**
     * Issue Secret Key from your Paystack Dashboard
     * @var string
     */
    protected $secretKey;
    /**
     * Instance of Client
     * @var Client
     */
    protected $client;
    /**
     *  Response from requests made to Paystack
     * @var mixed
     */
    protected $response;
    /**
     * Paystack API base Url
     * @var string
     */
    protected $baseUrl;

    /**
     * Authorization Url - Paystack payment page
     * @var string
     */
    protected $authorizationUrl;
    public function __construct()
    {
        $this->setKey();
        $this->setBaseUrl();
        $this->setRequestOptions();
    }
    /**
     * Get Base Url from Paystack config file
     */
    public function setBaseUrl()
    {
        //$this->baseUrl = Config::get('paystack.paymentUrl');
        $this->baseUrl = 'https://api.paystack.co';
    }

     /**
     * Get secret key from Paystack config file
     */
    public function setKey()
    {
        //$this->secretKey = Config::get('paystack.secretKey');
        $this->secretKey = 'sk_test_5ff11aa3c82e782c82054f11d5a19214fef3acc4';
    }

    /**
     * Set options for making the Client request
    */
    private function setRequestOptions()
    {
        $authBearer = 'Bearer '. $this->secretKey;
        $this->client = new Client(
            [
                'base_uri' => $this->baseUrl,
                'headers' => [
                    'Authorization' => $authBearer,
                    'Content-Type'  => 'application/json',
                    'Accept'        => 'application/json'
                ]
            ]
        );
    }

    /**
     * Hit Paystack Gateway to Verify that the transaction is valid
     */
    private function verifyTransactionAtGateway()
    {
        $transactionRef = request()->query('trxref');
        $relativeUrl = "/transaction/verify/{$transactionRef}";
        $this->response = $this->client->get($this->baseUrl . $relativeUrl, []);
    }

    /*
        PayStack webhook to listen to events
    */
    public function handlePaymentHook(Request $request)
    {
         $eventObject = "";
         $payStackheader = "";
         $quote = new Quote();
        // $quote->content = $request;
        // Check if web hook event is from PayStack

       // $payStackheader = $request->headers->all();
        //$payStackheader->save();

        $quote->content = $request->getContent();
        $quote->save();

        $response = [
            'event' => $quote
        ];

        return response()->json($response, 200);
    }

    public function verifyTransaction(Request $request, $transactionRef)
    {
        //$transactionRef = request()->query('trxref');
        $relativeUrl = "/transaction/verify/{$transactionRef}";
        $this->response = $this->client->get($this->baseUrl . $relativeUrl, []);

         $quote = new Quote();
         $quote->content = $this->response->getBody()->getContents();
        $quote->save();

        return response()->json($this->response->getBody()->getContents(), 200);

        /*
        $client = new Client();
        $secretKey = 'sk_test_5ff11aa3c82e782c82054f11d5a19214fef3acc4';
        $request->headers->set('Authorization', 'Bearer ' .$secretKey);
        $request->headers->set('Content-Type', 'application/json');
        $res = $client->request('POST', 'https://api.paystack.co/transaction/verify/' .$reference, [
            'form_params' => [
                'client_id' => 'test_id',
                'secret' => 'test_secret',
            ]
        ]);

        $result= $res->getBody();
        dd($result);

        */

    }
}