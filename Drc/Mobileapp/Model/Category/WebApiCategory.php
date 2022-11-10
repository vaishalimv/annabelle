<?php
namespace Drc\Mobileapp\Model\Category;

use Drc\Mobileapp\Api\Category\WebApiCategoryInterface;
use Magento\Customer\Model\Session;
use Magento\CatalogRule\Model\ResourceModel\Rule as RuleResourceModel; 
use Magento\CatalogRule\Api\CatalogRuleRepositoryInterface;
use Magento\Catalog\Helper\Image;
use Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku;
use Magento\Catalog\Model\Layer\FilterListFactory;

class WebApiCategory implements WebApiCategoryInterface
{
    protected $_productCollectionFactory;
    protected $_categoryFactory;
    protected $categoryRepository;
    protected $_productRepositoryInterface;
    protected $_productFactory;
    protected $_layerResolver;
    protected $filterableAttributeList;
    protected $_storeManager; 
    protected $_reviewFactory;
    protected $_currency;
    protected $_categoryCollectionFactory;
    protected $wishlist;
    protected $_customerSession;
    protected $_datetimezone;
    private $ruleResourceModel; 
    private $catalogRuleRepository;
    protected $_storeConfig;
    protected $_date;
    protected $_brand;
    protected $_connection;
    protected $imageHelper;
    private $getSalableQuantityDataBySku;
    protected $_stockFilter;
    protected $swatchCollection;
    protected $filterListFactory;

    /**
     * @var \Fledge\MultiBuy\Helper\Data
     */
    protected $multiBuyHelper;

    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepositoryInterface,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepositoryInterface,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\Catalog\Model\Layer\Category\FilterableAttributeList $filterableAttributeList,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Review\Model\ResourceModel\Review\CollectionFactory $reviewCollectionFactory,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Magento\Directory\Model\Currency $currency,
        \Magento\Wishlist\Model\Wishlist $wishlist,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        Session $customerSession,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $datetimezone,
        RuleResourceModel $ruleResourceModel,
        CatalogRuleRepositoryInterface $catalogRuleRepository,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \MGS\Brand\Model\Brand $brand,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Catalog\API\ProductRepositoryInterface $productRepository,
        Image $imageHelper,
        GetSalableQuantityDataBySku $getSalableQuantityDataBySku,
        \Magento\CatalogInventory\Helper\Stock $stockFilter,
        \Magento\Swatches\Model\ResourceModel\Swatch\CollectionFactory $swatchCollection,
        FilterListFactory $filterListFactory,
        \Fledge\MultiBuy\Helper\Data $multiBuyHelper
    ) {
        $this->_categoryFactory = $categoryFactory;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->categoryRepository = $categoryRepositoryInterface;
        $this->_productRepositoryInterface = $productRepositoryInterface;
        $this->_productFactory = $productFactory;
        $this->_layerResolver = $layerResolver;
        $this->_filterableAttributeList = $filterableAttributeList;
        $this->_reviewFactory = $reviewFactory;
        $this->_currency = $currency;
        $this->_storeManager = $storeManager;
        $this->wishlist = $wishlist;
        $this->_categoryCollectionFactory = $categoryCollectionFactory;
        $this->_customerSession = $customerSession;
        $this->_datetimezone = $datetimezone;
        $this->ruleResourceModel = $ruleResourceModel;
        $this->catalogRuleRepository = $catalogRuleRepository;
        $this->_storeConfig = $scopeConfig;
        $this->_date = $date;
        $this->_brand = $brand;
        $this->_connection = $resource->getConnection();
        $this->productRepository = $productRepository;
        $this->imageHelper = $imageHelper;
        $this->getSalableQuantityDataBySku = $getSalableQuantityDataBySku;
        $this->_stockFilter = $stockFilter;
        $this->swatchCollection = $swatchCollection;
        $this->filterListFactory = $filterListFactory;
        $this->multiBuyHelper = $multiBuyHelper;
   }
   /**
     * Returns All categories 
     *
     * @return mixed
     */
    public function getAllcategories(){

        $mediaUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        /* Backend store configuration Women Sale category */
        $womenSaleCatId = $this->_storeConfig->getValue('salecategory/women_sale/women_sale_category_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $womenSaleCatImage = $this->_storeConfig->getValue('salecategory/women_sale/image', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $womanCategoryName = $girlsCategoryName  = $boysCategoryName = $mMCategoryName = '';
        if ($womenSaleCatId) {
            $saleCategoryw = $this->categoryRepository->get($womenSaleCatId);
            $womanCategoryName = $saleCategoryw->getName();
        }

        /* Backend store configuration Girls Sale category */
        $girlsSaleCatId = $this->_storeConfig->getValue('salecategory/girls_sale/girls_sale_category_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $girlsSaleCatImage = $this->_storeConfig->getValue('salecategory/girls_sale/image', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($girlsSaleCatId) {
            $saleCategoryg = $this->categoryRepository->get($girlsSaleCatId);
            $girlsCategoryName = $saleCategoryg->getName();
        }

        /* Backend store configuration Boys Sale category */
        $boysSaleCatId = $this->_storeConfig->getValue('salecategory/boys_sale/boys_sale_category_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $boysSaleCatImage = $this->_storeConfig->getValue('salecategory/boys_sale/image', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($boysSaleCatId) {
            $saleCategoryb = $this->categoryRepository->get($boysSaleCatId);
            $boysCategoryName = $saleCategoryb->getName();        
        }

        /* Backend store configuration Mummy and Me Sale category */
        $mummymeSaleCatId = $this->_storeConfig->getValue('salecategory/mummyme_sale/mummyme_sale_category_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $mummymeSaleCatImage = $this->_storeConfig->getValue('salecategory/mummyme_sale/image', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if ($mummymeSaleCatId) {
            $saleCategorym = $this->categoryRepository->get($mummymeSaleCatId);
            $mMCategoryName = $saleCategorym->getName();                
        }
        
        /* Get All Category */
        $collection = $this->_categoryCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        $collection->addAttributeToFilter('level', array('eq' => 1));
        $collection->addAttributeToFilter('is_active', array('eq' => 1));
        $collection->addAttributeToFilter('include_in_menu', array('eq' => 1));
        
        $l = 0;
        $_categories = array();
        if($collection->getData() != array()){
            
            foreach ($collection as $category) {
                $_categories[$l]['id'] = $category->getId();
                $_categories[$l]['name'] = $category->getName();
                $_categories[$l]['category_shape'] = $category->getCategoryShape();
                $_categories[$l]['children_count'] = $category->getChildrenCount();
                $_categories[$l]['image'] = ($category->getImageUrl() != '') ? $category->getImageUrl() : "";
                $_categories[$l]['product_count'] = $category->getProductCollection()->Count();
                /* Category Level 2*/
                $subcategoryCollections = $category->getChildrenCategories();
                $k = 0;
                if($subcategoryCollections->getData() != array()){
                    foreach($subcategoryCollections as $subcategory){
                        $_category = $this->_categoryFactory->create()->load($subcategory->getEntityId());
                        $_category->getCollection()->addAttributeToSelect('*')->addFieldToFilter('is_active', 1);
                   
                        /* Category Filter on Brand */
                        $categoryLayer = $this->_layerResolver->get();
                        $categoryLayer->setCurrentCategory($_category->getEntityId());
                        $categoryLayer = $this->_layerResolver->get()->setCurrentCategory($_category->getEntityId());
                        $collection = $categoryLayer->getProductCollection();
                        $collection->addAttributeToFilter('visibility', \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH);
                        $collection->addAttributeToFilter('status',\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
                        $collection->setFlag('has_stock_status_filter', false);

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
                                    $brands = $this->_brand->getCollection()->addFieldToFilter('option_id', array('in' => array($item->getValue())));
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
                            /* Check brand value is not empty */
                            if (!empty($filterValues)) {
                                if($attributeCode == 'mgs_brand') {
                                    $filterArray = $filterValues;
                                }
                            }
                            $i++;
                        }
                        /* End Brand filer */
                        if ($_category->getIncludeInMenu() == 1) {
                            $_categories[$l]['children_data'][$k] = [
                                'name'                          => $_category->getName(),
                                'entity_id'                     => $_category->getEntityId(),
                                'image'                         => ($_category->getImageUrl() != '') ? $_category->getImageUrl() : "",
                                'children_count'                => $_category->getChildrenCount(),
                                'category_shape'                => $_category->getCategoryShape(),
                                'product_count'                 => $_category->getProductCollection()->Count(),
                                'shop_by_brand'                 => $filterArray
                            ]; 
                        }
                        /* add condition for women sale category  */
                        if($womanCategoryName == $_category->getName()){
                            $_categories[$l]['children_data'][$k]['sale_category'] = [
                                'entity_id' => $womenSaleCatId,
                                'name'     => "SALE",
                                'image' => $mediaUrl.'mobile_banner/'.$womenSaleCatImage
                            ];
                        }

                        /* add condition for girl sale category  */
                        if($girlsCategoryName == $_category->getName()){
                            $_categories[$l]['children_data'][$k]['sale_category'] = [
                                'entity_id' => $girlsSaleCatId,
                                'name'     => "SALE",
                                'image' => $mediaUrl.'mobile_banner/'.$girlsSaleCatImage
                            ];
                        }

                        /* add condition for boys sale category  */
                        if($boysCategoryName == $_category->getName()){
                            $_categories[$l]['children_data'][$k]['sale_category'] = [
                                'entity_id' => $boysSaleCatId,
                                'name'     => "SALE",
                                'image' => $mediaUrl.'mobile_banner/'.$boysSaleCatImage
                            ];
                        }

                        /* add condition for mummy and me sale category  */
                        if($mMCategoryName == $_category->getName()){
                            $_categories[$l]['children_data'][$k]['sale_category'] = [
                                'entity_id' => $mummymeSaleCatId,
                                'name'     => "SALE",
                                'image' => $mediaUrl.'mobile_banner/'.$mummymeSaleCatImage
                            ];
                        }

                        /* Category Level 3 */
                        $subsubcategoryCollections = $_category->getChildrenCategories();
                        $w = 0;
                        if($subsubcategoryCollections->getData() != array()){
                            
                            foreach($subsubcategoryCollections as $subsubcategory){         
                                $_category1 = $this->_categoryFactory->create()->load($subsubcategory->getEntityId());
                                $_category1->getCollection()->addAttributeToSelect('*')->addFieldToFilter('is_active', 1);
                                $_categories[$l]['children_data'][$k]['children_data'][$w] = [
                                    'name'                          => $_category1->getName(),
                                    'entity_id'                     => $_category1->getEntityId(),
                                    'image'                         => ($_category1->getImageUrl() != '') ? $_category1->getImageUrl() : "",
                                    'children_count'                => $_category1->getChildrenCount(),
                                    'category_shape'                => $_category1->getCategoryShape(),
                                    'product_count'                 => $_category1->getProductCollection()->Count()
                                ];

                                /* Category Level 4*/
                                $subsubsubcategoryCollections = $_category1->getChildrenCategories();
                                $wj = 0;
                                if($subsubsubcategoryCollections->getData() != array()){
                                    
                                    foreach($subsubsubcategoryCollections as $subsubsubcategory){           
                                        $_category11 = $this->_categoryFactory->create()->load($subsubsubcategory->getEntityId());
                                        $_category11->getCollection()->addAttributeToSelect('*')->addFieldToFilter('is_active', 1);
                                        $_categories[$l]['children_data'][$k]['children_data'][$w]['children_data'][$wj] = [
                                            'name'                          => $_category11->getName(),
                                            'entity_id'                     => $_category11->getEntityId(),
                                            'image'                         => ($_category11->getImageUrl() != '') ? $_category11->getImageUrl() : "",
                                            'children_count'                => $_category11->getChildrenCount(),
                                            'category_shape'                         => $_category11->getCategoryShape(),
                                            'product_count'                 => $_category11->getProductCollection()->Count()
                                        ];
                                        $wj++;  
                                    }
                                    
                                }else{
                                    $_categories[$l]['children_data'][$k]['children_data'][$w]['children_data'] = array();
                                }    
                                $w++;   
                            }
                        }else{
                            $_categories[$l]['children_data'][$k]['children_data'] = array();
                        }
                        $k++;
                    }
                }else{
                    $_categories[$l]['children_data'] = array();
                    $_categories[$l]['children_data'][$k]['children_data'] = array();
                }
                $l++;
            }
            $_categoriesss['allcategories'] = $_categories;
            $response[] = ['status'=> '200','message'=>'category loaded successfully.', 'data' =>$_categoriesss];
        }else{
            $_categories[$l]['message'] = 'Record not found !! Try agin after sometime.';
            $_categories[$l]['children_data'] = array();
            $response[] = ['status'=> '200','message'=>'No record found.', 'data' =>$_categories];
        }        
        return $response;
    }  
    /**
     * Sort by Price ASC 
     */
    public function sort_by_price_asc($a, $b)
    {
        return $a['price'] - $b['price'];
    } 
    
    /**
     * Sort by Price DESC 
     */
    public function sort_by_price_desc($a, $b)
    {
        return $b['price'] - $a['price'];
    }
    
    /*
     * Returns products
     *
     * @param int $category_id
     * @param int $customer_id
     * @param mixed $sortingfilter
     * @param mixed $height
     * @param mixed $width
     * @return mixed
     */
    public function getCategoryFilterNewVersion($customer_id,$category_id,$page_id,$sortingfilter = null, $height, $width){
        $attrToSelect = ['name','sku','price','final_price','image','category_ids','product_url'];
        $category = $this->_categoryFactory->create()->load($category_id);
        $parent_category =$this->_categoryFactory->create()->load($category->getParentId());
        $parent_categoryname = $parent_category->getName();
        $categoryname = $category->getName();
        if (!empty($category->getCategoryMobileBanner())) {
            $categoryBanner = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'catalog/category/';
            $category_mobile_banner = $categoryBanner.$category->getCategoryMobileBanner();
        }else{
            $category_mobile_banner = '';
        }
        $collections = $this->_productCollectionFactory->create();
        $collections->addAttributeToSelect($attrToSelect);
        $collections->addCategoryFilter($category);
        $collections->setFlag('has_stock_status_filter', true);
        $collections->addAttributeToFilter('visibility', \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH);
        $collections->addAttributeToFilter('status',\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
        $collections->addAttributeToSort('entity_id', 'desc');
        $this->_stockFilter->addInStockFilterToCollection($collections);
        $total_count = $collections->getSize();
        $collections->setPageSize(20)->setCurPage($page_id);
        $product_respone = array();
        $collection_response = array();
        if($collections->getData()){
            foreach($collections as $collection){
                $product_id         = $collection->getId();
                $product            = $this->_productFactory->create()->load($product_id);
                $type = $collection->getTypeID();
                
                if($type == 'configurable'){
                    $attributes  = $product->getTypeInstance()->getConfigurableOptions($product);
                    $checkArray = array();
                    $sizeCheck = array();
                    foreach ($attributes as $key => $options_val) {
                        if(!empty($options_val)){
                            foreach($options_val as $newkey => $value){
                                array_shift($value);
                                $salebleQty = $this->getSalableQuantityDataBySku->execute($options_val[0]['sku']);
                                if($salebleQty[0]['qty']){
                                if ($product->isSaleable()) {
                                    if(!in_array($value['option_title'],$sizeCheck)) {
                                        $sizeCheck[] = $value['option_title'];
                                        if($value['attribute_code'] == 'Size'){
                                            $swatchCollection = $this->swatchCollection->create();
                                            $swatchCollection->addFieldtoFilter('option_id',$value['value_index']);
                                            $size_item     = $swatchCollection->getFirstItem();
                                            $size_code     = array('size_code' => $size_item->getValue());
                                            $checkArray[$key][] = array_merge($value,$size_code);
                                        } else {
                                            $checkArray[$key][] = $value;
                                        }
                                    }
                                } 
                              }      
                            }
                        }   
                    }
                }

                if($product->isSalable()) {
                    $newCatIds = array_values($product->getCategoryIds());
                    $catAssignData = array();
                    /* Category Array */
                    foreach($newCatIds as $newCatId) {
                        $categoryCollection     = $this->_categoryFactory->create()->load($newCatId);
                        $catAssignData[]        = array(
                                      'id'      => $categoryCollection->getId(),
                                      'name'    =>  $categoryCollection->getName()); 
                    }
                    $optionValue = ($product->getMgsBrand() != '') ? $product->getMgsBrand() : '';
                    if($optionValue != ''){
                        $attribute = $product->getResource()->getAttribute('mgs_brand');
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

                    $imageUrl = $this->imageHelper->init($product, 'image')
                            ->setImageFile($product->getImage())
                            ->constrainOnly(TRUE)
                            ->keepAspectRatio(TRUE)
                            ->keepTransparency(TRUE)
                            ->keepFrame(FALSE)
                            ->getUrl(); 

                    $custom_price = $this->_currency->format($product->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue(), ['display'=>\Zend_Currency::NO_SYMBOL], false);
                    $custom_special_price = $this->_currency->format($product->getFinalPrice(), ['display'=>\Zend_Currency::NO_SYMBOL], false);        
                    
                    if ($custom_price == $custom_special_price) {
                        $specialPrice = '';
                    }
                    else{
                      $specialPrice = $this->_currency->format($product->getFinalPrice(), ['display'=>\Zend_Currency::NO_SYMBOL], false);  
                    }
                    if ($this->getRulesFromProduct($product) == null) {
                        $discounts = '';
                    }else{
                        $discounts = $this->getRulesFromProduct($product);
                    }
                    if($product->getSpecialPrice() && !empty($checkArray)){
                       $product_respone[]  = array(
                            'id' => $product->getId(),
                            'name' => $product->getName(),
                            'sku' => $product->getSku(),
                            'mgs_brand'  => $mgs_brand,
                            'currency_code'=> __($this->_storeManager->getStore()->getCurrentCurrency()->getCode()),
                            'price' => $this->_currency->format($product->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue(), ['display'=>\Zend_Currency::NO_SYMBOL], false),
                            'special_price' => $specialPrice,
                            'discount' => $discounts,
                            'image' => $imageUrl,
                            'url' => $product->getProductUrl(),
                            'category_ids' => $catAssignData,
                            'configurable_product_options' => $checkArray,
                            'is_wishlisted' => ($wishListFlag != '') ? $wishListFlag : 0,
                            'wishlist_item_id' => $wishListItemId,
                            'wishlist_id' => $WishlistId,
                            'product_label' => $this->getProductLabels($product), 
                            'multi_buy_label' => $this->getMultiBuyLabel($product)   
                            );    
                    } else {
                       if(!empty($checkArray)){
                        $product_respone[]  = array(
                            'id' => $product->getId(),
                            'name' => $product->getName(),
                            'sku' => $product->getSku(),
                            'mgs_brand'  => $mgs_brand,
                            'currency_code'=> __($this->_storeManager->getStore()->getCurrentCurrency()->getCode()),
                            'price' => $this->_currency->format($product->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue(), ['display'=>\Zend_Currency::NO_SYMBOL], false),
                            'special_price' => $specialPrice,
                            'discount' => $discounts,
                            'image' => $imageUrl,
                            'url' => $product->getProductUrl(),
                            'category_ids' => $catAssignData,
                            'configurable_product_options' => $checkArray,
                            'is_wishlisted' => ($wishListFlag != '') ? $wishListFlag : 0,
                            'wishlist_item_id' => $wishListItemId,
                            'wishlist_id' => $WishlistId, 
                            'product_label' => $this->getProductLabels($product),  
                            'multi_buy_label' => $this->getMultiBuyLabel($product)
                            ); 
                        }    
                    }
                        
                }
            }            
            if($sortingfilter != null){
                if(isset($sortingfilter) && $sortingfilter['sort_order'] == 'ASC'){
                    uasort($product_respone,array($this,'sort_by_price_asc'));
                } else if(isset($sortingfilter) && $sortingfilter['sort_order'] == 'DESC'){
                    uasort($product_respone,array($this,'sort_by_price_desc'));
                }   
            }
            
            if($product_respone){
                $collection_response[] =['parent_categoryname'=>$parent_categoryname,'categoryname'=>$categoryname, 'category_banner'=>$category_mobile_banner ,'products'=>$product_respone,'total_count'=>$total_count]; 
            }
            return $collection_response;
        } else {
            if($product_respone){
                $collection_response[] =['parent_categoryname'=>$parent_categoryname,'categoryname'=>$categoryname, 'category_banner'=>$category_mobile_banner,'products'=>$product_respone,'total_count'=>$total_count]; 
            }
            return $collection_response;
        }
    } 
 
    /* Product Placeholder Image */
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

    /**
     * Returns shop under products
     *
     * @param int $category_id
     * @param string $customer_id
     * @param int $page_id
     * @param mixed $sortingfilter 
     * @param mixed $height
     * @param mixed $width 
     * @return mixed
     */
     public function getShopUnderProduct($customer_id,$category_id,$page_id,$sortingfilter = null,$height,$width){

        $offset = ($page_id - 1)*80 ;
        $row_count = 80; 
        $query = $this->_connection->select()->from($this->_connection->getTableName('webspeaks_product_attachment_rel'),['product_id','contact_id'])->where('contact_id = ?', $category_id)->limit($offset, $row_count);
        $productCollection = $this->_connection->fetchAll($query);
        $product_respone = array();

        foreach($productCollection as $productId){
            $product = $this->productRepository->getById($productId['product_id']);            
            $type = $product->getTypeID();
            if($type == 'configurable'){
            
                $attributes = $product->getTypeInstance()->getConfigurableOptions($product);
                $checkArray = array();
                $sizeCheck = array();
                foreach ($attributes as $key => $options_val) {
                    if(!empty($options_val)){
                    foreach($options_val as $newkey => $value){
                        array_shift($value);
                        if ($product->isSaleable()) {
                            if(!in_array($value['option_title'],$sizeCheck)) {
                                $sizeCheck[] = $value['option_title'];
                                if($value['attribute_code'] == 'size'){
                                    $swatchCollection = $this->swatchCollection->create();
                                    $swatchCollection->addFieldtoFilter('option_id',$value['value_index']);
                                    $size_item     = $swatchCollection->getFirstItem();
                                    $size_code     = array('size_code' => $size_item->getValue());
                                    $checkArray[$key][] = array_merge($value,$size_code);
                                } else {
                                    $checkArray[$key][] = $value;   
                                }
                              }
                            }       
                        }
                    }   
                }
            }

            if($product->isSalable()) {
                $newCatIds = array_values($product->getCategoryIds());
                $catAssignData = array();
                /* Category Array */
                foreach($newCatIds as $newCatId) {
                        $categoryCollection     = $this->_categoryFactory->create()->load($newCatId);
                        $catAssignData[]        = array(
                                      'id'      => $categoryCollection->getId(),
                                      'name'    =>  $categoryCollection->getName()); 
                    }
                $optionValue = ($product->getMgsBrand() != '') ? $product->getMgsBrand() : '';
                if($optionValue != ''){
                    $attribute = $product->getResource()->getAttribute('mgs_brand');
                    $mgs_brand_lable = '';
                    if ($attribute->usesSource()) {
                        $mgs_brand_lable = $attribute->getSource()->getOptionText($optionValue);
                    }
                    $mgs_brand = $mgs_brand_lable;
                }else{
                    $mgs_brand = '';
                }
                /* Wishlist Product check*/
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
                 /* For Product Image  */   
                $imageUrl = $this->imageHelper->init($product, 'image')
                            ->setImageFile($product->getImage())
                            ->constrainOnly(TRUE)
                            ->keepAspectRatio(TRUE)
                            ->keepTransparency(TRUE)
                            ->keepFrame(FALSE)
                            ->getUrl(); 

                /*check special price*/
                $custom_price = $this->_currency->format($product->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue(), ['display'=>\Zend_Currency::NO_SYMBOL], false);
                $custom_special_price = $this->_currency->format($product->getFinalPrice(), ['display'=>\Zend_Currency::NO_SYMBOL], false);        
                
                if ($custom_price == $custom_special_price) {
                    $specialPrice = '';
                }
                else{
                  $specialPrice = $this->_currency->format($product->getFinalPrice(), ['display'=>\Zend_Currency::NO_SYMBOL], false);  
                }
                /*Discount*/
                if ($this->getRulesFromProduct($product) == null) {
                    $discounts = '';
                }else{
                    $discounts = $this->getRulesFromProduct($product);
                }
                if($product->getSpecialPrice() && empty($checkArray)){
                   $product_respone[]  = array(
                        'id' => $product->getId(),
                        'name' => $product->getName(),
                        'sku' => $product->getSku(),
                        'mgs_brand'  => $mgs_brand,
                        'currency_code'=> __($this->_storeManager->getStore()->getCurrentCurrency()->getCode()),
                        'price' => $this->_currency->format($product->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue(), ['display'=>\Zend_Currency::NO_SYMBOL], false),
                        'special_price' => $specialPrice,         
                        'final_price' => $this->_currency->format($product->getFinalPrice(), ['display'=>\Zend_Currency::NO_SYMBOL], false),
                        'discount' => $discounts,
                        'image' => $imageUrl,
                        'url' => $product->getProductUrl(),
                        'category_ids' => $catAssignData,
                        'configurable_product_options' => $checkArray,
                        'is_wishlisted' => $wishListFlag,
                        'wishlist_id' => $WishlistId,
                        'wishlist_item_id' => $wishListItemId,
                        'product_label' => $this->getProductLabels($product), 
                        'multi_buy_label' => $this->getMultiBuyLabel($product) 
                        );    
                } else {
                   if(!empty($checkArray)){
                    $product_respone[]  = array(
                        'id' => $product->getId(),
                        'name' => $product->getName(),
                        'sku' => $product->getSku(),
                        'mgs_brand'  => $mgs_brand,
                        'currency_code'=> __($this->_storeManager->getStore()->getCurrentCurrency()->getCode()),
                        'price' => $this->_currency->format($product->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue(), ['display'=>\Zend_Currency::NO_SYMBOL], false),
                        'special_price' => $specialPrice,         
                        'final_price' => $this->_currency->format($product->getFinalPrice(), ['display'=>\Zend_Currency::NO_SYMBOL], false),
                        'discount' => $discounts,   
                        'image' => $imageUrl,
                        'url' => $product->getProductUrl(),
                        'category_ids' => $catAssignData,
                        'configurable_product_options' => $checkArray,
                        'is_wishlisted' => $wishListFlag,
                        'wishlist_id' => $WishlistId,
                        'wishlist_item_id' => $wishListItemId, 
                        'product_label' => $this->getProductLabels($product),
                        'multi_buy_label' => $this->getMultiBuyLabel($product)
                        ); 
                    }    
                }
            }
            if($sortingfilter != null){
                if(isset($sortingfilter) && $sortingfilter['sort_order'] == 'ASC'){
                    uasort($product_respone,array($this,'sort_by_price_asc'));
                } else if(isset($sortingfilter) && $sortingfilter['sort_order'] == 'DESC'){
                    uasort($product_respone,array($this,'sort_by_price_desc'));
                }   
            }
            
        } 
        if($product_respone){
            $collection_response[] =['products'=>$product_respone]; 
        }
        else{
             $collection_response = [];
        }
        return $collection_response;
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
        //     $multiBuyLabel = $this->multiBuyHelper->getMultiBuyProductLabel($product, true);
        // }

        return $multiBuyLabel;
    }
}
