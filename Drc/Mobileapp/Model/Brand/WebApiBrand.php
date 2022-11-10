<?php
namespace Drc\Mobileapp\Model\Brand;
use Drc\Mobileapp\Api\Brand\WebApiBrandInterface;
use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\Layer\FilterListFactory;

class WebApiBrand implements WebApiBrandInterface
{
    protected $_brand;
    protected $_productCollectionFactory;
    protected $_catalogProductVisibility;
    protected $_productFactory;
    protected $_storeManager; 
    protected $_currency;
    protected $wishlist;
    protected $_layerResolver;
    protected $categoryRepository;
    protected $_storeConfig;
    protected $_date;
    protected $_categoryFactory;
    protected $imageHelper;
    public $priceHelper;
    protected $swatchCollection;
    protected $filterListFactory;
    protected $filterableAttributeList;

    public function __construct(
        \MGS\Brand\Model\Brand $brand,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,        
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Directory\Model\Currency $currency,
        \Magento\Wishlist\Model\Wishlist $wishlist,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\Catalog\Model\CategoryRepository $categoryRepository,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        Image $imageHelper,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Magento\Swatches\Model\ResourceModel\Swatch\CollectionFactory $swatchCollection,
        FilterListFactory $filterListFactory,
        \Magento\Catalog\Model\Layer\Category\FilterableAttributeList $filterableAttributeList

        ) {
        $this->_brand = $brand;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_catalogProductVisibility = $catalogProductVisibility;
        $this->_productFactory = $productFactory;
        $this->_currency = $currency;
        $this->_storeManager = $storeManager;
        $this->wishlist = $wishlist;
        $this->_layerResolver = $layerResolver;
        $this->categoryRepository = $categoryRepository;
        $this->_storeConfig = $scopeConfig;
        $this->_date = $date;
        $this->_categoryFactory = $categoryFactory;
        $this->imageHelper = $imageHelper;
        $this->priceHelper  = $priceHelper;
        $this->swatchCollection = $swatchCollection;
        $this->filterListFactory = $filterListFactory;
        $this->_filterableAttributeList = $filterableAttributeList;
    }
    /**
     * get Brand
     *
     * @return void
     */
    public function getAllBrand() {
        $brands = $this->_brand->getCollection()
        ->addFieldToFilter('status', '1');
        $listBrand = array();
        $store = $this->_storeManager->getStore();
        foreach ($brands as $brand) {
            $listBrand[] = array(
                'brand_id' => $brand->getBrandId(),
                'option_id' => $brand->getOptionId(),
                'name' => $brand->getName(),
                'url_key' => $brand->getUrlKey(),
                'value' => $brand->getId(),
                'store_code' => $brand->getStoreCode(),
                'image' => $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 
                $brand->getSmallImage(),
                );
        }
        return $listBrand;
    }

      /**
     * get all products
     * @param int $option_id
     * @param string $customer_id
     * @param mixed $sort_by           
     * @param int $pageSize
     * @param int $page
     * @param mixed $height
     * @param mixed $width
     * @return mixed
     */
    public function getProductsByBrand($option_id, $customer_id = '',$sort_by = null, $pageSize, $page,$height,$width){

        $collection = $this->_productCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        $collection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());
        $collection->addAttributeToFilter('mgs_brand', array('eq' => $option_id));
        $store = $this->_storeManager->getStore();
        $sortBy = ''; 
        if($sort_by != null){
            if(isset($sort_by) && $sort_by == 1){
                $collection->addAttributeToSort('price', 'ASC');
                $sortBy = 'Price - ASC';
            }else{
                $collection->addAttributeToSort('price', 'DESC');
                $sortBy = 'Price - DESC';
            }
        }  
        $collection->setPageSize($pageSize);
        $collection->setCurPage($page); 
        $confArr = []; 
        foreach ($collection as $product) 
        {
            $newproductobj = $this->_productFactory->create()->load($product->getId());
            /* For get configurable attribute option */
            if($newproductobj->getTypeId() == 'configurable'){
                $attributes  = $newproductobj->getTypeInstance()->getConfigurableOptions($newproductobj);
                $checkArray = array();
                $sizeCheck = array();
                foreach ($attributes as $key => $options_val) {
                    if(!empty($options_val)){
                        foreach($options_val as $newkey => $value){
                            array_shift($value);
                            if(!in_array($value['option_title'],$sizeCheck)) {
                                $sizeCheck[] = $value['option_title'];
                                if($value['attribute_code'] == 'color'){
                                    $swatchCollection = $this->swatchCollection->create();
                                    $swatchCollection->addFieldtoFilter('option_id',$value['value_index']);
                                    $color_item     = $swatchCollection->getFirstItem();
                                    $color_code     = array('color_code' => $color_item->getValue());
                                    $checkArray[$key][] = array_merge($value,$color_code);
                                } else {
                                    $checkArray[$key][] = $value;   
                                }
                            }       
                        }
                    }   
                }
            }
        
            /* For Product Description  */
            $description = ($newproductobj->getDescription()) ? $newproductobj->getDescription() : '' ;
            $short_description = ($newproductobj->getShortDescription()) ? $newproductobj->getShortDescription() : '' ;
            
            /* For Product Image  */   
            $imageUrl = $this->imageHelper->init($newproductobj, 'image')
                            ->setImageFile($newproductobj->getImage())
                            ->constrainOnly(TRUE)
                            ->keepAspectRatio(TRUE)
                            ->keepTransparency(TRUE)
                            ->keepFrame(FALSE)
                            ->getUrl(); 

            /* For Brand */
            $optionValue = ($newproductobj->getMgsBrand() != '') ? $newproductobj->getMgsBrand() : '';
            if($optionValue != ''){
                $attribute = $newproductobj->getResource()->getAttribute('mgs_brand');
                $mgs_brand_lable = '';
                if ($attribute->usesSource()) {
                    $mgs_brand_lable = $attribute->getSource()->getOptionText($optionValue);
                }
                $mgs_brand = $mgs_brand_lable;    
            }else{
                $mgs_brand = '';
            }

            /* For Wishlist */
            $wishListFlag = '';
            $wishListItemId = '';
            $WishlistId = '';
            if($customer_id != ''){
                $wishlist_collection = $this->wishlist->loadByCustomerId($customer_id, true)->getItemCollection();
                if($wishlist_collection->getData() != array()){
                    foreach ($wishlist_collection as $item) {
                        if($newproductobj->getId() == $item->getProduct()->getId()){
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

            /* For category Id */
            $newCatIds = array_values($newproductobj->getCategoryIds());
            $catAssignData = array();
            /* Category Array */
            foreach($newCatIds as $newCatId) {
                $categoryCollection     = $this->_categoryFactory->create()->load($newCatId);
                $catAssignData[]        = array(
                              'id'      => $categoryCollection->getId(),
                              'name'    =>  $categoryCollection->getName()); 
            }
            $regular_price = $product->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue(); 
            $special_price = $this->_currency->format($product->getPrice('special_price'), ['display'=>\Zend_Currency::NO_SYMBOL], false);
            if ($regular_price != $special_price) {
                $specialPrice = $this->_currency->format($newproductobj->getPriceInfo()->getPrice('final_price')->getMinimalPrice()->getValue(), ['display'=>\Zend_Currency::NO_SYMBOL], false);
            }else{
                $specialPrice = $this->_currency->format($this->priceHelper->currency($newproductobj->getSpecialPrice(), false, false), ['display'=>\Zend_Currency::NO_SYMBOL], false);
            }
            if ($this->getRulesFromProduct($newproductobj) == null) {
                $discounts = '';
            }else{
                $discounts = $this->getRulesFromProduct($newproductobj);
            }
            if($newproductobj->getSpecialPrice() && !empty($checkArray)){
            $confArr[] = 
                array(                            
                'product_id'    => $newproductobj->getId(),
                'name'          => $newproductobj->getName(),
                'sku'           => $newproductobj->getSku(),
                'image'         => $imageUrl,
                'url'           => $newproductobj->getProductUrl(),
                'price'         => $this->_currency->format($newproductobj->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue(), ['display'=>\Zend_Currency::NO_SYMBOL], false),
                'special_price' => $specialPrice,         
                'final_price' => $this->_currency->format($newproductobj->getFinalPrice(), ['display'=>\Zend_Currency::NO_SYMBOL], false),
                'discount' => $discounts,
                'short_description' => $short_description,
                'category_ids' => $catAssignData,
                'configurable_product_options' => $checkArray,
                'mgs_brand'  => $mgs_brand,
                'avaibility'    => $newproductobj->isAvailable(),
                'is_wishlisted' => $is_wishlisted,                          
                'wishlist_item_id' => $wishlist_item_id,                          
                'currency_code'=> __($this->_storeManager->getStore()->getCurrentCurrency()->getCode()),
                'product_label' => $this->getProductLabels($product), 
                );
             } else {
                   if(!empty($checkArray)){
                    $confArr[]  = array(
                        'product_id'    => $newproductobj->getId(),
                        'name'          => $newproductobj->getName(),
                        'sku'           => $newproductobj->getSku(),
                        'image'         => $imageUrl,
                        'url'           => $newproductobj->getProductUrl(),
                        'price'         => $this->_currency->format($newproductobj->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue(), ['display'=>\Zend_Currency::NO_SYMBOL], false),
                        'special_price' => $specialPrice,         
                        'final_price' => $this->_currency->format($product->getFinalPrice(), ['display'=>\Zend_Currency::NO_SYMBOL], false),
                        'discount' => $discounts,
                        'short_description' => $short_description,
                        'category_ids' => $catAssignData,
                        'configurable_product_options' => $checkArray,
                        'mgs_brand'  => $mgs_brand,
                        'avaibility'    => $newproductobj->isAvailable(),
                        'is_wishlisted' => $is_wishlisted,                          
                        'wishlist_item_id' => $wishlist_item_id,                          
                        'currency_code'=> __($this->_storeManager->getStore()->getCurrentCurrency()->getCode()),
                        'product_label' => $this->getProductLabels($product),      
                        ); 
                    }    
                }
        }
        $pages = $collection->getLastPageNumber();
        $pagination_status =  $page." of ".$collection->getLastPageNumber();
        $is_next = ($page < $pages) ? true : false;
        $new_array = [
                    'total_count' => $collection->getSize(),
                    'pagination_status'=>$pagination_status,
                    'is_next'=> $is_next,
                    'sort_by'=>$sortBy,
                    'products' => array_values($confArr)
                ]; 
        $productsCollection[] = ['status'=> '200','message'=>'Category Data loading successfully.', 'data' =>$new_array];
        return $productsCollection;
    }

    /* For Category filter on brand */
    public function getCategoryBrand($category_id)
    {
        $categoryLayer = $this->_layerResolver->get();
        $categoryLayer->setCurrentCategory($category_id);
        $categoryLayer = $this->_layerResolver->get()->setCurrentCategory($category_id);
        $collection = $categoryLayer->getProductCollection();
        $collection->addAttributeToFilter('visibility', \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH);
        $collection->addAttributeToFilter('status',\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
        $collection->setFlag('has_stock_status_filter', false);
        $categoryLayer = $this->_layerResolver->get()->setCurrentCategory($category_id);
        $category = $this->categoryRepository->get($category_id, $this->_storeManager->getStore()->getId());
        $filterableAttributes = $this->_filterableAttributeList;
        $filterList = $this->filterListFactory->create(['filterableAttributes' => $filterableAttributes]);   
        $filterAttributes = $filterList->getFilters($categoryLayer);
        $filterArray      = [];
        $i = 0;
        /* Filter on category attribute */
        foreach ($filterAttributes as $filter) {
            $attributeLabel = (string) $filter->getName();
            $attributeCode  = (string) $filter->getRequestVar();
            $items          = $filter->getItems();
            $filterValues   = [];
            $j = 0;
            /* Get attribute Data*/
            foreach ($items as $item) {
                if($collection->getData()) {
                    $brands = $this->_brand->getCollection()->addFieldToFilter('name', array('in' => array($item->getLabel())));
                        $filterValues[$j]['name'] = strip_tags($item->getLabel());
                        $filterValues[$j]['option_id']   = $item->getValue();
                        $filterValues[$j]['count'] = $item->getCount();
                    /* Get Brand Image */   
                    foreach($brands as $brand){
                         $mediaUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
                        $filterValues[$j]['image'] = $mediaUrl.$brand->getSmallImage();
                    }
                }
                $j++;
            }
            /* Check brans value is not empty */
            if (!empty($filterValues)) {
                if($attributeCode == 'mgs_brand') {
                    $filterArray = $filterValues;
                }
            }
            $i++;
        }
        return [$filterArray];
    }

    /* For Brand Image */    
    private function getBrandImage($label){
        $mediaUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $lcase = strtolower(str_replace(" ", "-", $label));
        return $mediaUrl."mgs_brand/".$lcase.".png";
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
    
    protected function getRulesFromProduct($newproductobj)
    {
        if ($newproductobj->getTypeId() == 'configurable') {
            $basePrice = $newproductobj->getPriceInfo()->getPrice('regular_price');
            $product_price = $basePrice->getMinRegularAmount()->getValue();
            $specialPrice = $newproductobj->getFinalPrice();
            if($specialPrice < $product_price)
            { 
                $_savingPercent = 100 - round(($specialPrice / $product_price)*100); 
                if ($specialPrice) {
                    return $_savingPercent."% OFF";
                }
            }
        }
    }
}