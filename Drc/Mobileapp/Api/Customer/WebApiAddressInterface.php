<?php
namespace Drc\Mobileapp\Api\Customer;
 
interface WebApiAddressInterface{ 
    /**
     * Returns 
     * @api
     * @param mixed $address
     * @return array
     */
    public function saveAddress($address);
	
	/**
     * Returns 
     * @api
     * @param int $customer_id
     * @return array
     */
    public function getCustomerAddress($customer_id);
    
}