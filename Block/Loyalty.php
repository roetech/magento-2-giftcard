<?php
namespace RoeTech\GiftCard\Block;
 
use Magento\Framework\View\Element\Template;
 
class Loyalty extends Template {

    protected $adapterFactory;
    protected $messageManager;
    
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }


    public function getFormAction() { 
        return "/shop/giftcard/loyalty/add";
    }
}