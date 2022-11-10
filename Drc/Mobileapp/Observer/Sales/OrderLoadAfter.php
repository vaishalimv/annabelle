<?php
namespace Drc\Mobileapp\Observer\Sales;
use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Api\ProductRepositoryInterfaceFactory as ProductRepository;
use Magento\Sales\Api\Data\OrderItemExtensionFactory;
use Magento\Store\Model\StoreManagerInterface as StoreManager;
use Magento\Sales\Api\Data\OrderAddressExtensionInterfaceFactory;
class OrderLoadAfter implements ObserverInterface
{
    protected $productRepository;
    protected $storeManager;
    protected $extensionFactory;
    protected $countryFactory;
    protected $addressExtensionInterfaceFactory;
    protected $_currency;
    protected $_storeConfig;

    public function __construct(
        ProductRepository $productRepository,
        StoreManager $storeManager,
        OrderItemExtensionFactory $extensionFactory,
        OrderAddressExtensionInterfaceFactory $addressExtensionInterfaceFactory,
        \Magento\Directory\Model\Currency $currency,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
        ){
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        $this->extensionFactory = $extensionFactory;
        $this->countryFactory= $countryFactory;
        $this->addressExtensionInterfaceFactory = $addressExtensionInterfaceFactory;
        $this->_currency = $currency;
        $this->_storeConfig = $scopeConfig;

    }
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getOrder();
        $store = $this->storeManager->getStore();
        $extensionAttributes = $order->getExtensionAttributes();    
        $orderExtension = $extensionAttributes ? $extensionAttributes : $this->orderExtensionFactory->create(); 
        $orderExtension->setStatusLabel(__($order->getStatusLabel()));  
        $orderExtension->setCurrencyCode(__($store->getCurrentCurrency()->getCode()));  
        $order->setExtensionAttributes($orderExtension);

        $orderItems = $order->getAllItems();
        
        foreach ($orderItems as $item) {
            $extensionAttributes = $item->getExtensionAttributes();
            if ($extensionAttributes === null) {
                $extensionAttributes = $this->getOrderItemExtensionDependency();
            }
            $productData = $this->productRepository->create()->get($item->getSku());

            $productsize='';
            if($productData->getSize()){
                $productsize = $productData->getResource()->getAttribute('size')->getFrontend()->getValue($productData);
            }

            $short_description = ($productData->getDescription()) ? $productData->getDescription() : '' ;
            
            // end
            if($productData->getThumbnail() != ''){
                $productImageUrl = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' .$productData->getThumbnail(); 
            }else{
                $productImageUrl= $this->getPlaceHolderImage();
            }
            /* Country Id to country Name */
            $shippingCountryId = $order->getShippingAddress()->getData();
            $shippingCountryIdCode = $shippingCountryId['country_id'];
            $billingCountryId = $order->getBillingAddress()->getData();
            $billingCountryIdCode = $billingCountryId['country_id']; 
      
            $shippingCountryName = $this->countryFactory->create()->loadByCode($shippingCountryIdCode);
            $billingCountryName = $this->countryFactory->create()->loadByCode($billingCountryIdCode);
            $extensionAttributes->setImage($productImageUrl);
            $extensionAttributes->setShortDescription($short_description);
            $extensionAttributes->setCurrencyCode(__($store->getCurrentCurrency()->getCode()));
            $extensionAttributes->setSize(__($productsize));
            $extensionAttributes->setShippingCountryName($shippingCountryName->getName());
            $extensionAttributes->setBillingCountryName($billingCountryName->getName());
            $item->setExtensionAttributes($extensionAttributes);   
        }
    }
    public function getPlaceHolderImage(){
        $mediaBaseUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA );
        $placeholderPath = $this->_storeConfig->getValue('catalog/placeholder/image_placeholder');//Base Image
        $fullUrl = $mediaBaseUrl.'catalog/product/placeholder/'.$placeholderPath;
        return $fullUrl;
    }
}
