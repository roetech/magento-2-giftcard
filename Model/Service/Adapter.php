<?php
namespace RoeTech\GiftCard\Model\Service;

class Adapter {
	protected $client;

	// api variables
	const API_URI = '[API URL]]';
	const FETCH_BALANCE_URI = '[API GET BALLANCE URI]';
	const ADD_USAGE_URI = '[API ADD USAGE URI]';
	const FETCH_LOYALTY_BALANCE_URI = '[API FETCH LOYALTY BALANCE URI]';
	const MERCHANT_ID = '[MERCHANT ID]';

		
	public function __construct(
		\Zend\Http\Client $client
    ) {
    	$this->client = $client;
    }

	// get giftcard balance from service
    public function getGiftcardBalanceFromService($number, $pin) {
		$apiUri = 'API_URL';
        $fetchBalanceUri = 'FETCH_BALANCE_URI';
        $merchantId = 'MERCHANT_ID';
        $url = $apiUri . $fetchBalanceUri;

        try {
        	$this->client->reset();
	        $this->client->setUri($url);
	        $this->client->setMethod(\Zend\Http\Request::METHOD_POST);
	        $this->client->setParameterPost(['Type' => '[TYPE]', 'MerchantID' => $merchantId, 'Reference' => $number, 'PinCode' => $pin]);
	        
	        $this->client->send();
	        $response = $this->client->getResponse();
	            
	       	$data = $response->getBody();

	       	$doc = new \DOMDocument;
			$doc->loadXML($data);

			$status = trim($doc->getElementsByTagName('Status')->item(0)->nodeValue);
			
			if($status != "true") {
				return "";
			} else {
				return $doc->getElementsByTagName('Balance')->item(0)->nodeValue;
			}

	       	/*if(strstr($data, 'ERROR') === FALSE && $balance) {
	            return $balance;
	        }*/
	    }
	    catch (\Zend\Http\Exception\RuntimeException $runtimeException) {
	    	echo $runtimeException->getMessage();
	    }

    	return FALSE;
    }

	// add usage before order payment
    public function addUsageBeforeOrderPayment($number, $pin, $orderRef, $amount) {
    	$apiUri = 'API_URL';
        $fetchBalanceUri = 'ADD_USAGE_URI';
        $merchantId = 'MERCHANT_ID';
        $url = $apiUri . $fetchBalanceUri;


        try {
        	$this->client->reset();
	        $this->client->setUri($url);
	        $this->client->setMethod(\Zend\Http\Request::METHOD_POST);
	        $this->client->setParameterPost(
	            [
	                'Type'          => '[TYPE]',
	                'SaleReference' => $orderRef,
	                'MerchantID'    => $merchantId,
	                'Reference'     => $number,
	                'PinCode'       => $pin,
	                'UsageValue'    => $amount
	            ]
	        );
	        
	        $this->client->send();
	        $response = $this->client->getResponse();
	            
	       	$data = $response->getBody();

	       	$doc = new \DOMDocument;
			$doc->loadXML($data);

			$status = trim($doc->getElementsByTagName('Status')->item(0)->nodeValue);
			$message = trim($doc->getElementsByTagName('Message')->item(0)->nodeValue);
			
			if($status == 'true') {
				return $status;
			} else {
				return $message;
			} 

	    }
	    catch (\Zend\Http\Exception\RuntimeException $runtimeException) {
	    	echo $runtimeException->getMessage();
	    }
    }

	// get loyalty balance
    public function getLoyaltyCodeFromService($loyaltyCode) {
    	$apiUri = 'API_URL';
        $fetchBalanceUri = 'FETCH_LOYALTY_BALANCE_URI';
        $merchantId = 'MERCHANT_ID';
        $url = $apiUri . $fetchBalanceUri;

        
        try {
        	$this->client->reset();
	        $this->client->setUri($url);
	        $this->client->setMethod(\Zend\Http\Request::METHOD_POST);
	        $this->client->setParameterPost(
	            [
	            'Reference'      => $loyaltyCode,
                'PinCode'        => '',
                'MerchantID'     => $merchantId,
                'GetCustDetails' => 'false'
	            ]
	        );

	        $this->client->send();
	        $response = $this->client->getResponse();
	            
	       	$data = $response->getBody();

	       	$doc = new \DOMDocument;
			$doc->loadXML($data);

			$status = trim($doc->getElementsByTagName('Status')->item(0)->nodeValue);			

	    }
	    catch (\Zend\Http\Exception\RuntimeException $runtimeException) {
	    	echo $runtimeException->getMessage();
	    }

	    if ($status == 'true') {
	    	return  trim($doc->getElementsByTagName('DiscountCode')->item(0)->nodeValue);
	    }

	    return FALSE;
	}

}
