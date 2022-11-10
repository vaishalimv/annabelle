<?php
namespace Drc\Mobileapp\Model\Homepage;
use Drc\Mobileapp\Api\Homepage\WebApiBottomMenuInterface;

class WebApiBottomMenu implements WebApiBottomMenuInterface
{	
	protected $_currency;
	protected $_storeConfig;
	protected $_storeManager;
	protected $_countryFactory;

	public function __construct(
        \Magento\Directory\Model\Currency $currency,
        \Magento\Store\Model\StoreManagerInterface $storemanager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Directory\Model\CountryFactory $countryFactory
    ) {
		$this->_storeManager 	= $storemanager;
        $this->_storeConfig 	= $scopeConfig;
		$this->_currency 	    = $currency;
		$this->_countryFactory  = $countryFactory;
	}
	
	public function getHomepageBottomMenu($customer_id = '',$country_code= "",$store_language= ""){
		$mediaUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

		/* Sidebar Settings */
		$country = $this->_countryFactory->create()->loadByCode($country_code); 
		$countryName = $country->getName();
		$country_flag = $mediaUrl .'storeflag/'. $country_code.'.svg'; 
		$currencyCode = $this->_storeManager->getStore()->getCurrentCurrency()->getCode();

		/* Sidebar Need Assistance */
		$sidebarWhatsapp = $this->_storeConfig->getValue('sidebardashboard/store_general/whatsapp_number', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$sidebarEmail = $this->_storeConfig->getValue('sidebardashboard/store_general/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

		/* Sidebar setting */
		$bottomMenuCollection['settings']['store_language']         = $store_language;
		$bottomMenuCollection['settings']['country_name']           = $countryName;
		$bottomMenuCollection['settings']['country_code']           = $country_code;
		$bottomMenuCollection['settings']['country_flag']           = $country_flag;
		$bottomMenuCollection['settings']['currency']               = $currencyCode;
		
		/* Sidebar Need assistance */
		$bottomMenuCollection['need_assistance']['whatsapp_number'] = $sidebarWhatsapp;
		$bottomMenuCollection['need_assistance']['email']           = $sidebarEmail;
		$bottomMenuCollection['need_assistance']['contact_us']      = 'contact-us-mobile';
		
		/* Sidebar about cms page*/
		$bottomMenuCollection['about_annabelle']['our_story']       = 'our-story-mobile';
		$bottomMenuCollection['about_annabelle']['store_locator']   = 'stores-mobile';
		$bottomMenuCollection['help_centre']['size_guide']          = 'size-guide-mobile';
		$bottomMenuCollection['help_centre']['privacy_policy']      = 'privacy-policy-mobile';
		$bottomMenuCollection['help_centre']['shipping_info']      	= 'shipping-delivery-mobile';
		$bottomMenuCollection['help_centre']['refunds_return']      = 'return-and-refund-mobile';
		$bottomMenuCollection['help_centre']['payments']            = 'payment-mobile';
				
		$bottomMenucollectionnew = ['0' =>['status'=> '200','message'=>'Sidebar Bottom loading successfully.', 'data' =>$bottomMenuCollection]];		
		return $bottomMenucollectionnew;  
	}
	
		
}