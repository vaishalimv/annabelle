<?php
namespace Drc\Mobileapp\Api\Customer;
 
interface WebApiQuoteQtyInterface{
	/**
     * Returns quote id and total
     *
     * @param int $customer_id
     * @return mixed
     */
    public function getQuoteQty($customer_id);
}