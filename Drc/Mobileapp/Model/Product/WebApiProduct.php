<?php
namespace Drc\Mobileapp\Model\Product;
use Drc\Mobileapp\Api\Product\WebApiProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogRule\Model\ResourceModel\Rule as RuleResourceModel; 
use Magento\CatalogRule\Api\CatalogRuleRepositoryInterface;
use Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku;
use Magento\Catalog\Helper\Image;
use Magento\Framework\Pricing\PriceCurrencyInterface as CurrencyInterface;

class WebApiProduct implements WebApiProductInterface
{
	protected $_productRepository;
	
	/**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productModelFactory;
	
	/**
     * @var \Magento\Swatches\Helper\Data
     */
    protected $swatchHelper;
	
	/**
     * @var ProductRepositoryInterface
     */
    protected $productRepositoryInterface;
	
	protected $_storeManager;
		
	protected $wishlist;
	
	protected $_currency;
	
	public $priceHelper;

	protected $_datetimezone;

	private $ruleResourceModel; 

	private $catalogRuleRepository; 

	protected $_storeConfig;	
	
	protected $_brand;

	protected $_product = null;

	protected $registry;

	protected $_productCollectionFactory;

	protected $_catalogProductVisibility;

	protected $_brandHelper;

	protected $_date;

	private $getSalableQuantityDataBySku;

	protected $mgsProduct;

	protected $imageHelper;

	protected $_productloader;  

	/**
	 * @var \Fledge\MultiBuy\Helper\Data
	 */
	protected $multiBuyHelper;
	
	public function __construct(
		\Magento\Catalog\Model\ProductRepository $productRepository,
		\Magento\Catalog\Model\ProductFactory $productModelFactory,
		ProductRepositoryInterface $productRepositoryInterface,
		\Magento\Swatches\Helper\Data $swatchHelper,
	    \Magento\Store\Model\StoreManagerInterface $storeManager,
	    \Magento\Directory\Model\Currency $currency,
		\Magento\Framework\Pricing\Helper\Data $priceHelper,
	    \Magento\Wishlist\Model\Wishlist $wishlist,
		\Magento\Framework\Stdlib\DateTime\TimezoneInterface $datetimezone,
		RuleResourceModel $ruleResourceModel,
		CatalogRuleRepositoryInterface $catalogRuleRepository,	
		\MGS\Brand\Model\Brand $brand,
		\MGS\Brand\Model\Product $mgsProduct,
		\Magento\Framework\Registry $registry,
		\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,        
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\MGS\Brand\Helper\Data $brandHelper,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        GetSalableQuantityDataBySku $getSalableQuantityDataBySku,
        Image $imageHelper,
		\Magento\Catalog\Model\ProductFactory $_productloader,
		\Fledge\MultiBuy\Helper\Data $multiBuyHelper
   
	)
	{
		$this->_brandHelper = $brandHelper;
		$this->_productRepository = $productRepository;
		$this->_catalogProductVisibility = $catalogProductVisibility;
		$this->productModelFactory = $productModelFactory;
		$this->productRepositoryInterface = $productRepositoryInterface;
		$this->swatchHelper = $swatchHelper;
	    $this->_storeManager = $storeManager;
	    $this->_currency = $currency;
	    $this->wishlist = $wishlist;
		$this->priceHelper  = $priceHelper;
		$this->_datetimezone = $datetimezone;
		$this->ruleResourceModel = $ruleResourceModel;
		$this->catalogRuleRepository = $catalogRuleRepository;		
        $this->_storeConfig = $scopeConfig;
        $this->_brand = $brand;
        $this->registry = $registry;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_date = $date;
        $this->getSalableQuantityDataBySku = $getSalableQuantityDataBySku;
        $this->mgsProduct = $mgsProduct;
        $this->imageHelper = $imageHelper;
        $this->_productloader = $_productloader;
        $this->multiBuyHelper = $multiBuyHelper;
	}
	 /**
     * Return Product details.
     *
     * @param int $productId
     * @param int $customerId
     * @param mixed $height
     * @param mixed $width
     * @return mixed
     */
	public function getDetails($customer_id = '',$product_id,$height,$width){
		$baseUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'catalog/product';
		
		$product = $this->_productRepository->getById($product_id);
		if($product->getData() != array()) {
			/* for get configurable attribute option */
			$configurable_product_options = array();
			if($product->getTypeId() == 'configurable'){
				$data = $product->getTypeInstance()->getConfigurableOptions($product);
				foreach($data as $attr){
				  	foreach($attr as $key => $value){
				  		$options[$key] = $value;
				  	}
				}

				foreach($options as $option){
				  	$prod = $this->_productRepository->get($option['sku']);
				  	$prod_id = $this->_productRepository->getById($prod->getId());

				  	$attribute_id = $product->getResource()->getAttribute($option['attribute_code'])->getId(); 
			  		$option['attribute_id'] = $attribute_id;
			  		$option['price'] = $this->_currency->format($this->priceHelper->currency($prod_id->getPrice(), false, false), ['display'=>\Zend_Currency::NO_SYMBOL], false);		  		 
				  	$option['special_price'] = $this->_currency->format($this->priceHelper->currency($prod_id->getSpecialPrice(), false, false), ['display'=>\Zend_Currency::NO_SYMBOL], false);
				  	$option['final_price'] = $this->_currency->format($this->priceHelper->currency($prod_id->getFinalPrice(), false, false), ['display'=>\Zend_Currency::NO_SYMBOL], false);
					$salebleQty	= $this->getSalableQuantityDataBySku->execute($option['sku']);
				  	$option['qty'] = $salebleQty[0]['qty'];
					$specialPrice =	($prod->getSpecialPrice() != '') ? $this->_currency->format($prod->getSpecialPrice(), ['display'=>\Zend_Currency::NO_SYMBOL], false) : '';
					$product_price = $this->_currency->format($prod->getPrice(), ['display'=>\Zend_Currency::NO_SYMBOL], false);
				  	if($specialPrice < $product_price)
				    { 
				    	$_savingPercent = 100 - round(((int)$specialPrice / (int)$product_price)*100); 
				        if ($specialPrice) {
				        	$option['discount'] = $_savingPercent."% OFF";
				        }else{
				        	$option['discount'] = '';
				        }
				    }
				  	$configurable_product_options[] = $option;
				}
			}

            $description = ($product->getDescription()) ? $product->getDescription() : '' ;
            $short_description = ($product->getShortDescription()) ? $product->getShortDescription() : '' ;          
		    $_product['product_id'] = ($product->getId() != '') ? $product->getId() : '';
		    $_product['name'] = ($product->getName() != '') ? $product->getName() : '';
		    $_product['sku'] = ($product->getSku() != '') ? $product->getSku() : '';
		    $_product['type'] = ($product->getTypeId() != '') ? $product->getTypeId() : '';
		    $_product['producturl'] = ($product->getProductUrl() != '') ? $product->getProductUrl() : '';
		    if ($product->getTypeId() == 'configurable') {
			      $basePrice = $product->getPriceInfo()->getPrice('regular_price');
			      $regularPrice = $this->_currency->format($basePrice->getMinRegularAmount()->getValue(), ['display'=>\Zend_Currency::NO_SYMBOL], false);

			}else{
				$regularPrice = $this->_currency->format($product->getPrice(), ['display'=>\Zend_Currency::NO_SYMBOL], false);
			}
			$custom_special_price = $this->_currency->format($product->getFinalPrice(), ['display'=>\Zend_Currency::NO_SYMBOL], false);
			if ($regularPrice == $custom_special_price) {
				$custom_specialPrice = '';
			}else{
				$custom_specialPrice = $this->_currency->format($product->getFinalPrice(), ['display'=>\Zend_Currency::NO_SYMBOL], false);
			}

		    $_product['price'] = $regularPrice;
		    $_product['special_price'] = $custom_specialPrice;
		    $_product['final_price'] = $this->_currency->format($product->getFinalPrice(), ['display'=>\Zend_Currency::NO_SYMBOL], false);
		    if ($this->getRulesFromProduct($product) == null) {
                $discounts = '';
            }else{
                $discounts = $this->getRulesFromProduct($product);
            }
		    $_product['discount'] = $discounts;		    
		    $_product['short_description'] = $short_description;		    
		    $_product['description'] = $description;
		 	$_product['configurable_product_options'] = $configurable_product_options;
		    $_product['weight'] = ($product->getWeight() != '') ? (int)$product->getWeight() : '';
		    $_product['avaibility'] = $product->isAvailable();		    
		    $_product['currency_code'] = __($this->_storeManager->getStore()->getCurrentCurrency()->getCode());
		    
		    /* For Brand Attribute */
		    $optionValue = ($product->getMgsBrand() != '') ? $product->getMgsBrand() : '';
		    if($optionValue != ''){
		        $attribute = $product->getResource()->getAttribute('mgs_brand');
		        $mgs_brand_lable = '';
                if ($attribute->usesSource()) {
                    $mgs_brand_lable = $attribute->getSource()->getOptionText($optionValue);
                }
		         $_product['mgs_brand'] = $mgs_brand_lable;    
		    }else{
		         $_product['mgs_brand'] = '';
		    }
		    
		    /*Wishlist product check*/
		    $wishListFlag = '';
		    $wishListItemId = '';
		    $WishlistId = '';
            if($customer_id != ''){
                $wishlist_collection = $this->wishlist->loadByCustomerId($customer_id, true)->getItemCollection();
                if($wishlist_collection->getData() != array()){
                    foreach ($wishlist_collection as $item) {
                        if($product_id == $item->getProduct()->getId()){
                            $wishListFlag = 1;
                            $wishListItemId = $item->getWishlistItemId();
                            $WishlistId = $item->getWishlistId();
							break;
                        }
                    }
                }
            }else{
                 $wishListFlag = '';    
            }
            $_product['is_wishlisted'] = ($wishListFlag != '') ? $wishListFlag : 0;
            $_product['wishlist_item_id'] = $wishListItemId;
		    		    
		    /* Product Media Gallery*/
		    $productimages = $product->getMediaGalleryImages();
		    $im = 0;
		    $media = array();
		    foreach($productimages as $productimage)
            {
                $media[$im]['media_type'] = $productimage['media_type'];
                $media[$im]['value_id'] = $productimage['value_id'];
                $imageUrl = $this->imageHelper->init($productimage, 'image')
                            ->setImageFile($productimage->getFile())
                            ->constrainOnly(TRUE)
                            ->keepAspectRatio(TRUE)
                            ->keepTransparency(TRUE)
                            ->keepFrame(TRUE)
                            ->getUrl(); 
            	$media[$im]['url'] =$imageUrl;
            	$media[$im]['position'] = $productimage['position'];
            	$media[$im]['disabled'] = $productimage['disabled'];
            	$im++;
            }
            
            $_product['media_gallery'] = $media;
            $_product['product_label'] = $this->getProductLabels($product);
        	$_product['multi_buy_label'] = $this->getMultiBuyLabel($product);

		    $response[] = ['status'=> '200','message'=>'successfully loaded product details.', 'data' =>$_product];
		}else{
		    $response[] = ['status'=> '200','message'=>'No record found.', 'data' =>array()];
		}
		return $response;
	}
    
    public function getRelatedProducts($customer_id = '',$product_id, $height,$width){  
        $productIds = array();
        $store = $this->_storeManager->getStore();
        $productImageUrl = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product';
        $brand = $this->getBrand($product_id);

        if ($brand) {
            $pCollection = $this->mgsProduct->getCollection();
            $pCollection->addFieldToFilter('brand_id', ['eq' => $brand->getBrandId()]);
            foreach ($pCollection as $product) {
                    $productIds[] = $product->getProductId();
            }
        }
        $pageSize = $this->_storeConfig->getValue('brand/product_page_settings/limit_related_products', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $productCollection = $this->_productCollectionFactory->create();
        $productCollection->addAttributeToSelect('*');
        $productCollection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());
        $productCollection->getSelect()->order('rand()');
        $productCollection->addFieldToFilter('entity_id', ['in' => $productIds])
            ->addStoreFilter()
            ->setPageSize($pageSize)
            ->setCurPage(1);
        $relatedProducts = array();
       	if($productCollection->getData() != array()) {
         foreach ($productCollection as $product) {
			/* For Wishlist */
	        $wishListFlag = '';
	        $wishListItemId = '';
	        $WishlistId = '';
	        if($customer_id != ''){
	            $wishlist_collection = $this->wishlist->loadByCustomerId($customer_id, true)->getItemCollection();
	            if($wishlist_collection->getData() != array()){
	                foreach ($wishlist_collection as $item) {
	                    if($product->getId() == $item->getProduct()->getId()){
	                        $wishListFlag = 1;
	                        $wishListItemId = $item->getWishlistItemId();
	                        $WishlistId = $item->getWishlistId();
	                        break;
	                    }
	                }
	            }
	        }else{
	             $wishListFlag = '';    
	        }
	        $is_wishlisted = ($wishListFlag != '') ? $wishListFlag : 0;
	        $wishlist_item_id = $wishListItemId;
	        /* END Wishlist */

			/* For Brand */
			$mgs_brand='';
			if($product->getMgsBrand()){
				$mgs_brand = $product->getResource()->getAttribute('mgs_brand')->getFrontend()->getValue($product);
			}

			$imageUrl = $this->imageHelper->init($product, 'image')
                            ->setImageFile($product->getImage())
                            ->constrainOnly(TRUE)
                            ->keepAspectRatio(TRUE)
                            ->keepTransparency(TRUE)
                            ->keepFrame(FALSE)
                            ->getUrl(); 
            if ($this->getRulesFromProduct($product) == null) {
                $discounts = '';
            }else{
                $discounts = $this->getRulesFromProduct($product);
            }
			if($product->isSalable()){
			$relatedProducts[] =[
		        'product_id'    => $product->getId(),
	            'name'          => $product->getName(),
	            'sku'           => $product->getSku(),
	            'image'         => $imageUrl,
	            'url'           => $product->getProductUrl(),
	            'type'          => $product->getTypeId(),
	            'price'         => $this->_currency->format($product->getPriceInfo()->getPrice('regular_price')->getMaxRegularAmount()->getValue(), ['display'=>\Zend_Currency::NO_SYMBOL], false),
	            //'special_price' => $this->_currency->format($product->getPrice('special_price'), ['display'=>\Zend_Currency::NO_SYMBOL], false),
	            'special_price' => $this->_currency->format($product->getFinalPrice(), ['display'=>\Zend_Currency::NO_SYMBOL], false),
	            'discount' => $discounts,
	            'mgs_brand'  => $mgs_brand,
	            'avaibility'    => $product->isAvailable(),
	            'is_wishlisted' => $is_wishlisted,                          
	            'wishlist_item_id' => $wishlist_item_id,                          
	            'currency_code'=> __($this->_storeManager->getStore()->getCurrentCurrency()->getCode()),
	            'multi_buy_label' => $this->getMultiBuyLabel($product)
	        	];
	    	}
		}
		$relatedcollection = ['0' =>['status'=> '200','message'=>'Related product loading successfully.', 'data' =>$relatedProducts]];		
		}else{
		    $relatedcollection[] = ['status'=> '200','message'=>'No record found.', 'data' =>array()];
		}
		return $relatedcollection;		
    }

    public function getBrand($product_id)
    {
        $products = $this->productRepositoryInterface->getById($product_id);	 
        $optionId = $products->getMgsBrand();
        if ($optionId) {
            $collection = $this->_brand->getCollection()->addFieldToFilter('option_id', ['eq' => $optionId]);
            if (count($collection)) {
                return $collection->getFirstItem();
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    public function getPlaceHolderImage(){
		$mediaBaseUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA );
		$placeholderPath = $this->_storeConfig->getValue('catalog/placeholder/image_placeholder');//Base Image
		$fullUrl = $mediaBaseUrl.'catalog/product/placeholder/'.$placeholderPath;
		return $fullUrl;
    }

    /* For Product Label */
    public function getProductLabels($product){
        $html = '';
        $newLabel = $this->_storeConfig->getValue('mpanel/catalog/new_label', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $saleLabel = $this->_storeConfig->getValue('mpanel/catalog/sale_label', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $soldLabel = __('Out of Stock');
        // Out of stock label
        if (!$product->isSaleable() || !$product->isAvailable()){
            $html .= $soldLabel;
        }else {
            // New label
            $numberLabel = 0;
            $now = $this->_date->gmtDate();
            $dateTimeFormat = \Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT;
            $newFromDate = $product->getNewsFromDate();
            if($newFromDate) {
                $newFromDate = date($dateTimeFormat, strtotime($newFromDate));
            }
            $newToDate = $product->getNewsToDate();
            if($newToDate) {
                $newToDate = date($dateTimeFormat, strtotime($newToDate));
            }
            if($newLabel != ''){
                if(!(empty($newToDate))){
                    if(!(empty($newFromDate)) && ($newFromDate < $now) && ($newToDate > $now)){
                        $html = $newLabel;
                        $numberLabel = 1;
                    }
                }   
            }   
            // Sale label
            $price = $product->getOrigData('price');
            $finalPrice = $product->getFinalPrice();
            $fiPrice = $product->getPriceInfo()->getPrice('final_price')->getValue();
            if($this->_storeConfig->getValue('mpanel/catalog/sale_label_discount', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) == 1){
                if(($finalPrice<$price)){
                    $save = $price - $finalPrice;
                    $percent = round(($save * 100) / $price);
                    if($numberLabel == 1){
                        $html = $percent;
                    }else{
                        $html = $percent;
                    }
                }
            }else {
                if($saleLabel!=''){
                    if(($finalPrice<$price) || ($fiPrice<$price)){
                        if($numberLabel == 1){
                            $html = $saleLabel;
                        }else{
                            $html = $saleLabel;
                        }
                    }
                }
            }
        }
        return $html;
    }

    protected function getRulesFromProduct($product)
    {
    	if ($product->getTypeId() == 'configurable') {
            $basePrice = $product->getPriceInfo()->getPrice('regular_price');
            $product_price = $basePrice->getMinRegularAmount()->getValue();
            $specialPrice = $product->getFinalPrice();
            if($specialPrice < $product_price)
            { 
                $_savingPercent = 100 - round(($specialPrice / $product_price)*100); 
                if ($specialPrice) {
                    return $_savingPercent."% OFF";
                }
            }
        }
    }

    /**
     * Get multi buy label
     *
     * @param  $product
     * @return string
     */
    public function getMultiBuyLabel($product)
    {
    	$multiBuyLabel = '';
    	// if($this->multiBuyHelper->isModuleEnable()) {
    	// 	$multiBuyLabel = $this->multiBuyHelper->getMultiBuyProductLabel($product, true);
    	// }

    	return $multiBuyLabel;
    }
}