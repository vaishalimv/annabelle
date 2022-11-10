<?php
namespace Drc\Mobileapp\Model\Homepage;

use Drc\Mobileapp\Api\Homepage\WebApiHomepageInterface;
use Magento\Sales\Model\ResourceModel\Report\Bestsellers\CollectionFactory as BestSellersCollectionFactory;
use Absolute\AdvancedSlider\Model\ResourceModel\Slides\CollectionFactory;
use Magento\Framework\Data\Collection;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Catalog\Helper\Image;
use Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku;

class WebApiHomepage implements WebApiHomepageInterface{	

	protected $_bestSellersCollectionFactory;
	protected $_productRepository;
	protected $_storeManager;	
	protected $_categoryFactory;
	protected $_currency;
	protected $wishlist;
    protected $_storeConfig;
	protected $contactCollection;
    protected $_brand;
    protected $collectionFactory;
    protected $_date;
    protected $configurable;
    protected $imageHelper;
    protected $swatchCollection;
    private $getSalableQuantityDataBySku;

	public function __construct(
        \Magento\Catalog\Model\ProductRepository $productRepository,
        BestSellersCollectionFactory $bestSellersCollectionFactory,		
		\Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Directory\Model\Currency $currency,
		\Magento\Wishlist\Model\Wishlist $wishlist,
        \Magento\Store\Model\StoreManagerInterface $storemanager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Webspeaks\ProductsGrid\Model\ResourceModel\Contact\Collection $contactCollection,
		\MGS\Brand\Model\Brand $brand,
		CollectionFactory $collectionFactory,
		\Magento\Framework\Stdlib\DateTime\DateTime $date,
        Configurable $configurable,
        Image $imageHelper,
        \Magento\Swatches\Model\ResourceModel\Swatch\CollectionFactory $swatchCollection,
        GetSalableQuantityDataBySku $getSalableQuantityDataBySku,
        \Fledge\MultiBuy\Helper\Data $multiBuyHelper
    ) {
        $this->_bestSellersCollectionFactory 		= $bestSellersCollectionFactory; 
		$this->_productRepository					= $productRepository;
		$this->_storeManager 						= $storemanager;
		$this->_categoryFactory 					= $categoryFactory;
		$this->_currency 							= $currency;
		$this->wishlist 							= $wishlist;
        $this->_storeConfig 						= $scopeConfig;
        $this->contactCollection 					= $contactCollection;
        $this->_brand                               = $brand;
        $this->collectionFactory                    = $collectionFactory;
		$this->_date                                = $date;
		$this->configurable                         = $configurable;
		$this->imageHelper                          = $imageHelper;
		$this->swatchCollection                     = $swatchCollection;
		$this->getSalableQuantityDataBySku          = $getSalableQuantityDataBySku;
		$this->multiBuyHelper = $multiBuyHelper;
	}
	
	/** 
     * @return \Drc\Mobileapp\Api\Category\Data\HomepagemergerInterface
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
	

	public function getHomepageMergerFunction($customer_id = ''){

		/******************* Backend Store configuration for homepage ************************/

		$enableBestSeller = $this->_storeConfig->getValue('homepagebanner/home_best_seller/enable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$bestSellerCategoryId = $this->_storeConfig->getValue('homepagebanner/home_best_seller/best_seller_category_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$homeShopUnder = $this->_storeConfig->getValue('homepagebanner/home_shop_under/enable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$enableSeeMoreCategory = $this->_storeConfig->getValue('homepagebanner/home_see_more_category/enable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$homeSeeMoreCategoryId = $this->_storeConfig->getValue('homepagebanner/home_see_more_category/see_more_category_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$enableHomeMummyMe = $this->_storeConfig->getValue('homepagebanner/home_mummy_me/enable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$homeMummyMeCategoryId = $this->_storeConfig->getValue('homepagebanner/home_mummy_me/mummy_me_category_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$homeShopByBrand = $this->_storeConfig->getValue('homepagebanner/home_shop_by_brand/enable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$homePlusSize = $this->_storeConfig->getValue('homepagebanner/home_plus_size/enable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$homePlusSizeCategoryId = $this->_storeConfig->getValue('homepagebanner/home_plus_size/plus_size_category_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$homeTrending = $this->_storeConfig->getValue('homepagebanner/home_trending/enable', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$homeTrendingCategoryId = $this->_storeConfig->getValue('homepagebanner/home_trending/trending_category_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$homeSortOrderBanner_1 = $this->_storeConfig->getValue('homepagebanner/general_1/sort_order', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$homeSortOrderBanner_2 = $this->_storeConfig->getValue('homepagebanner/general_2/sort_order', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$homeSortOrderBanner_3 = $this->_storeConfig->getValue('homepagebanner/general_3/sort_order', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$homeSortOrderBestSeller = $this->_storeConfig->getValue('homepagebanner/home_best_seller/sort_order', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$homeSortOrderShopUnder = $this->_storeConfig->getValue('homepagebanner/home_shop_under/sort_order', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$homeSortOrderSeeMore = $this->_storeConfig->getValue('homepagebanner/home_see_more_category/sort_order', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$homeSortOrderMummyMe = $this->_storeConfig->getValue('homepagebanner/home_mummy_me/sort_order', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$homeSortOrderBrand = $this->_storeConfig->getValue('homepagebanner/home_shop_by_brand/sort_order', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$homeSortOrderPlusSize = $this->_storeConfig->getValue('homepagebanner/home_plus_size/sort_order', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$homeSortOrderTrending = $this->_storeConfig->getValue('homepagebanner/home_trending/sort_order', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$sortOrderServiceIcon = $this->_storeConfig->getValue('homepagebanner/service_block_slider_1/sort_order', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

		/**************************** Banner Slider ************************************/

		$banner_response = []; 
		$currentStoreId = $this->_storeManager->getStore()->getId();
		
		$mediaUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
		$stores = [
            0, // all store views
            $this->_storeManager->getStore()->getWebsiteId()
        ];
        /* $collection ResourceModel\Slides\Collection */
        $collection = $this->collectionFactory->create();

        if ($currentStoreId == 1) {
        	$collection->addFieldToFilter("main_table.slider_id", ['eq' => $currentStoreId])
            ->addFieldToFilter('active_from', [['lteq' => date('Y-m-d H:i:s')], ['null' => true]])
            ->addFieldToFilter('active_to', [['gteq' => date('Y-m-d H:i:s')], ['null' => true]])
            ->addFieldToFilter('main_table.store_id',['in' => $stores])
            ->setOrder('main_table.order', Collection::SORT_ORDER_ASC);
        }
        if ($currentStoreId == 8) {
        	$collection->addFieldToFilter("main_table.slider_id", ['eq' => 2])
            ->addFieldToFilter('active_from', [['lteq' => date('Y-m-d H:i:s')], ['null' => true]])
            ->addFieldToFilter('active_to', [['gteq' => date('Y-m-d H:i:s')], ['null' => true]])
            ->addFieldToFilter('main_table.store_id',['in' => $stores])
            ->setOrder('main_table.order', Collection::SORT_ORDER_ASC);
        }


        $banner_response = [];
        $j = 0;
        try{
			if($collection->getData()){
		        foreach($collection as $banner){
		            $banner_response[$j]['slide_id'] = $banner->getSlideId();
		            $banner_response[$j]['slider_id'] = $banner->getSliderId();
		            $banner_response[$j]['store_id'] = $banner->getStoreId();
		            $banner_response[$j]['title'] = $banner->getTitle();
		            $banner_response[$j]['mobile_image'] = $mediaUrl.'absolute_advancedslider/image/'.$banner->getMobileImage();
		            $banner_response[$j]['link']  = $banner->getLink();
		            $banner_response[$j]['category_id'] = $banner->getCategoryId();
		            $banner_response[$j]['order'] = $banner->getOrder();
		            $j++;
		        }
		    }
		}catch (\Magento\Framework\Exception\LocalizedException $exception) {
			$banner_response = array();
        }    
		 
		/**************************** For Shop By Category ***********************************/

		$cat_id = 2;
		$subcategories = $this->_categoryFactory->create()->load($cat_id); 
		$subcategoryCollections = $subcategories->getChildrenCategories(); 
		$subCategoryDetails = []; 
		try{
			if($subcategoryCollections->getData() != array()){
				foreach($subcategoryCollections as $subcategory){			
					$category = $this->_categoryFactory->create()->load($subcategory->getEntityId());
					$category->getCollection()->addAttributeToSelect('*')->addFieldToFilter('is_active', 1);			
					if ($category->getIncludeInMenu() == 1) {
					$subCategoryDetails[] = [
						'name'							=> $category->getName(),
						'image'							=> ($category->getThumbnail() != null) ? $mediaUrl.'catalog/category/'.$category->getThumbnail() : '',
						'entity_id'						=> $category->getEntityId(),
						'attribute_set_id'				=> $category->getAttributeSetId(),
						'is_active'						=> $category->getIsActive(),
						'include_in_menu'               => $category->getIncludeInMenu(),
						'parent_id'						=> $category->getParentId(),
						'url'							=> $category->getUrl(),
						'children_count'				=> $category->getChildrenCount(),
						'category_shape'                => $category->getCategoryShape(),						 
					];
				} 
			}
		}	
		}catch (\Magento\Framework\Exception\LocalizedException $exception) {
				$subCategoryDetails = array();
		} 	
		 
		
		/**************************** For Top sellers ********************************/
	    $store = $this->_storeManager->getStore();
	   	$bestSellerData = $this->_bestSellersCollectionFactory->create()->setModel('Magento\Catalog\Model\Product')->setPeriod('month'); 
	    $bestSellers = [];
	    $parent_arr = [];
	    try {
	    foreach ($bestSellerData as $item){
       	$parentIds = $this->configurable->getParentIdsByChild($item->getProductId());
         if(isset($parentIds[0])){                         
         $productIds =  $parentIds[0]; //Configurable product ids here 
           if (!in_array($productIds, $parent_arr)) { // Avoid Repetition Of Configurable product id    
               $parent_arr[] = $productIds;                       
               	$Products = $this->_productRepository->getById($productIds); 
                $mgs_brand='';
				if($Products->getMgsBrand()){
					$mgs_brand = $Products->getResource()->getAttribute('mgs_brand')->getFrontend()->getValue($Products);
				}
				$productsDiscount = '';
				$regular_price = $this->_currency->format($Products->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue(), ['display'=>\Zend_Currency::NO_SYMBOL], false);
		        $special_price = $this->_currency->format($Products->getFinalPrice(), ['display'=>\Zend_Currency::NO_SYMBOL], false);
			    if($special_price < $regular_price)
			    { 
			        $_savingPercent = 100 - round(((int)$special_price / (int)$regular_price)*100); 
			        if ($special_price) {
			            $productsDiscount = $_savingPercent."% OFF";
			        }else{
			        	$productsDiscount = '';
			        }

			    }

			    /* For Wishlist */ 
                $wishListFlag = '';
                $wishListItemId = '';
                $WishlistId = '';
                if($customer_id != ''){
                    $wishlist_collection = $this->wishlist->loadByCustomerId($customer_id, true)->getItemCollection();
                    if($wishlist_collection->getData() != array()){
                        foreach ($wishlist_collection as $item) {
                            if($Products->getId() == $item->getProduct()->getId()){
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
              	$custom_price = $this->_currency->format($Products->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue(), ['display'=>\Zend_Currency::NO_SYMBOL], false);
              	$custom_special_price = $this->_currency->format($Products->getFinalPrice(), ['display'=>\Zend_Currency::NO_SYMBOL], false);
              	if ($custom_price == $custom_special_price) {
              		$custom_specialPrice = '';
              	}else{
              		$custom_specialPrice = $this->_currency->format($Products->getFinalPrice(), ['display'=>\Zend_Currency::NO_SYMBOL], false);
              	}


                if ($this->checkConfigurableProduct($Products)) {
						$bestSellers[] = [
							"product_id"	=>  $Products->getId(),
							"sku" 			=> 	$Products->getSku(),
							"mgs_brand"	    =>	$mgs_brand,						
							"name" 			=>	$Products->getName(),
							"url"			=>  $Products->getProductUrl(),
							"image" 		=> 	$this->getProductsImage($Products),			        
							"price" => $this->_currency->format($Products->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue(), ['display'=>\Zend_Currency::NO_SYMBOL], false),
                            "special_price" => $custom_specialPrice,
		                    "final_price" => $this->_currency->format($Products->getFinalPrice(), ['display'=>\Zend_Currency::NO_SYMBOL], false),
							"discount" => $productsDiscount,
							"currency_code"	=> __($this->_storeManager->getStore()->getCurrentCurrency()->getCode()),
							"is_wishlisted" => ($wishListFlag != '') ? $wishListFlag : 0,
                            "wishlist_item_id" => $wishListItemId,
                            "wishlist_id" => $WishlistId, 
                            "multi_buy_label" => $this->getMultiBuyLabel($Products)
						];
					}
         		}
         	}
        }
        }catch (\Magento\Framework\Exception\LocalizedException $exception) {
			$bestSellers = array();
		}

		/******************** For Shop Under Category  ********************/ 
		$shop_category = $this->contactCollection;
		$shop_under = array();
		foreach($shop_category as $shop_under_cat)
		{
			$shop_under[] = array(
                'id' => $shop_under_cat->getContactId(),
                'title' => $shop_under_cat->getTitle(),
                "currency_code"	=> __($this->_storeManager->getStore()->getCurrentCurrency()->getCode()),
                );
		}
		
		/**************************  For Shop by brand ***************/
        $brands = $this->_brand->getCollection()
        ->addFieldToFilter('status', '1');
        $listBrand = array();
        $store = $this->_storeManager->getStore();
        foreach ($brands as $brand) {
            $listBrand[] = array(
				'brand_id' => $brand->getBrandId(),
                'label' => $brand->getName(),
                'url_key' => $brand->getUrlKey(),
                'option_id' => $brand->getOptionId(),
                'image' => $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 
                $brand->getSmallImage(),
            );
        }

		/*******************************  For See more category  **************************/
		$subcategories = $this->_categoryFactory->create()->load($homeSeeMoreCategoryId); 
		$subcategoryCollections = $subcategories->getChildrenCategories(); 
		$see_more_category = []; 
		try{
			if($subcategoryCollections->getData() != array()){
				foreach($subcategoryCollections as $subcategory){		
					$category = $this->_categoryFactory->create()->load($subcategory->getEntityId());
					$category->getCollection()->addAttributeToSelect('*')->addFieldToFilter('is_active', 1);			
					$see_more_category[] = [
						'name'							=> $category->getName(),
						'image'							=> ($category->getThumbnail() != null) ? $mediaUrl.'catalog/category/'.$category->getThumbnail() : '',
						'entity_id'						=> $category->getEntityId(),
						'attribute_set_id'				=> $category->getAttributeSetId(),
						'is_active'						=> $category->getIsActive(),
						'parent_id'						=> $category->getParentId(),
						'url'							=> $category->getUrl(),
						'children_count'				=> $category->getChildrenCount(),
						'category_shape'                => $category->getCategoryShape(),
					]; 
				}
			}	
		}catch (\Magento\Framework\Exception\LocalizedException $exception) {
				$subCategoryDetails = array();
		}

		/******************************  For Mummy & Me category *****************************/
		$category = $this->_categoryFactory->create()->load($homeMummyMeCategoryId);
		$categoryNamemm = $category->getName();
		$categoryIdmm = $category->getId();	
		$categoryProducts = $category->getProductCollection()->addAttributeToSelect('*')
							->addAttributeToSort('entity_id', 'DESC')->setPageSize(25);
		$muumy_me_category = array();
		foreach ($categoryProducts as $Products) {
			    $Products = $this->_productRepository->getById($Products->getId());
				$mgs_brand='';
				if($Products->getMgsBrand()){
					$mgs_brand = $Products->getResource()->getAttribute('mgs_brand')->getFrontend()->getValue($Products);
				}
				
	            /* For Wishlist */ 
                $wishListFlag = '';
                $wishListItemId = '';
                $WishlistId = '';
                if($customer_id != ''){
                    $wishlist_collection = $this->wishlist->loadByCustomerId($customer_id, true)->getItemCollection();
                    if($wishlist_collection->getData() != array()){
                        foreach ($wishlist_collection as $item) {
                            if($Products->getId() == $item->getProduct()->getId()){
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
               	$custom_price = $this->_currency->format($Products->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue(), ['display'=>\Zend_Currency::NO_SYMBOL], false);
              	$custom_special_price = $this->_currency->format($Products->getFinalPrice(), ['display'=>\Zend_Currency::NO_SYMBOL], false);
              	if ($custom_price == $custom_special_price) {
              		$custom_specialPrice = '';
              	}else{
              		$custom_specialPrice = $this->_currency->format($Products->getFinalPrice(), ['display'=>\Zend_Currency::NO_SYMBOL], false);
              	}
                if ($this->checkConfigurableProduct($Products)) {
			    $muumy_me_category[] =[
	    			"entity_id"        => $Products->getId(),
	    			"name"             => $Products->getName(),
	    			"sku"              => $Products->getSku(),
	    			"mgs_brand"	       => $mgs_brand,
	    			"image" 		   => $this->getProductsImage($Products),
	    			"currency_code"	   => __($this->_storeManager->getStore()->getCurrentCurrency()->getCode()),
	    			"price" => $this->_currency->format($Products->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue(), ['display'=>\Zend_Currency::NO_SYMBOL], false),
                    "special_price" => $custom_special_price,         
                    "final_price"      => $this->_currency->format($Products->getFinalPrice(), ['display'=>\Zend_Currency::NO_SYMBOL], false),
                    "discount"         => $this->getRulesFromProduct($Products),
                    "product_label"    => $this->getProductLabels($Products),
                    "is_wishlisted"    => ($wishListFlag != '') ? $wishListFlag : 0,
                    "wishlist_item_id" => $wishListItemId,
                    "wishlist_id"      => $WishlistId, 
                    "multi_buy_label" => $this->getMultiBuyLabel($Products)
				];
			}
		}

		/******************************  For Plus Size category *****************************/
		$plusSizeCategory = $this->_categoryFactory->create()->load($homePlusSizeCategoryId);
		$categoryIdpz = $plusSizeCategory->getId();	
		$categoryNamepz = $plusSizeCategory->getName();
		$categoryProducts = $plusSizeCategory->getProductCollection()->addAttributeToSelect('*')
							->addAttributeToSort('entity_id', 'DESC')->setPageSize(10);
		$plus_size_category = array();
		foreach ($categoryProducts as $Products) {
			if ($Products->isSaleable()) {
			    $Products = $this->_productRepository->getById($Products->getId());	 
			    $mgs_brand='';
				if($Products->getMgsBrand()){
					$mgs_brand = $Products->getResource()->getAttribute('mgs_brand')->getFrontend()->getValue($Products);
				}
				
                 /* For Wishlist */ 
                $wishListFlag = '';
                $wishListItemId = '';
                $WishlistId = '';
                if($customer_id != ''){
                    $wishlist_collection = $this->wishlist->loadByCustomerId($customer_id, true)->getItemCollection();
                    if($wishlist_collection->getData() != array()){
                        foreach ($wishlist_collection as $item) {
                            if($Products->getId() == $item->getProduct()->getId()){
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

                $custom_price = $this->_currency->format($Products->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue(), ['display'=>\Zend_Currency::NO_SYMBOL], false);
              	$custom_special_price = $this->_currency->format($Products->getFinalPrice(), ['display'=>\Zend_Currency::NO_SYMBOL], false);
              	if ($custom_price == $custom_special_price) {
              		$custom_specialPrice = '';
              	}else{
              		$custom_specialPrice = $this->_currency->format($Products->getFinalPrice(), ['display'=>\Zend_Currency::NO_SYMBOL], false);
              	}
                if ($this->checkConfigurableProduct($Products)) {
                	
				    $plus_size_category[] =[
		    			"entity_id"=> $Products->getId(),
		    			"name"=> $Products->getName(),
		    			"sku"=> $Products->getSku(),
		    			"image" 		=> 	$this->getProductsImage($Products),
		    			"mgs_brand"	=>	$mgs_brand,
		    			"currency_code"	=> __($this->_storeManager->getStore()->getCurrentCurrency()->getCode()),
		    			"price" => $this->_currency->format($Products->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue(), ['display'=>\Zend_Currency::NO_SYMBOL], false),
	                    "special_price" => $custom_specialPrice,        
	                    "final_price" => $this->_currency->format($Products->getFinalPrice(), ['display'=>\Zend_Currency::NO_SYMBOL], false),
	                    "discount" => $this->getRulesFromProduct($Products),
	                    "product_label" => $this->getProductLabels($Products),
	                    "is_wishlisted" => ($wishListFlag != '') ? $wishListFlag : 0,
	                    "wishlist_item_id" => $wishListItemId,
	                    "wishlist_id" => $WishlistId, 
                        "multi_buy_label" => $this->getMultiBuyLabel($Products)
		    		];
	    		}
			}
		}

		/******************************  For Trending category *****************************/
		$trendingCategory = $this->_categoryFactory->create()->load($homeTrendingCategoryId);
		$categoryIdTrending = $trendingCategory->getId();	
		$categoryNameTrending = $trendingCategory->getName();
		$categoryProduct = $trendingCategory->getProductCollection()->addAttributeToSelect('*')
							->addAttributeToSort('entity_id', 'DESC')->setPageSize(30);
		$trending_category = [];
		foreach ($categoryProduct as $Products) {
		    $Products = $this->_productRepository->getById($Products->getId());	 
		    $mgs_brand='';
			if($Products->getMgsBrand()){
				$mgs_brand = $Products->getResource()->getAttribute('mgs_brand')->getFrontend()->getValue($Products);
			}			
             /* For Wishlist */ 
            $wishListFlag = '';
            $wishListItemId = '';
            $WishlistId = '';
            if($customer_id != ''){
                $wishlist_collection = $this->wishlist->loadByCustomerId($customer_id, true)->getItemCollection();
                if($wishlist_collection->getData() != array()){
                    foreach ($wishlist_collection as $item) {
                        if($Products->getId() == $item->getProduct()->getId()){
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
            $custom_price = $this->_currency->format($Products->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue(), ['display'=>\Zend_Currency::NO_SYMBOL], false);
          	$custom_special_price = $this->_currency->format($Products->getFinalPrice(), ['display'=>\Zend_Currency::NO_SYMBOL], false);
          	if ($custom_price == $custom_special_price) {
          		$custom_specialPrice = '';
          	}else{
          		$custom_specialPrice = $this->_currency->format($Products->getFinalPrice(), ['display'=>\Zend_Currency::NO_SYMBOL], false);
          	}
			if (!empty($this->checkConfigurableProduct($Products))) {	
			    $trending_category[] =[
	    			"entity_id"=> $Products->getId(),
	    			"name"=> $Products->getName(),
	    			"sku"=> $Products->getSku(),
	    			"image" 		=> $this->getProductsImage($Products),
	    			"mgs_brand"	=> $mgs_brand,
	    			"currency_code"	=> __($this->_storeManager->getStore()->getCurrentCurrency()->getCode()),
	    			"price" => $this->_currency->format($Products->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue(), ['display'=>\Zend_Currency::NO_SYMBOL], false),
                    "special_price" => $custom_specialPrice,     
                    "final_price" => $this->_currency->format($Products->getFinalPrice(), ['display'=>\Zend_Currency::NO_SYMBOL], false),
                    "discount" => $this->getRulesFromProduct($Products),
                    "product_label" => $this->getProductLabels($Products),
                    "is_wishlisted" => ($wishListFlag != '') ? $wishListFlag : 0,
                    "wishlist_item_id" => $wishListItemId,
                    "wishlist_id" => $WishlistId, 
                    "multi_buy_label" => $this->getMultiBuyLabel($Products)
	    		];
    		}
		}

		/*****************************  For Home Page offer Banner **************************/
		$offerCategoryId = $this->_storeConfig->getValue('homepagebanner/general_1/category_id_1', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$offerBannerImage = $this->_storeConfig->getValue('homepagebanner/general_1/category_image_id_1', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$fullUrl = $mediaUrl.'mobile_banner/'.$offerBannerImage;
		$offer_items_banner = array();
		$offer_items_banner[] = [
				'category_id' => $offerCategoryId,
				'banner_image' => $fullUrl,
		];

		/*****************************  For Color Collection Banner **************************/
		$colorCategoryId = $this->_storeConfig->getValue('homepagebanner/general_2/category_id_2', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$colorBannerImage = $this->_storeConfig->getValue('homepagebanner/general_2/category_image_id_2', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$fullUrl = $mediaUrl.'mobile_banner/'.$colorBannerImage;
		$color_collection_banner = array();
		$color_collection_banner[] = [
				'category_id' => $colorCategoryId,
				'banner_image' => $fullUrl,
		];

		/*****************************  For Summer Sale Banner **************************/
		$saleCategoryId = $this->_storeConfig->getValue('homepagebanner/general_3/category_id_3', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$saleBannerImage = $this->_storeConfig->getValue('homepagebanner/general_3/category_image_id_3', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$fullUrl = $mediaUrl.'mobile_banner/'.$saleBannerImage;
		$summer_sale_banner = array();
		$summer_sale_banner[] = [
				'category_id' => $saleCategoryId,
				'banner_image' => $fullUrl,
		];

		/*****************************  For Service Block Slider 1 **************************/
		$serviceTitle_1 = $this->_storeConfig->getValue('homepagebanner/service_block_slider_1/service_title', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$serviceDesc_1 = $this->_storeConfig->getValue('homepagebanner/service_block_slider_1/service_desc', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$serviceIcon_1 = $this->_storeConfig->getValue('homepagebanner/service_block_slider_1/service_icon', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$service_block_slider_1 = array();
		$service_block_slider_1 = [
				'title' => $serviceTitle_1,
				'description' => $serviceDesc_1,
				'icon' => $mediaUrl.'mobile_banner/'.$serviceIcon_1,
		];

		/*****************************  For Service Block Slider 2 **************************/
		$serviceTitle_2 = $this->_storeConfig->getValue('homepagebanner/service_block_slider_2/service_title', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$serviceDesc_2 = $this->_storeConfig->getValue('homepagebanner/service_block_slider_2/service_desc', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$serviceIcon_2 = $this->_storeConfig->getValue('homepagebanner/service_block_slider_2/service_icon', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$service_block_slider_2 = array();
		$service_block_slider_2 = [
				'title' => $serviceTitle_2,
				'description' => $serviceDesc_2,
				'icon' => $mediaUrl.'mobile_banner/'.$serviceIcon_2,
		];

		/*****************************  For Service Block Slider 3 **************************/
		$serviceTitle_3 = $this->_storeConfig->getValue('homepagebanner/service_block_slider_3/service_title', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$serviceDesc_3 = $this->_storeConfig->getValue('homepagebanner/service_block_slider_3/service_desc', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$serviceIcon_3 = $this->_storeConfig->getValue('homepagebanner/service_block_slider_3/service_icon', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$service_block_slider_3 = array();
		$service_block_slider_3 = [
				'title' => $serviceTitle_3,
				'description' => $serviceDesc_3,
				'icon' => $mediaUrl.'mobile_banner/'.$serviceIcon_3,
		];

		/*****************************  For Service Block Slider 4 **************************/
		$serviceTitle_4 = $this->_storeConfig->getValue('homepagebanner/service_block_slider_4/service_title', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$serviceDesc_4 = $this->_storeConfig->getValue('homepagebanner/service_block_slider_4/service_desc', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$serviceIcon_4 = $this->_storeConfig->getValue('homepagebanner/service_block_slider_4/service_icon', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$service_block_slider_4 = array();
		$service_block_slider_4 = [
				'title' => $serviceTitle_4,
				'description' => $serviceDesc_4,
				'icon' => $mediaUrl.'mobile_banner/'.$serviceIcon_4,
		];

		/*****************************  For Sort order **************************/
		$homepagecol['sort_by']['best_seller'] = $homeSortOrderBestSeller;
		$homepagecol['sort_by']['shop_under'] = $homeSortOrderShopUnder;
		$homepagecol['sort_by']['offer_items_banner'] = $homeSortOrderBanner_1;
		$homepagecol['sort_by']['see_more_categories'] = $homeSortOrderSeeMore;
		$homepagecol['sort_by']['muumy_me_category'] = $homeSortOrderMummyMe;
		$homepagecol['sort_by']['shop_by_brand'] = $homeSortOrderBrand;
		$homepagecol['sort_by']['color_collection_banner'] = $homeSortOrderBanner_2;
		$homepagecol['sort_by']['plus_size_category'] = $homeSortOrderPlusSize;
		$homepagecol['sort_by']['summer_sale_banner'] = $homeSortOrderBanner_3;
		$homepagecol['sort_by']['trending_category'] = $homeSortOrderTrending;
		$homepagecol['sort_by']['service_block'] = $sortOrderServiceIcon;
		asort($homepagecol['sort_by']);
		foreach($homepagecol['sort_by'] as $sortd=>$key){
			$homepagecollection['newsort_by'][]=	$sortd;
		} 

		/*****************************  For Top Menu *******************************/
		$homepagecollection['top_menu'] = $subCategoryDetails;

		/*****************************  For Banner Slider **************************/
		$homepagecollection['banner_slider'] = $banner_response;
		if ($enableBestSeller == 1) {
			$homepagecollection['best_seller']['id'] = $bestSellerCategoryId;
			$homepagecollection['best_seller']['bestseller_list'] = $bestSellers;
		}
		
		/*****************************  For Mummy and me category *******************/
		if ($enableHomeMummyMe == 1) {
			$homepagecollection['muumy_me_category']['category_id'] = $categoryIdmm;
			$homepagecollection['muumy_me_category']['category_name'] = $categoryNamemm;
			$homepagecollection['muumy_me_category']['list'] = $muumy_me_category;
		}
		
		/*****************************  For See more category ************************/
		if ($enableSeeMoreCategory == 1) {
			$homepagecollection['see_more_categories'] = $see_more_category;
		}
		/*****************************  For Shop By Brand ****************************/
		if ($homeShopByBrand == 1) {
			$homepagecollection['shop_by_brand'] = $listBrand;
		}

		/*****************************  For Plus size category ************************/
		if ($homePlusSize == 1) {
			$homepagecollection['plus_size_category']['category_id'] = $categoryIdpz;
			$homepagecollection['plus_size_category']['category_name'] = $categoryNamepz;
			$homepagecollection['plus_size_category']['list'] = $plus_size_category;
		}

		/********************************  For Trending ******************************/
		if ($homeTrending == 1) {
			$homepagecollection['trending_category']['category_id'] = $categoryIdTrending;
			$homepagecollection['trending_category']['category_name'] = $categoryNameTrending;
			$homepagecollection['trending_category']['list'] = $trending_category;
		}

		/********************************  For Shop under ******************************/
		if ($homeShopUnder) {	
			$homepagecollection['shop_under'] = $shop_under;
		}

		/********************************  For Offer Items Banner ***********************/
		$homepagecollection['offer_items_banner'] = $offer_items_banner;

		/****************************  For Color collection Banner ***********************/
		$homepagecollection['color_collection_banner'] = $color_collection_banner;
		
		/****************************  For Summer Sale Banner ****************************/
		$homepagecollection['summer_sale_banner'] = $summer_sale_banner;

		/**********************************  For Service Block  **************************/
		$homepagecollection['service_block']= [$service_block_slider_1,$service_block_slider_2,$service_block_slider_3,$service_block_slider_4];
	
		$homepagecollectionnew = ['0' =>['status'=> '200','message'=>'Homepage loading successfully.', 'data' =>$homepagecollection]];		
		return $homepagecollectionnew;  
	}

	/*****************************  get PlaceHolder Image  **************************/
    public function getPlaceHolderImage(){
        $mediaBaseUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA );
        $placeholderPath = $this->_storeConfig->getValue('catalog/placeholder/image_placeholder');//Base Image
        $fullUrl = $mediaBaseUrl.'catalog/product/placeholder/'.$placeholderPath;
        return $fullUrl;
    }

    protected function getRulesFromProduct($Products)
    {
    	 if ($Products->getTypeId() == 'configurable') {
            $basePrice = $Products->getPriceInfo()->getPrice('regular_price');
            $product_price = $basePrice->getMinRegularAmount()->getValue();
            $specialPrice = $Products->getFinalPrice();
            if($specialPrice < $product_price)
            { 
                $_savingPercent = 100 - round(($specialPrice / $product_price)*100); 
                if ($specialPrice) {
                    return $_savingPercent."% OFF";
                }
            }
        }
    }

    public function getProductLabels($Products){
        $html = '';
        $newLabel = $this->_storeConfig->getValue('mpanel/catalog/new_label', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $saleLabel = $this->_storeConfig->getValue('mpanel/catalog/sale_label', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $soldLabel = __('Out of Stock');
        // Out of stock label
        if (!$Products->isSaleable() || !$Products->isAvailable()){
            $html .= $soldLabel;
        }else {
            // New label
            $numberLabel = 0;
            $now = $this->_date->gmtDate();
            $dateTimeFormat = \Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT;
            $newFromDate = $Products->getNewsFromDate();
            if($newFromDate) {
                $newFromDate = date($dateTimeFormat, strtotime($newFromDate));
            }
            $newToDate = $Products->getNewsToDate();
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
            $price = $Products->getOrigData('price');
            $finalPrice = $Products->getFinalPrice();
            $fiPrice = $Products->getPriceInfo()->getPrice('final_price')->getValue();
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

    public function getProductsImage($Products)
    {
    	  return $this->imageHelper->init($Products, 'image')
                            ->setImageFile($Products->getImage())
                            ->constrainOnly(TRUE)
                            ->keepAspectRatio(TRUE)
                            ->keepTransparency(TRUE)
                            ->keepFrame(FALSE)
                            ->getUrl(); 
    }	
    public function checkConfigurableProduct($Products)
    {
    	/*For configurable product*/
	    $type = $Products->getTypeID();
        if($type == 'configurable'){
            $attributes = $Products->getTypeInstance()->getConfigurableOptions($Products);
            $checkArray = array();
            $sizeCheck = array();
            foreach ($attributes as $key => $options_val) {
                if(!empty($options_val)){
                foreach($options_val as $newkey => $value){
                    array_shift($value);
 					$salebleQty = $this->getSalableQuantityDataBySku->execute($options_val[0]['sku']);
					if(isset($salebleQty[0]) && isset($salebleQty[0]['qty']) && $salebleQty[0]['qty'] != 0){
                    if ($Products->isSaleable()) {
                        if(!in_array($value['option_title'],$sizeCheck)) {
                            $sizeCheck[] = $value['option_title'];
                            if($value['attribute_code'] == 'size'){
                                $swatchCollection   = $this->swatchCollection->create();
                                $swatchCollection->addFieldtoFilter('option_id',$value['value_index']);
                                $size_item     = $swatchCollection->getFirstItem();
                                $size_code     = array('size_code' => $size_item->getValue());
                                $qty_code     = array('qty_code' => $salebleQty[0]['qty']);
                                $checkArray[$key][] = array_merge($value,$size_code,$qty_code);
                            	return $checkArray;
                            } else {
                                $checkArray[$key][] = $value;   
                            	return $checkArray;
                            }
                          }
                        } 
                       }      
                    }
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
    private function getMultiBuyLabel($product)
    {
    	$multiBuyLabel = '';
    	// if($this->multiBuyHelper->isModuleEnable()) {
    	// 	$multiBuyLabel = $this->multiBuyHelper->getMultiBuyProductLabel($product, true);
    	// }

    	return $multiBuyLabel;
    }
}