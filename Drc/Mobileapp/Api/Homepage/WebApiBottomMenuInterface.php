<?php
namespace Drc\Mobileapp\Api\Homepage;
 
interface WebApiBottomMenuInterface{
    /** 
     * @return string 
	  * @param mixed $customer_id
      * @param mixed $country_code
      * @param mixed $store_language
     */      
    public function getHomepageBottomMenu($customer_id,$country_code,$store_language);
}