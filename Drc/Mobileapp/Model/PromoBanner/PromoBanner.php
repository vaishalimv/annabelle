<?php
namespace Drc\Mobileapp\Model\PromoBanner;

use Drc\Mobileapp\Api\PromoBanner\WebApiPromoaBannerInterface;

class PromoBanner implements WebApiPromoaBannerInterface
{
	protected $collectionFactory;

	protected $_storeManager;

	protected $_positionSource;

	public function __construct(
        \Amasty\PromoBanners\Model\ResourceModel\Rule\CollectionFactory $collectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Amasty\PromoBanners\Model\Source\Position $positionSource,
        array $data = [] 
    )
    {
    	$this->collectionFactory = $collectionFactory;
    	$this->_storeManager     = $storeManager;
    	$this->_positionSource = $positionSource;
    }

	  /**
     * Returns PromoBanner 
     * @api
	  * @param mixed $customer_id
	  * @param mixed banner_position
     * @return mixed
     */
    public function getPromoBanner($customer_id='',$banner_position){
    	$promoBanner = $this->collectionFactory->create();    	
    	$promoBanners = [];
    	$mediaUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA );
    	if($promoBanner->getData() != array()) {
	    	foreach($promoBanner as $banner){
	    		if ($banner->getBannerPosition() == $banner_position) {
		    		$promoBanners[] = [
		    			'id'              => $banner->getId(),
		    			'rule_name'       => $banner->getRuleName(),
		    			'is_active'       => $banner->getIsActive(),
		    			'sort_order'      => $banner->getSortOrder(),
		    			'stores'          => $banner->getStores(),
		    			'cust_groups'     => $banner->getCustGroups(),
		    			'banner_position' => $banner->getBannerPosition(),
		    			'banner_img'      => $mediaUrl.'amasty/ampromobanners/'.$banner->getBannerImg(),
		    			'banner_link'     => $banner->getBannerLink(),
		    			'banner_title'    => $banner->getBannerTitle(),
		    			'cms_block'       => $banner->getCmsBlock(),
		    			'cats_id'         => $banner->getCats(),
		    			'show_on_products'=> $banner->getShowOnProducts(),
		    			'banner_type'     => $banner->getBannerType(),
		    			'html_text'       => $banner->getHtmlText(),
		    			'show_products'   => $banner->getShowProducts(),
		    			'conditions_serialized' => json_decode($banner->getConditionsSerialized()),
		    		];
	    		}
	    	}
    		$promocollection = ['0' =>['status'=> '200','message'=>'Promo Banner loading successfully.', 'data' =>$promoBanners]];		
		}else{
		    $promocollection[] = ['status'=> '200','message'=>'No record found.', 'data' =>array()];
		}
		return $promocollection;	
    }

     /**
     * Returns PromoBannerPosition 
     * @api
	  * @param mixed $customer_id
     * @return mixed
     */
    public function getPromoBannerPosition($customer_id=''){
    	return $this->_positionSource->getPositionMulti();
    }
}