<?php
namespace Drc\Mobileapp\Model\Customer;
use Drc\Mobileapp\Api\Customer\WebApiCustomerInterface;

class CustomerDetails implements WebApiCustomerInterface
{
    protected $_customerRepositoryInterface;
    protected $_addressRepository;
    public function __construct(
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository
        
    ) {
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
        $this->_addressRepository = $addressRepository;
    }
    
    public function getCustomerDetails($customer_id)
    {
        $ary_response = [];
        $customer = $this->_customerRepositoryInterface->getById($customer_id);
        if($customer->__toArray() != array()){
            
            $billingAddressId = $customer->getDefaultBilling();
            $billingAddress = $this->_addressRepository->getById($billingAddressId);
            
            $shippingAddressId = $customer->getDefaultShipping();
            $shippingAddress = $this->_addressRepository->getById($shippingAddressId);
            
            $valid = [
                "customer"=>$customer->__toArray(),
            ];
            $response[] = ['status'=> '200','message'=>'successfully loaded customer.', 'data' =>$valid];
        }else{
            $response[] = ['status'=> '200','message'=>'Customer Not Found !!', 'data' =>array()];
        }
        return $response;
    }    
}
