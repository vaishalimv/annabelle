<?php
namespace Drc\Mobileapp\Api\Customer;
 
interface WebApiOrderInterface{
	/**
     * Returns quote id and total
     *
     * @param int $customer_id
     * @return mixed
     */
    public function getOrderList($customer_id);
}