<?php
namespace RoeTech\GiftCard\Block;
 
use Magento\Framework\View\Element\Template;
 
class Balance extends Template {

    protected $adapterFactory;
    protected $messageManager;
    
    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Backend\Block\Template\Context $context,
        \RoeTech\GiftCard\Model\Service\AdapterFactory $adapterFactory,
        array $data = []
    ) {
        $this->messageManager = $messageManager;
        $this->adapterFactory = $adapterFactory;
        parent::__construct($context, $data);
    }



    public function getGiftCardBalance() {
        $AdapterModel = $this->adapterFactory->create();

    	$post = (array) $this->getRequest()->getPost(); 
    	      
        if (!empty($post)) {
        	$giftCardCode = $post['giftcard_code'];
        	$giftCardPin  = $post['giftcard_pin'];

        	$giftCardBalance = $AdapterModel->getGiftcardBalanceFromService($giftCardCode, $giftCardPin);
        	if(!$giftCardBalance) {
                $this->messageManager->addError(__("Wrong gift card code or pin entered"));
        	}
        	return $giftCardBalance;
        }
		
        return false;
    }
}