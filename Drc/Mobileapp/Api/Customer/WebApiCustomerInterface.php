<?php
namespace Drc\Mobileapp\Api\Customer;
 
interface WebApiCustomerInterface{ 
    /**
     * Returns 
     * @api
     * @param int $customer_id
     * @return array
     */
    public function getCustomerDetails($customer_id);
    
}