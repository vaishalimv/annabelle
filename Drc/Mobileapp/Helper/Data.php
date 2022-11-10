<?php
namespace Drc\Mobileapp\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Customer\Model\Session;
use Magento\CatalogRule\Model\ResourceModel\Rule as RuleResourceModel; 
use Magento\CatalogRule\Api\CatalogRuleRepositoryInterface;
use Amasty\GiftCardAccount\Model\GiftCardAccount\Repository;
use Amasty\GiftCardAccount\Model\GiftCardAccountFormatter;

/**
 * Class Data
 * @package Drc\CheckDelivery\Helper
 */
class Data extends AbstractHelper
{

    protected $_storeManager;

    protected $_customerSession;

    protected $_datetimezone;

    private $ruleResourceModel; 

    private $catalogRuleRepository; 

    /**
     * @var GiftCardAccountFormatter
     */
    private $accountFormatter;

    /**
     * @var Repository
     */
    private $accountRepository;


    /**
     * Helper construct.
     * @param Context                                              $context
     * @param \Magento\Store\Model\StoreManagerInterface           $storeManager
     * @param Session                                              $customerSession
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $datetimezone
     * @param RuleResourceModel                                    $ruleResourceModel
     * @param CatalogRuleRepositoryInterface                       $catalogRuleRepository
     * @param GiftCardAccountFormatter                             $accountFormatter
     * @param Repository                                           $accountRepository
     */
    public function __construct(
        Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Session $customerSession,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $datetimezone,
        RuleResourceModel $ruleResourceModel,
        CatalogRuleRepositoryInterface $catalogRuleRepository,
        GiftCardAccountFormatter $accountFormatter,
        Repository $accountRepository
    ) {
        $this->_storeManager = $storeManager;
        $this->_customerSession = $customerSession;
        $this->_datetimezone = $datetimezone;
        $this->ruleResourceModel = $ruleResourceModel;
        $this->catalogRuleRepository = $catalogRuleRepository;
        $this->accountFormatter = $accountFormatter;
        $this->accountRepository = $accountRepository;
        parent::__construct($context);
    }


    protected function getRulesFromProduct($product)
    {
        $productId = $product->getId();
        $storeId = $product->getStoreId();
        $websiteId = $this->_storeManager->getStore($storeId)->getWebsiteId();
        if ($product->hasCustomerGroupId()) {
            $customerGroupId = $product->getCustomerGroupId();
        } else {
            $customerGroupId = $this->_customerSession->getCustomerGroupId();
        }
        $dateTs = $this->_datetimezone->scopeTimeStamp();

        $ruleData = $this->ruleResourceModel->getRulesFromProduct($dateTs, $websiteId, $customerGroupId, $productId);
        $applied_rules = array();
        if(isset($ruleData) && !empty($ruleData)){
            foreach ($ruleData as $rule) {
                $rule = $this->catalogRuleRepository->get($rule['rule_id']);
                $applied_rules[] = $rule->getName();
            }
        }
        return implode(',',$applied_rules);
    }

    /**
     * Get cards front
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCardsFront($customerId): array
    {
        $cards = $this->accountRepository->getAccountsByCustomerId($customerId);
        $preparedCards = [];

        foreach ($cards as $card) {
            if($card->getStatus() == \Amasty\GiftCardAccount\Model\OptionSource\AccountStatus::STATUS_ACTIVE) {
                $preparedCards[] = $this->accountFormatter->getFormattedData($card);
            }
        }

        return $preparedCards;
    }
}
