<?php
namespace Drc\Mobileapp\Api\Homepage;
 
interface WebApiHomepageInterface{
    /** 
     * @return string 
	  * @param mixed $customer_id
     */
    public function getHomepageMergerFunction($customer_id);
}