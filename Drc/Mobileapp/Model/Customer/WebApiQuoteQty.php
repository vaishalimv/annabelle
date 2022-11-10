<?php
namespace Drc\Mobileapp\Model\Customer;
use Drc\Mobileapp\Api\Customer\WebApiQuoteQtyInterface;
 
class WebApiQuoteQty implements WebApiQuoteQtyInterface
{
	protected $quoteFactory;
    protected $storeManager;
    protected $customerRepository;
    protected $cartRepositoryInterface;
    protected $cartManagementInterface;
    
    public function __construct(\Magento\Quote\Model\QuoteFactory $quoteFactory,
    \Magento\Store\Model\StoreManagerInterface $storeManager,
    \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
    \Magento\Quote\Api\CartRepositoryInterface $cartRepositoryInterface,
    \Magento\Quote\Api\CartManagementInterface $cartManagementInterface
    ){
        $this->quoteFactory = $quoteFactory;
        $this->storeManager = $storeManager;
        $this->customerRepository = $customerRepository;
        $this->cartRepositoryInterface = $cartRepositoryInterface;
        $this->cartManagementInterface = $cartManagementInterface;
    }
  
	/**
     * Returns quote id and total
     *
     * @param int $customer_id
     * @return mixed
     */
	public function getQuoteQty($customer_id = ''){

       $store = $this->storeManager->getStore();
       $quoteCollection = $this->quoteFactory->create()->getCollection();
       $quoteCollection->addFieldToFilter('customer_id',$customer_id);
       $quoteCollection->addFieldToFilter('is_active', 1);
       
       if($quoteCollection->getSize() > 0){
            $quote = $quoteCollection->getFirstItem();
            $response = [
                'quote_id'=> (int)$quote->getId(),
                'quote_total'=> (int)$quote->getItemsQty(),
                'quote_items_count'=> (int)$quote->getItemsCount()
            ];  
       }else{
           //init the quote
            $cart_id = $this->cartManagementInterface->createEmptyCart();
            $cart = $this->cartRepositoryInterface->get($cart_id);
            $cart->setStore($store);
    
            // if you have already had the buyer id, you can load customer directly
            $customer= $this->customerRepository->getById($customer_id);
            $cart->setCurrency();
            $cart->assignCustomer($customer); //Assign quote to customer
            $cart->save();

            $quoteCollection->addFieldToFilter('customer_id',$customer_id);
            $quoteCollection->addFieldToFilter('is_active', 1);

            $quote = $quoteCollection->getFirstItem();
            $response = [
                'quote_id'=> (int)$quote->getId(),
                'quote_total'=> (int)$quote->getItemsQty(),
                'quote_items_count'=> (int)$quote->getItemsCount()
            ];  
       }
       $responseData = ['0' =>['status'=> '200','message'=>'Quote loading successfully.', 'data' =>$response]];
       return $responseData;
    }
}
