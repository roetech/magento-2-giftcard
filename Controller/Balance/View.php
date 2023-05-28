<?php 
namespace RoeTech\GiftCard\Controller\Balance;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\Page;

class View extends Action
{
    protected $resultFactory;
    protected $page;
    
    public function __construct(
      Context $context,
      ResultFactory $resultFactory,
      Page $page
    )
    {
      $this->resultFactory = $resultFactory;
      $this->page = $page;
      parent::__construct($context);
    }
    
    public function execute()
    {

      $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
      $resultPage->getConfig()->getTitle()->set('check your gift card balance');

      $breadcrumbs = $resultPage->getLayout()->getBlock('breadcrumbs');
      $breadcrumbs->addCrumb('home', [
          'label' => __('Home'),
          'title' => __('Home'),
          'link' => $this->_url->getUrl('')
              ]
      );
      $breadcrumbs->addCrumb('giftcard', [
          'label' => __('Gift Card Balance'),
          'title' => __('Gift Card Balance')
              ]
      );

      return $resultPage;
    }   
}
