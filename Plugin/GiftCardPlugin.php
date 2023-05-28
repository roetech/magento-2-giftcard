<?php
declare(strict_types=1);

namespace RoeTech\GiftCard\Plugin;

use Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface;
use Amasty\GiftCardAccount\Model\GiftCardAccount\Repository;
use Amasty\GiftCardAccount\Model\GiftCardAccount\Account;
use Amasty\GiftCard\Model\Code\CodeGenerator;
use Amasty\GiftCard\Model\Code\CodeGeneratorManagement;
use Amasty\GiftCardAccount\Model\GiftCardAccount\GiftCardAccountManagement;
use RoeTech\GiftCard\Model\Service\Adapter;
use RoeTech\GiftCard\Logger\Logger;

class GiftCardPlugin
{
	private $accountRepository;
	private $account;
	private $codeGenerator;
	private $codeGeneratorManagement;
	private $adapter;
	private $_logger;

  public function __construct
  (
  	Repository $accountRepository,
  	Account $account,
  	CodeGenerator $codeGenerator,
  	CodeGeneratorManagement $codeGeneratorManagement,
  	Adapter $adapter,
  	Logger $logger
  )
  {
  	$this->accountRepository = $accountRepository;
  	$this->account = $account;
  	$this->codeGenerator = $codeGenerator;
  	$this->codeGeneratorManagement = $codeGeneratorManagement;
  	$this->adapter = $adapter;
  	$this->_logger = $logger;
  }

	public function beforeApplyGiftCardToCart(GiftCardAccountManagement $subject, $cartId, string $giftCardCode)
	{
		$this->_logger->info('Plugin called');
				
		$poolID = 1;
		$qty = 1;
		$template = 'TEST-{D}{D}{D}{D}';

		$ourCode = explode('-xxxx-', $giftCardCode);
		$giftcardNumber = $ourCode[0];
		$giftcardPin = $ourCode[1];

		$giftCardBalance = $this->adapter->getGiftcardBalanceFromService($giftcardNumber, $giftcardPin);

		if($giftCardBalance) {	    	
			
			try {
				$exists = $this->accountRepository->getByCode($giftCardCode)->getAccountId();
				$this->_logger->info('Gift card account already exists. Updating account');

				$model = $this->accountRepository->getByCode($giftCardCode)->setStatus(1)->setCurrentValue($giftCardBalance);
				$this->accountRepository->save($model);            

			} catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
				$this->_logger->info('Gift card account does not exists. Creating');

				$this->codeGeneratorManagement->generateCodesByTemplate($poolID, $template, $qty);

				$model = $this->accountRepository->getEmptyAccountModel()
					->setStatus(1)
					->setInitialValue($giftCardBalance)
					->setCurrentValue($giftCardBalance)
					->setWebsiteId(1)
					->setCodePool(1);

				$this->accountRepository->save($model);

				$code = $model->getCodeModel();

				$code->setCodeSetId(1)
					->setUsed(0)
					->setEnabled(1)
					->setCode($giftCardCode)
					->save();
			}
		}

		return null;
	}
}