<?php

namespace RoeTech\GiftCard\Observer;

use Magento\Sales\Api\Data\OrderInterface;
use RoeTech\GiftCard\Model\Service\Adapter;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class RoeTechProcessOrder implements ObserverInterface 
{
	private $adapter;

	public function __construct(
		Adapter $adapter		
	)
	{
		$this->adapter = $adapter;
	}

	public function execute(Observer $observer)
	{
				
		$order = $observer->getEvent()->getOrder();
		$address = $observer->getEvent()->getAddress();

		if (!$address) {
			$quote = $observer->getEvent()->getQuote();
			$address = $quote->isVirtual() ? $quote->getBillingAddress() : $quote->getShippingAddress();
		}
    
		$amount = $address->getAmGiftCardsAmount() ?: 0;

		if($amount > 0)
		{
			$giftCards = $address->getAmGiftCards();
		  
			foreach ($giftCards as $giftCard) {
				$giftcardCode = $giftCard['code'];
				$giftcardCode = explode('-xxxx-', $giftcardCode);

				$giftcardNumber = $giftcardCode[0];
				$giftcardPin    = $giftcardCode[1];
				$giftcardReference = $order->getIncrementId();
				$giftcardBaseAmount = $giftCard['b_amount']; 

				$response = $this->adapter->addUsageBeforeOrderPayment($giftcardNumber, $giftcardPin, $giftcardReference, $giftcardBaseAmount);
				if($response == 'true') {
					//$logger->info('GiftCard Update sucessfull');
				} else {
					//$logger->info('GiftCard response' . $response);
				}
			}
		} 
		
	}
}