<?php
namespace Drc\Mobileapp\Model\Customer;

use Drc\Mobileapp\Api\Customer\WebApiOrderInterface;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
 
class WebApiOrder implements WebApiOrderInterface
{
	 /**
     * @var CollectionFactory
     */
    private $orderCollectionFactory;

    protected $productRepository;

    protected $_storeManager;

    protected $_order;

    protected $_productRepositoryFactory;

    protected $_currency;

    protected $orderRepository;  
    
    public function __construct(CollectionFactory $orderCollectionFactory,
        \Magento\Catalog\Api\ProductRepositoryInterfaceFactory $productRepository,
        \Magento\Store\Model\StoreManagerInterface $storemanager,
        \Magento\Sales\Model\Order $order,
        \Magento\Catalog\Api\ProductRepositoryInterfaceFactory $productRepositoryFactory,
        \Magento\Directory\Model\Currency $currency,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
    ){
        $this->orderCollectionFactory    = $orderCollectionFactory;
        $this->productRepository         = $productRepository;
        $this->_storeManager             = $storemanager;
        $this->_order                    = $order;
        $this->_productRepositoryFactory = $productRepositoryFactory;
        $this->_currency                 = $currency;
        $this->orderRepository           = $orderRepository;
    }
  
	/**
     * Returns order collection
     *
     * @param int $customer_id
     * @return mixed
     */
	public function getOrderList($customer_id){
            
        $collection = $this->orderCollectionFactory->create()->addFieldToFilter('customer_id', $customer_id)->setOrder('created_at','desc');
        $response = array();
        $i = 0;
        if($collection->getSize() > 0){
            foreach ($collection as $order) {
                 $response['data'][$i] = [
                    'order_id' => $order->getEntityId(),
                    'created_at' => $order->getCreatedAt(),
                    'status' => $order->getStatus(),
                    'increment_id' => $order->getIncrementId(),
                    'grand_total' => $order->getGrandTotal(),  
                 ];                              
                /* Item Data */
                $order = $this->orderRepository->get($order->getEntityId());
                $items = $order->getAllVisibleItems();
                foreach ($items as $item) {
                    $product = $this->_productRepositoryFactory->create()->getById($item->getProductId());
                    $mediaUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'catalog/product';
                    
                        $response['data'][$i]['items'][] = [
                            'product_id' => $product->getId(),
                            'name' => $product->getName(),
                            'qty' => (int)$item->getQtyOrdered(),
                            'price' => $this->_currency->format($product->getPrice(), ['display'=>\Zend_Currency::NO_SYMBOL], false),
                            'special_price' => ($product->getSpecialPrice() != '') ? $this->_currency->format($product->getSpecialPrice(), ['display'=>\Zend_Currency::NO_SYMBOL], false) : '',
                            'final_price' => $this->_currency->format($product->getFinalPrice(), ['display'=>\Zend_Currency::NO_SYMBOL], false),
                            'image' => $mediaUrl.$product->getData('image')
                        ]; 
                }
                $i++;
            }
        }else{
            $response[] = ['message'=>'No order found!'];
        }   
        return $response;
    }
}