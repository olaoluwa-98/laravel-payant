<?php

/*
 * This file is part of the Laravel Payant package.
 *
 * (c) Emmanuel Awotunde <awotunde.emmanuel1@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Olaoluwa98\Payant;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Config;
// use Olaoluwa98\Payant\Exceptions\ApiRequestError;
// use Olaoluwa98\Payant\Exceptions\InvalidCredentials;
use Olaoluwa98\Payant\Exceptions\InvalidFeeBearer;
// use Olaoluwa98\Payant\Exceptions\InvalidParameterType;
use Olaoluwa98\Payant\Exceptions\IsInvalid;
use Olaoluwa98\Payant\Exceptions\IsNull;
use Olaoluwa98\Payant\Exceptions\IsNullOrInvalid;
use Olaoluwa98\Payant\Exceptions\RequiredValueMissing;
use Olaoluwa98\Payant\Exceptions\RequiredValuesMissing;

use \Exception as phpException;
class Payant {
    

    /**
     * @var $private_key
    */
    protected $private_key;
    

    /**
     *
     * @var $base_uri
     *
    */
    protected $base_uri = 'https://api.demo.payant.ng';
    
    
    /**
     * @var $client
     *
    */
    protected $client;

    
    /**
     * [__construct sets the needed variables got from Payant config file]
     */
    public function __construct()
    {
        $this->setKey();
        $this->setBaseUrl();
        $this->setRequestOptions();        
    }

    /**
    * Get Base Url from Payant config file
    */
    public function setBaseUrl()
    {
        if(Config::get('payant.mode') == 'LIVE')
        {
            $this->base_uri = "https://api.payant.ng";
        }
    }

    /**
     * Get private key from Payant config file
     */
    public function setKey()
    {
        $this->private_key = Config::get('payant.private_key');
    }


    /**
     * Set options for making the Client request
     */
    private function setRequestOptions()
    {       
       $authorization_string = 'Bearer '. $this->private_key;

        //Set up Guzzle
        $this->client = new Client( [
            'base_uri' => $this->base_uri,
            'protocols' => ['https'],
            'headers' => [
                'Authorization' => $authorization_string,
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json'
            ]
        ]);
    }


    /**
     * [getStates Get States in a country (Nigeria)]
     * @return object [list of banks and their respective bank_ids]
    */
    public function getBanks(){
        return $this->sendRequest('get', '/banks');
    }
    

    
    /**
     * [resolveAccount description]
     * @param array $client_data [description]
     * Required fields - 'settlement_bank', 'account_number'
    */
    public function resolveAccount( array $client_data){
        // Mandatory fields
        $required_values = ['settlement_bank', 'account_number'];

        if(!array_keys_exist($client_data, $required_values)){
         throw new RequiredValuesMissing("Missing required values :(");
        }

        $url = '/resolve-account';

        return $this->sendRequest('post', $url, ['form_params' => $client_data]);
    }



    
    /**
     * [addClient description]
     * @param array $client_data [description]
     * Required fields - 'first_name', 'last_name', 'email', 'phone'
     * Optional - 'address', 'company_name', 'type', 'settlement_bank', 'account_number'
    */
    public function addClient( array $client_data){
        // Mandatory fields
        $required_values = ['first_name', 'last_name', 'email', 'phone'];

        if(!array_keys_exist($client_data, $required_values)){
         throw new RequiredValuesMissing("Missing required values :(");
        }

        $url = '/clients';

        return $this->sendRequest('post', $url, ['form_params' => $client_data]);
    }

    


    /**
     * [getClient Get client Details]
     * @param  string $client_id
     * @return object
    */
    public function getClient($client_id = null){
        if(!$client_id){
            throw new IsNullOrInvalid("Error Processing Request - Null/Invalid Client Id");
        }

        $url = "/clients/{$client_id}";

        return $this->sendRequest('get', $url);
    }

    



    /**
    * [editClient - Edit Existing Client]
    * @param string $client_id
    * @param array $client_data
    *        Required fields - 'first_name', 'last_name', 'email', 'phone'
    *        Optional - 'address', 'company_name', 'type', 'settlement_bank', 'account_number'
    */
    public function editClient( $client_id, array $client_data){
        if(!$client_id){
           throw new IsNullOrInvalid("Error Processing Request - Null/Invalid Client Id");
        }

        $url = "/clients/{$client_id}";

        // Mandatory fields
        $required_values = ['first_name', 'last_name', 'email', 'phone'];

        if(!array_keys_exist($client_data, $required_values)){
             throw new RequiredValuesMissing("Missing required values :(");
        }

        return $this->sendRequest('put', $url, ['form_params' => $client_data]);
    }

    



    /**
     * [deleteClient]
     * @param  string $client_id [description]
     */
    public function deleteClient($client_id = null){
        if(!$client_id){
            throw new IsNullOrInvalid("Error Processing Request - Null/Invalid Client Id");
        }

        $url = "/clients/{$client_id}";

        return $this->sendRequest('delete', $url);
    }





    /**
     * [addInvoice description]
     * @param string      $client_id   [Optional - if client_data is supplied]
     * @param array|null    $client_data [Optional - if client_id is supplied]
     *      Required Keys - 'first_name', 'last_name', 'email', 'phone'
     *      Optional - 'address', 'company_name', 'lga', 'state'                        
     * @param string      $due_date    [Mandatory, Format - DD/MM/YYYY]
     * @param string      $fee_bearer  [Mandatory]
     * @param array         $items       [Mandatory]
     */
    public function addInvoice($client_id, array $client_data, $due_date, $fee_bearer, array $items){
        // Mandatory Client fields
        $required_client_values = ['first_name', 'last_name', 'email', 'phone'];
        

        // Vaild fee_bearer types: 'account' and 'client'
        $valid_fee_bearers = ['account', 'client'];

        
        // Either the client Id is supplied or a new client data is provided
        if(!$client_id && !array_keys_exist($client_data, $required_client_values)){
            throw new RequiredValuesMissing("Missing required values :( - Provide client_id or client_data");
        }

        if(!$due_date){
            throw new IsNullOrInvalid("Error Processing Request - Null/Invalid Due Date");
        }

        if(!$fee_bearer){
            throw new IsNull("Error Processing Request - Null Fee Bearer");
        }elseif (!in_array($fee_bearer, $valid_fee_bearers)) {
            throw new InvalidFeeBearer("Invalid Fee Bearer - Use either 'account' or 'client'");
        }

        if(!is_array($items)){
            throw new IsInvalid("Error Processing Request - Invalid Items");
        }

        $url = "/invoices";

        $post_data = [
            'due_date' => $due_date,
            'fee_bearer' => $fee_bearer,
            'items' => $items
        ];

        ($client_id) ? $post_data['client_id'] = $client_id : null;
        ($client_data) ? $post_data['client'] = $client_data : null;

        return $this->sendRequest('post', $url, ['form_params' => $post_data]);
    }




    /**
    * [getInvoice ]
    * @param  string $reference_code [Mandatory - Invoice Reference Code]
    * @return object               
    */
    public function getInvoice($reference_code){
        if(!$reference_code){
            throw new IsNullOrInvalid("Error Processing Request - Null/Invalid reference_code");
        }

        $url = "/invoices/{$reference_code}";

        return $this->sendRequest('get', $url);
    }

    /**
    * [sendInvoice]
    * @param  string $reference_code [Mandatory - Invoice Reference Code]
    * @return object               
    */
    public function sendInvoice($reference_code = null){
        if(!$reference_code){
            throw new IsNullOrInvalid("Error Processing Request - Null/Invalid reference_code");
        }

            $url = "/invoices/send/{$reference_code}";

            return $this->sendRequest('get', $url);
    }





    /**
    * [getInvoiceHistory]
    * @param  string $period [Mandatory || Valid Options ["today", "week", "month", "30", "90", "year", "custom"]]
    * @param  string $start  [Format - DD/MM/YYYY]
    * @param  string $end    [Format - DD/MM/YYYY]
    * @return object         
    */
    public function getInvoiceHistory($period, $start = null, $end = null){
        if(!$period){
            throw new RequiredValueMissing("Error Processing Request - period Missing");
        }

        //Validate Period
        $valid_period_options = ["today", "week", "month", "30", "90", "year", "custom"];

        if (!in_array($period, $valid_period_options)) {
            throw new IsInvalid("Invalid Period - Available options: today, week, month, 30, 90, year or custom");
        }

        $post_data = [
            'period' => $period
        ];

        if ($period == 'custom'){
            if (!$start || !$end){
                throw new IsNull("Invalid custom Start or End date");
            }
            $post_data['start'] = $start;
            $post_data['end'] = $end;
        }

        $url = "/invoices/history";

        return $this->sendRequest('post', $url, ['form_params' => $post_data]);
    }





    /**
    * [deleteInvoice]
    * @param  string $reference_code [Mandatory - Invoice Reference Code]
    * @return object                 
    */
    public function deleteInvoice($reference_code){
        if(!$reference_code){
            throw new IsNullOrInvalid("Error Processing Request - Null/Invalid reference_code");
        }

        $url = "/invoices/{$reference_code}";

        return $this->sendRequest('delete', $url);
    }





    /**
     * [addTransfer description]
     * @param array $client_data [description]
     * Required fields - 'first_name', 'last_name', 'email', 'phone', 'settlement_bank', 'account_number',
     * Optional - 'address', 'company_name', 'type',
     * @param string      $amount    [Mandatory]
     */
    public function addTransfer(array $client_data, string $amount){
        // Mandatory Client fields
        $required_client_values = ['first_name', 'last_name', 'email', 'phone', 'settlement_bank', 'account_number'];        
        
        if(!array_keys_exist($client_data, $required_client_values)){
            throw new RequiredValuesMissing("Missing required values :( - Provide client_data");
        }

        if(!$amount){
            throw new IsNullOrInvalid("Error Processing Request - Null/Invalid amount");
        }

        $url = "/transfers";

        $post_data = [
            'client' => $client_data,
            'amount' => $amount,
            ];

        return $this->sendRequest('post', $url, ['form_params' => $post_data]);
    }





    /**
    * [getTransfer ]
    * @param  string $reference_code [Mandatory - Transfer Reference Code]
    * @return object               
    */
    public function getTransfer($reference_code){
        if(!$reference_code){
            throw new IsNullOrInvalid("Error Processing Request - Null/Invalid reference_code");
        }

        $url = "/transfers/{$reference_code}";

        return $this->sendRequest('get', $url);
    }






    /**
    * [getTransferHistory]
    * @param  string $period [Mandatory || Valid Options ["today", "week", "month", "30", "90", "year", "custom"]]
    * @param  string $start  [Format - DD/MM/YYYY]
    * @param  string $end    [Format - DD/MM/YYYY]
    * @return object         
    */
    public function getTransferHistory($period, $start = null, $end = null){
        if(!$period){
            throw new RequiredValueMissing("Error Processing Request - period Missing");
        }

        //Validate Period
        $valid_period_options = ["today", "week", "month", "30", "90", "year", "custom"];

        if (!in_array($period, $valid_period_options)) {
            throw new IsInvalid("Invalid Period - Available options: today, week, month, 30, 90, year or custom");
        }

        $post_data = [
            'period' => $period
        ];

        if ($period == 'custom'){
            if (!$start || !$end){
                throw new IsNull("Invalid custom Start or End date");
            }
            $post_data['start'] = $start;
            $post_data['end'] = $end;
        }

        $url = "/transfers/history";

        return $this->sendRequest('post', $url, ['form_params' => $post_data]);
    }





    /**
    * [deleteTransfer]
    * @param  string $reference_code [Mandatory - Invoice Reference Code]
    * @return object                 
    */
    public function deleteTransfer($reference_code){
        if(!$reference_code){
            throw new IsNullOrInvalid("Error Processing Request - Null/Invalid reference_code");
        }

        $url = "/transfers/{$reference_code}";

        return $this->sendRequest('delete', $url);
    }






    /**
    * [addPayment]
    * @param string $reference_code [Mandatory - Invoice Reference Code]
    * @param string $date           [Mandatory - [Format - DD/MM/YYYY]]
    * @param string $amount         [Mandatory]
    * @param string $channel        [Mandatory - valid ["Cash", "BankTransfer", "POS", "Cheque"]]
    */
    public function addPayment(string $reference_code, string $date, string $amount, string $channel){
        if(!$reference_code){
            throw new IsNullOrInvalid("Error Processing Request - Null/Invalid reference_code");
        }

        if(!$due_date){
            throw new IsNullOrInvalid("Error Processing Request - Null/Invalid date");
        }

        if(!$amount){
            throw new IsNullOrInvalid("Error Processing Request - Null/Invalid amount");
        }

        $valid_channels = ["Cash", "BankTransfer", "POS", "Cheque"];

        if(!$channel){
            throw new IsNull("Error Processing Request - Null/Invalid amount");
        }elseif (!in_array(ucfirst($channel), $valid_channels)) {
            throw new IsInvalid("Invalid Channel - Cash, BankTransfer, POS or Cheque");
        }

        $url = "/payments";

        $post_data = [
            'reference_code' => $reference_code,
            'date' => $date,
            'amount' => $amount,
            'channel' => $channel
        ];

        return $this->sendRequest('post', $url, ['form_params' => $post_data]);
    }





    /**
    * [getPayment]
    * @param string $reference_code [Mandatory - Invoice Reference Code]
    */
    public function getPayment($reference_code){
        if(!$reference_code){
            throw new IsNullOrInvalid("Error Processing Request - Null/Invalid reference_code");
        }

        $url = "/payments/{$reference_code}";

        return $this->sendRequest('get', $url);
    }

    



    /**
    * [getPaymentHistory]
    * @param  string $period [Mandatory || Valid Options ["today", "week", "month", "30", "90", "year", "custom"]]
    * @param  string $start  [Format - DD/MM/YYYY || Optional if $period !== 'custom']
    * @param  string $end    [Format - DD/MM/YYYY || Optional if $period !== 'custom']
    * @return object         
    */
    public function getPaymentHistory(string $period, string $start, string $end){
        if(!$period){
            throw new RequiredValueMissing("Error Processing Request - period Missing");
        }

        //Validate Period
        $valid_period_options = ["today", "week", "month", "30", "90", "year", "custom"];

        if (!in_array(strtolower($period), $valid_period_options)) {
            throw new IsInvalid("Invalid Period - Available options: today, week, month, 30, 90, year or custom");
        }

        $post_data = [
            'period' => $period
        ];

        if ($period == 'custom'){
            if (!$start || !$end){
                throw new IsNull("Invalid custom Start or End date");
            }
            $post_data['start'] = $start;
            $post_data['end'] = $end;
        }

        $url = "/payments/history";

        return $this->sendRequest('post', $url, ['form_params' => $post_data]);
    }

    



    /**
    * [addProduct]
    * @param string $name        [Mandatory - Product's name]
    * @param string $description [Mandatory - Product's description]
    * @param string $unit_cost   [Mandatory - Product's unit cost]
    * @param string $type        [Mandatory - Product type 'product' or 'service']
    */
    public function addProduct(string $name, string $description, string $unit_cost, string $type){
        if(!$name){
            throw new IsNull("Error Processing Request - Null/Invalid name");
        }

        if(!$description){
            throw new IsNull("Error Processing Request - Null/Invalid description");
        }

        if(!$unit_cost){
            throw new IsNull("Error Processing Request - Null/Invalid unit_cost");
        }

        //Validate Product Type
        $valid_product_type = ["product", "service"];

        if(!$type){
            throw new IsNull("Error Processing Request - Null/Invalid type");
        }elseif (!in_array(strtolower($type), $valid_product_type)) {
            throw new IsInvalid("Invalid Type - Available options: 'product' or 'service'");
        }

        $url = "/products";

        $post_data = [
            'name' => $name,
            'description' => $description,
            'unit_cost' => $unit_cost,
            'type' => $type
        ];

        return $this->sendRequest('post', $url, ['form_params' => $post_data]);
    }

        



    /**
    * [getProduct]
    * @param  int $product_id [Mandatory - Product ID]
    * @return object 
    */
    public function getProduct($product_id){
        if(!$product_id){
            throw new IsNullOrInvalid("Error Processing Request - Null/Invalid product_id");
        }

        $url = "/products/{$product_id}";

        return $this->sendRequest('get', $url);
    }

    



    /**
    * [editProduct]
    * @param  string $product_id   [Mandatory - Product ID]
    * @param  array  $product_data [description]
    * @return object               
    */
    public function editProduct($product_id, array $product_data){
        if(!$product_id){
               throw new IsNullOrInvalid("Error Processing Request - Null/Invalid Product Id");
        }

        //Validate Type
        $product_type = strtolower(array_get($product_data, 'type', 'none'));

        $valid_product_type = ["product", "service"];

        if(!$product_type){
            throw new IsNull("Error Processing Request - Null/Invalid type");
        }elseif (!in_array($product_type, $valid_product_type)) {
            throw new IsInvalid("Invalid Type - Available options: 'product' or 'service'");
        }

       $url = "/products/{$product_id}";

       // Mandatory fields
       $required_values = ['name', 'description', 'unit_cost', 'type'];

        if(!array_keys_exist($client_data, $required_values)){
             throw new RequiredValuesMissing("Missing required values :(");
        }

        return $this->sendRequest('put', $url, ['form_params' => $post_data]);
    }

    


    /**
    * [getProducts]
    * @return object
    */
    public function getProducts(){
        $url = "/products";

        return $this->sendRequest('get', $url);
    }

        


    /**
    * [deleteProduct]
    * @param $product_id [Mandatory - Product ID]
    * @return object           
    */
    public function deleteProduct($product_id){
        if(!$product_id){
            throw new IsNullOrInvalid("Error Processing Request - Null/Invalid Product Id");
        }

        $url = "/products/{$product_id}";

        return $this->sendRequest('delete', $url);
    }



    /**
    * [addPayment]
    * @param string $method       [Mandatory - request method <get | post | put | delete> ]
    * @param string $url           [Mandatory - url to send request to]
    * @param array $params         [data to post to request url]
    */
    public function sendRequest($method, $url, $params=[])
    {
        try{
            if (strtolower($method) == 'get'){
                $result = $this->client->request('GET', $url);
            }elseif (strtolower($method) == 'post'){
                $result = $this->client->request('POST', $url, $params);
            }elseif (strtolower($method) == 'put'){
                $result = $this->client->request('PUT', $url, $params);
            }elseif (strtolower($method) == 'delete'){
                $result = $this->client->request('DELETE', $url);
            }

            return cleanResponse($result);
        }
        catch( Exception $e){
            throw $e;
        }
    }
}
