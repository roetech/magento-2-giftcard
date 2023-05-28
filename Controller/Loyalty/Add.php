<?php 
namespace RoeTech\GiftCard\Controller\Loyalty;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory;
use \Magento\Checkout\Model\Session;

class Add extends Action
{
  protected $adapterFactory;
  protected $resultPageFactory;
  protected $messageManager;
  
  public function __construct(
    Context $context, 
    \RoeTech\GiftCard\Model\Service\AdapterFactory $adapterFactory,
    \Magento\Framework\Message\ManagerInterface $messageManager,
    ResultFactory $resultPageFactory, 
    Session $checkoutSession
  )
  {
    $this->adapterFactory = $adapterFactory;
    $this->messageManager = $messageManager;
    $this->resultPageFactory = $resultPageFactory;
    $this->checkoutSession = $checkoutSession;
    parent::__construct($context);
  }
  
  public function execute()
  {

    $AdapterModel = $this->adapterFactory->create();

    $post = (array) $this->getRequest()->getPost();

      if (!empty($post)) {
        
        $loyaltycode = $post['loyalty_code'];

        $couponCode = $AdapterModel->getLoyaltyCodeFromService($loyaltycode);

        if ($couponCode !== false) {
          $this->checkoutSession->getQuote()->setCouponCode($couponCode)->collectTotals()->save();
        } else {
          $this->messageManager->addError(__("Cannot apply loyalty card."));
        }

        

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl('/shop/checkout/cart/');

        return $resultRedirect;
      }
  }   
}
