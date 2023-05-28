<?php
declare(strict_types=1);

namespace RoeTech\GiftCard\Model;

use Amasty\GiftCardAccount\Api\Data\GiftCardAccountResponseInterface;
use Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface;
use Amasty\GiftCardAccount\Model\GiftCardAccount\Repository;
use Amasty\GiftCardAccount\Model\GiftCardAccount\Account;
use Amasty\GiftCard\Model\Code\CodeGenerator;
use Amasty\GiftCard\Model\Code\CodeGeneratorManagement;
use Amasty\GiftCardAccount\Model\GiftCardAccount\GiftCardAccountManagement;
use Amasty\GiftCardAccount\Model\GiftCardAccount\GiftCardCartProcessor;
use RoeTech\GiftCard\Model\Service\Adapter;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Amasty\GiftCardAccount\Model\GiftCardAccount\CartAction\Response\Builder;


class GiftCardPlugin extends \Amasty\GiftCardAccount\Model\GiftCardAccount\GiftCardAccountManagement
{
	private $accountRepository;
	private $account;
	private $codeGenerator;
	private $codeGeneratorManagement;
	private $adapter;
	private $_logger;
	private $quoteRepository;
	private $gCardCartProcessor;
	private $responseBuilder;

  public function __construct
  (
  	Repository $accountRepository,
  	Account $account,
  	CodeGenerator $codeGenerator,
  	CodeGeneratorManagement $codeGeneratorManagement,
  	Adapter $adapter,
  	CartRepositoryInterface $quoteRepository,
  	GiftCardCartProcessor $gCardCartProcessor,
  	Builder $responseBuilder
  )
  {
  	$this->accountRepository = $accountRepository;
  	$this->account = $account;
  	$this->codeGenerator = $codeGenerator;
  	$this->codeGeneratorManagement = $codeGeneratorManagement;
  	$this->adapter = $adapter;
  	$this->quoteRepository = $quoteRepository;
  	$this->gCardCartProcessor = $gCardCartProcessor;
  	$this->responseBuilder = $responseBuilder;
  }
  	public function removeGiftCardFromCart($cartId, string $giftCardCode): string
    {
        /** @var  \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);

        if (!$quote->getItemsCount()) {
            throw new CouldNotDeleteException(__('The "%1" Cart doesn\'t contain products.', $cartId));
        }

        try {
            $giftCard = $this->accountRepository->getByCode($giftCardCode);
            $this->gCardCartProcessor->removeFromCart($giftCard, $quote);
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__("The gift card couldn't be deleted from the quote."));
        }

        return $giftCardCode;
    }
  	public function applyGiftCardAccountToCart(
        $cartId,
        string $giftCardCode
    ): GiftCardAccountResponseInterface {
		$giftCardCode = trim($giftCardCode);
        
        /** @var  \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->getActive($cartId);

        if (!$quote->getItemsCount()) {
            throw new CouldNotSaveException(__('The "%1" Cart doesn\'t contain products.', $cartId));
        }

        try {
            $giftCard = $this->accountRepository->getByCode($giftCardCode);
            $this->gCardCartProcessor->applyToCart($giftCard, $quote);
        } catch (\Exception $e) {
        	$poolID = 1;
        	$qty = 1;
			$template = 'TEST-{D}{D}{D}{D}';
			$ourCode = explode('-xxxx-', $giftCardCode);
			$giftcardNumber = $ourCode[0];
			$giftcardPin = $ourCode[1];

			$giftCardBalance =(float)$this->adapter->getGiftcardBalanceFromService($giftcardNumber, $giftcardPin);

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
				$giftCard = $this->accountRepository->getByCode($giftCardCode);
            	$this->gCardCartProcessor->applyToCart($giftCard, $quote);
        }

        return $this->responseBuilder->build($giftCard, Builder::ADD_TO_CART);
    }

	public function applyGiftCardToCart($cartId, string $giftCardCode): string
	{
		$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/test.log');
		$logger = new \Zend\Log\Logger();
		$logger->addWriter($writer);
		$logger->info('Plugin called');

				
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
				$logger->info('Gift card account already exists. Updating account');

				$model = $this->accountRepository->getByCode($giftCardCode)->setStatus(1)->setCurrentValue($giftCardBalance);
				$this->accountRepository->save($model);            

			} catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
				$logger->info('Gift card account does not exists. Creating');

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