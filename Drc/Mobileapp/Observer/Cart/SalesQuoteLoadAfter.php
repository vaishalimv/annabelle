<?php
namespace Drc\Mobileapp\Observer\Cart;
use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Api\ProductRepositoryInterfaceFactory as ProductRepository;
use Magento\Store\Model\StoreManagerInterface as StoreManager;
use Magento\Store\Model\App\Emulation as AppEmulation;
use Magento\Quote\Api\Data\CartItemExtensionFactory;

use Magento\CatalogRule\Model\ResourceModel\Rule as RuleResourceModel;
use Magento\Customer\Model\Session;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\CatalogRule\Api\CatalogRuleRepositoryInterface;

class SalesQuoteLoadAfter implements ObserverInterface
{   
    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     *@var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     *@var \Magento\Store\Model\App\Emulation
     */
    protected $appEmulation;

    /**
     * @var CartItemExtensionFactory
     */
    protected $extensionFactory;

    private $ruleResourceModel;

    protected $_customerSession;

    protected $date;

    private $timezone;

    private $catalogRuleRepository;

    protected $priceHelper;

    protected $_currency;

    protected $_storeConfig;

    protected $quoteRepository;

    protected $stockState;
    /**
     * @param ProductRepository $productRepository
     * @param \Magento\Catalog\Helper\ImageFactory
     * @param \Magento\Store\Model\StoreManagerInterface
     * @param \Magento\Store\Model\App\Emulation
     * @param CartItemExtensionFactory $extensionFactory
     */
    public function __construct(
        ProductRepository $productRepository,
        StoreManager $storeManager,
        AppEmulation $appEmulation,
        CartItemExtensionFactory $extensionFactory,
        RuleResourceModel $ruleResourceModel,
        CatalogRuleRepositoryInterface $catalogRuleRepository,
        Session $customerSession,
        TimezoneInterface $timezone,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Magento\Directory\Model\Currency $currency,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\CatalogInventory\Api\StockStateInterface $stockState
    ) {
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        $this->appEmulation = $appEmulation;
        $this->extensionFactory = $extensionFactory;
        $this->ruleResourceModel = $ruleResourceModel;
        $this->catalogRuleRepository = $catalogRuleRepository;
        $this->_customerSession = $customerSession;
        $this->timezone = $timezone;
        $this->priceHelper  = $priceHelper;
        $this->_currency = $currency;
        $this->_storeConfig = $scopeConfig;
        $this->quoteRepository = $quoteRepository;
        $this->stockState = $stockState;

    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $quote = $observer->getQuote();
        $store = $this->storeManager->getStore(); 
        
        $itemOffers = $this->getCategoryItemCount($quote->getAllItems());
        $quoteItemOffer = $this->getQuoteItemCount($quote->getAllItems());

        if(!empty($quoteItemOffer)) {
            $multiBuyCartOffer = $this->getAddMoreItemLabel($quoteItemOffer);
            $extension = $quote->getExtensionAttributes();
            $extension->setMultiBuyOffers($multiBuyCartOffer);
            $quote->setExtensionAttributes($extension);
        }

       /**
         * Code to add the items attribute to extension_attributes
         * 
         */
        foreach ($quote->getAllItems() as $item) {
           	$extensionAttributes = $item->getExtensionAttributes();
            if ($extensionAttributes === null) {
                $extensionAttributes = $this->cartItemExtension->create();
            }
            $productData = $this->productRepository->create()->get($item->getSku());
            
            $productsize='';
            if($productData->getSize()){
                $productsize = $productData->getResource()->getAttribute('size')->getFrontend()->getValue($productData);
            }
            $StockState = $this->stockState;
            $short_description = ($productData->getShortDescription()) ? $productData->getShortDescription() : '' ;
            if($productData->getThumbnail() != ''){
                $productImageUrl = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' .$productData->getThumbnail(); 
            }else{
                $productImageUrl= $this->getPlaceHolderImage();
            }

            $rules = $this->getRulesFromProduct($item->getProduct());

            $extensionAttributes->setProductId($item->getProduct()->getId());
            $extensionAttributes->setImage($productImageUrl);

            if ($item->getProduct()->getTypeId() == 'configurable') {
                  $basePrice = $item->getProduct()->getPriceInfo()->getPrice('regular_price');
                  $regularPrice = $this->_currency->format($basePrice->getMinRegularAmount()->getValue(), ['display'=>\Zend_Currency::NO_SYMBOL], false);

            }else{
                $regularPrice = $this->_currency->format($item->getProduct()->getPrice(), ['display'=>\Zend_Currency::NO_SYMBOL], false);
            }
            $extensionAttributes->setPrice($regularPrice);

            $specialPrice = ($this->_currency->format($this->priceHelper->currency($item->getProduct()->getPrice(), false, false), ['display'=>\Zend_Currency::NO_SYMBOL], false));

            $extensionAttributes->setSpecialPrice($specialPrice);

            $extensionAttributes->setFinalPrice($this->_currency->format($this->priceHelper->currency($item->getProduct()->getFinalPrice(), false, false), ['display'=>\Zend_Currency::NO_SYMBOL], false));            
            $extensionAttributes->setDiscountRule($rules);
            $extensionAttributes->setSize(__($productsize));
            $extensionAttributes->setShortDescription($short_description);
            $extensionAttributes->setCurrencyCode(__($store->getCurrentCurrency()->getCode()));
            
            $extensionAttributes->setMultiBuyLabel($this->isItemEligibleForOffer($itemOffers, $item));            
            
            $extensionAttributes->setAvaibility(__($StockState->getStockQty($productData->getId())));

            $item->setExtensionAttributes($extensionAttributes);
        }
        return;
    }

    protected function getRulesFromProduct($product)
    {
        $productId = $product->getId();
        $storeId = $product->getStoreId();
        $websiteId = $this->storeManager->getStore($storeId)->getWebsiteId();
        if ($product->hasCustomerGroupId()) {
            $customerGroupId = $product->getCustomerGroupId();
        } else {
            $customerGroupId = $this->_customerSession->getCustomerGroupId();
        }
        $dateTs = $this->timezone->scopeTimeStamp();

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
    public function getPlaceHolderImage(){
        $mediaBaseUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA );
        $placeholderPath = $this->_storeConfig->getValue('catalog/placeholder/image_placeholder');//Base Image
        $fullUrl = $mediaBaseUrl.'catalog/product/placeholder/'.$placeholderPath;
        return $fullUrl;
    }

    /**
     * Get quote item count.
     *
     * @param $items
     * @return array|int[]
     */
    public function getQuoteItemCount($items)
    {
        $i = $j = $k = $l = 0;
        
        if(count($items) > 0) {

            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $multiBuyHelper = $objectManager->create(\Fledge\MultiBuy\Helper\Data::class);

            $defaultCategory1 = \Fledge\MultiBuy\Helper\Data::CATEGORY_ID_FOR_RULE1;
            $defaultCategory2 = \Fledge\MultiBuy\Helper\Data::CATEGORY_ID_FOR_RULE2;
            $defaultCategory3 = \Fledge\MultiBuy\Helper\Data::CATEGORY_ID_FOR_RULE3;
            $defaultCategory4 = \Fledge\MultiBuy\Helper\Data::CATEGORY_ID_FOR_RULE4;
            $kuwaitCategory1 = \Fledge\MultiBuy\Helper\Data::KUWAIT_CATEGORY_ID_FOR_RULE1;
            $kuwaitCategory2 = \Fledge\MultiBuy\Helper\Data::KUWAIT_CATEGORY_ID_FOR_RULE2;

            foreach ($items as $item) {

                if($item->getParentItemId()) {
                    continue;
                }

                $product = $multiBuyHelper->getProductById($item->getProductId());
                if($product && $product->getId()) {
                    $categoryIds = (!empty($product->getCategoryIds())) ? $product->getCategoryIds(): [$product->getCategoryId()];
                    if($multiBuyHelper->isKuwaitWebsite()) {
                        if(in_array($kuwaitCategory1, $categoryIds)) {
                            $i = $i + (int)$item->getQty();
                        }
                        if(in_array($kuwaitCategory2, $categoryIds)) {
                            $j = $j + (int)$item->getQty();
                        }
                    } else {
                        if(in_array($defaultCategory1, $categoryIds)) {
                            $i = $i + (int)$item->getQty();
                        }
                        if(in_array($defaultCategory2, $categoryIds)) {
                            $j = $j + (int)$item->getQty();
                        }
                        if(in_array($defaultCategory3, $categoryIds)) {
                            $k = $k + (int)$item->getQty();
                        }
                        if(in_array($defaultCategory4, $categoryIds)) {
                            $l = $l + (int)$item->getQty();
                        }
                    }
                }
            }
        }

        return ['offer1' => $i, 'offer2' => $j, 'offer3' => $k, 'offer4' => $l];
    }

    /**
     * Get category item count.
     *
     * @param $items
     * @return array
     */
    public function getCategoryItemCount($items)
    {
        $offer1 = $offer2 = $offer3 = $offer4 = [];

        if(count($items) > 0) {

            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $multiBuyHelper = $objectManager->create(\Fledge\MultiBuy\Helper\Data::class);

            $defaultCategory1 = \Fledge\MultiBuy\Helper\Data::CATEGORY_ID_FOR_RULE1;
            $defaultCategory2 = \Fledge\MultiBuy\Helper\Data::CATEGORY_ID_FOR_RULE2;
            $defaultCategory3 = \Fledge\MultiBuy\Helper\Data::CATEGORY_ID_FOR_RULE3;
            $defaultCategory4 = \Fledge\MultiBuy\Helper\Data::CATEGORY_ID_FOR_RULE4;
            $kuwaitCategory1 = \Fledge\MultiBuy\Helper\Data::KUWAIT_CATEGORY_ID_FOR_RULE1;
            $kuwaitCategory2 = \Fledge\MultiBuy\Helper\Data::KUWAIT_CATEGORY_ID_FOR_RULE2;

            foreach ($items as $item) {

                if($item->getParentItemId()) {
                    continue;
                }

                $product = $multiBuyHelper->getProductById($item->getProductId());
                if($product && $product->getId()) {
                    $categoryIds = (!empty($product->getCategoryIds())) ? $product->getCategoryIds(): [$product->getCategoryId()];
                    if($multiBuyHelper->isKuwaitWebsite()) {
                        if(in_array($kuwaitCategory1, $categoryIds)) {
                            $offer1[] = $item->getId();
                        }
                        if(in_array($kuwaitCategory2, $categoryIds)) {
                            $offer2[] = $item->getId();
                        }
                    } else {
                        if(in_array($defaultCategory1, $categoryIds)) {
                            $offer1[] = $item->getId();
                        }
                        if(in_array($defaultCategory2, $categoryIds)) {
                            $offer2[] = $item->getId();
                        }
                        if(in_array($defaultCategory3, $categoryIds)) {
                            $offer3[] = $item->getId();
                        }
                        if(in_array($defaultCategory4, $categoryIds)) {
                            $offer4[] = $item->getId();
                        }
                    }
                }
            }
        }

        return ['offer1' => $offer1, 'offer2' => $offer2, 'offer3' => $offer3, 'offer4' => $offer4];
    }

    public function getAddMoreItemLabel($itemOffers)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $multiBuyHelper = $objectManager->create(\Fledge\MultiBuy\Helper\Data::class);
        $offerArray = [];
        if($multiBuyHelper->isModuleEnable() && !empty($itemOffers)) {

            $offerText = __('Add one more item to claim');

            if($multiBuyHelper->isKuwaitWebsite()) {
                if($itemOffers['offer1'] % 2 != 0) {
                    $offerType = __('Buy 2 @ KWD 8.250 OFFER');
                    $categoryId = \Fledge\MultiBuy\Helper\Data::KUWAIT_CATEGORY_ID_FOR_RULE1;
                    $offerArray = ['offer_text' => $offerText, 'offer_type' => $offerType, 'category_id' => $categoryId];
                }

                if($itemOffers['offer2'] % 2 != 0) {
                    $offerType = __('Buy 2 @ KWD 10.750 OFFER');
                    $categoryId = \Fledge\MultiBuy\Helper\Data::KUWAIT_CATEGORY_ID_FOR_RULE2;
                    $offerArray = ['offer_text' => $offerText, 'offer_type' => $offerType, 'category_id' => $categoryId];
                }
            } else {
                if($itemOffers['offer1'] % 2 != 0) {
                    $offerType = __('Buy 2 @ AED 99 OFFER');
                    $categoryId = \Fledge\MultiBuy\Helper\Data::CATEGORY_ID_FOR_RULE1;
                    $offerArray = ['offer_text' => $offerText, 'offer_type' => $offerType, 'category_id' => $categoryId];
                }

                if($itemOffers['offer2'] % 2 != 0) {
                    $offerType = __('Buy 2 @ AED 129 OFFER');
                    $categoryId = \Fledge\MultiBuy\Helper\Data::CATEGORY_ID_FOR_RULE2;
                    $offerArray = ['offer_text' => $offerText, 'offer_type' => $offerType, 'category_id' => $categoryId];
                }

                if($itemOffers['offer3'] % 2 != 0) {
                    $offerType = __('Buy 2 @ AED 149 OFFER');
                    $categoryId = \Fledge\MultiBuy\Helper\Data::CATEGORY_ID_FOR_RULE3;
                    $offerArray = ['offer_text' => $offerText, 'offer_type' => $offerType, 'category_id' => $categoryId];
                }

                if($itemOffers['offer4'] % 2 != 0) {
                    $offerType = __('Buy 2 @ AED 79 OFFER');
                    $categoryId = \Fledge\MultiBuy\Helper\Data::CATEGORY_ID_FOR_RULE4;
                    $offerArray = ['offer_text' => $offerText, 'offer_type' => $offerType, 'category_id' => $categoryId];
                }
            }
        }

        return $offerArray;
    }

    /**
     * Is eligible for offer.
     *
     * @param $item
     * @return string
     */
    public function isItemEligibleForOffer($itemOffers, $item)
    {
        $html = '';
        if($item && $item->getId()) {

            $itemId = $item->getId();
            $qty = (int)$item->getQty();
            $label = __('Offer Claimed');

            if(((count($itemOffers['offer1']) > 0 && count($itemOffers['offer1']) % 2 == 0) ||
                (count($itemOffers['offer2']) > 0 && count($itemOffers['offer2']) % 2 == 0) ||
                $qty >= 2) && (in_array($itemId, $itemOffers['offer1']))
            ) {
                $html .= $label;
            }

            if( ((count($itemOffers['offer2']) > 0 && count($itemOffers['offer2']) % 2 == 0) || $qty >= 2) && (in_array($itemId, $itemOffers['offer2']))) {
                $html .= $label;
            }

            if(((count($itemOffers['offer3']) > 0 && count($itemOffers['offer3']) % 2 == 0) || $qty >= 2) && (in_array($itemId, $itemOffers['offer3']))) {
                $html .= $label;
            }

            if(((count($itemOffers['offer4']) > 0 && count($itemOffers['offer4']) % 2 == 0) || $qty >= 2) && (in_array($itemId, $itemOffers['offer4']))) {
                $html .= $label;
            }

        }

        return $html;
    }
}