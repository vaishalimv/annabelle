<?php
namespace Drc\Mobileapp\Model\Category\Products;
use Drc\Mobileapp\Api\Category\Products\WebApiProductsInterface;
use MGS\Mpanel\Helper\Data;

class WebApiProducts implements WebApiProductsInterface
{
	protected $_productCollectionFactory;
	
	protected $_categoryFactory;
	
	protected $_storeManager;
	
	protected $_currency;
	
	protected $wishlist;
	
	public $priceHelper;
	
	protected $_storeConfig;

	protected $_date;

	public function __construct(
	    \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
	    \Magento\Catalog\Model\CategoryFactory $categoryFactory,
	    \Magento\Store\Model\StoreManagerInterface $storeManager,
	    \Magento\Directory\Model\Currency $currency,
	    \Magento\Wishlist\Model\Wishlist $wishlist,
		\Magento\Framework\Pricing\Helper\Data $priceHelper,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Framework\Stdlib\DateTime\DateTime $date
	) {
	    $this->_categoryFactory = $categoryFactory;
	    $this->_productCollectionFactory = $productCollectionFactory;
	    $this->_storeManager = $storeManager;
	    $this->_currency = $currency;
	    $this->_reviewRatingFactory = $reviewRatingFactory;
	    $this->wishlist = $wishlist;
		$this->priceHelper  = $priceHelper;
		$this->_storeConfig = $scopeConfig;
		$this->_date = $date;
	}
    /**
     * Returns products
     *
     * @param int $category_id
	 * @param mixed $sort_by
     * @return mixed
     */
    public function getCategoryProducts($customer_id = '',$category_id,$sort_by = null,$page,$pageSize){
	    
	    $baseUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'catalog/product';
	    $category = $this->_categoryFactory->create()->load($category_id);
	    $collections = $this->_productCollectionFactory->create();
	    $collections->addAttributeToSelect('*');
	    $collections->addCategoryFilter($category);
	    $collections->addAttributeToFilter('visibility', \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH);
	    $collections->addAttributeToFilter('status',\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
	    
	    /* For Product Filter */
	    $sortBy = '';
		if($sort_by != null){
			if(isset($sort_by) && $sort_by == '0'){
				$collections->addAttributeToSort('price', 'ASC');
				$sortBy = 'Price - ASC';
			}else if(isset($sort_by) && $sort_by == '1'){
				$collections->addAttributeToSort('price', 'DESC');
				$sortBy = 'Price - DESC';
			}else if(isset($sort_by) && $sort_by == '2'){
			    $collections->addAttributeToSort('entity_id', 'DESC');
			    $sortBy = 'New';
			}else if(isset($sort_by) && $sort_by == '3'){
			    $collections->addAttributeToSort('name', 'ASC');
			    $sortBy = 'name - ASC';
			}else if(isset($sort_by) && $sort_by == '4'){
			    $collections->addAttributeToSort('name', 'DESC');
			    $sortBy = 'name - DESC';
			}
		}
	    $collections->setPageSize($pageSize);
        $collections->setCurPage($page);
        
        $pages = $collections->getLastPageNumber();
        if($page > $pages){
            $collections->clear();
        }
        
		$currentStoreId = $this->_storeManager->getStore()->getId();
		$l = 0;
		if($collections->getData()){
		    
		    $asa['total_count'] = count($collections);
		    $asa['pagination_status'] =  $page." of ".$collections->getLastPageNumber();
		    $asa['is_next'] = ($page < $pages) ? true : false;
		    
		    foreach($collections as $product)
		    {
				if($product->isSalable()) {
	 	            $asa['product'][$l]['product_id'] = $product->getId();
					$asa['product'][$l]['name'] = $product->getName();
					$asa['product'][$l]['sku'] = $product->getSku();
					$asa['product'][$l]['image'] = $baseUrl.$product->getImage();
					$asa['product'][$l]['small_image'] = $baseUrl.$product->getSmallImage();
					$asa['product'][$l]['thumbnail'] = $baseUrl.$product->getThumbnail();
					$asa['product'][$l]['url'] = $product->getProductUrl();
					$asa['product'][$l]['type'] = $product->getTypeId();
					$asa['product'][$l]['price'] = ($product->getPrice() != '') ? $this->priceHelper->currency($product->getPrice(), true, false) : $this->priceHelper->currency(0.00, true, false);
					$asa['product'][$l]['special_price'] = ($product->getSpecialPrice() != '') ? $this->priceHelper->currency($product->getSpecialPrice(), true, false) : $this->priceHelper->currency(0.00, true, false);
					$asa['product'][$l]['final_price'] = $this->priceHelper->currency($product->getFinalPrice(), true, false);
					$asa['product'][$l]['product_label'] = $this->getProductLabels($product);
				    /* For Brand Attribute Value */
				    $optionValue = ($product->getMgsBrand() != '') ? $product->getMgsBrand() : '';
				    if($optionValue != ''){
				        $attribute = $product->getResource()->getAttribute('mgs_brand');
				        $mgs_brand_lable = '';
	                    if ($attribute->usesSource()) {
	                        $mgs_brand_lable = $attribute->getSource()->getOptionText($optionValue);
	                    }
				        $asa['product'][$l]['mgs_brand'] = $mgs_brand_lable;    
				    }else{
				        $asa['product'][$l]['mgs_brand'] = '';
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
	                $asa['product'][$l]['is_wishlisted'] = ($wishListFlag != '') ? $wishListFlag : 0;
	                $asa['product'][$l]['wishlist_item_id'] = $wishListItemId;
	                $asa['product'][$l]['wishlist_id'] = $WishlistId;
	        $l++;
			}
		}
		$product_respone = $asa;
		$response[] = ['status'=> '200','message'=>'successfully loaded category products.', 'data' =>$product_respone];
		} else {		    
		    $asa['product'] = array();
		    $product_respone  = $asa;
		    $response[] = ['status'=> '200','message'=>'category products not found!!.', 'data' =>$product_respone];
		}
		return $response;		
	}
	public function getProductLabels($product){
		$html = '';
		$newLabel = $this->_storeConfig->getValue('mpanel/catalog/new_label', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$saleLabel = $this->_storeConfig->getValue('mpanel/catalog/sale_label', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$soldLabel = __('Out of Stock');
		/* Out of stock label */
		if (!$product->isSaleable() || !$product->isAvailable()){
			$html .= $soldLabel;
		}else {
			/* New label*/
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
			/* Sale label*/
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
}
