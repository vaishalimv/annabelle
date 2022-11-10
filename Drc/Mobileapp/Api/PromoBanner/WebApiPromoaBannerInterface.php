<?php
namespace Drc\Mobileapp\Api\PromoBanner;
 
interface WebApiPromoaBannerInterface{
    /**
     * Returns PromoBanner 
     * @api
	  * @param mixed $customer_id
      * @param mixed $banner_position
     * @return mixed
     */
    public function getPromoBanner($customer_id='',$banner_position);	


     /**
     * Returns PromoBannerPosition 
     * @api
      * @param mixed $customer_id
     * @return mixed
     */
    public function getPromoBannerPosition($customer_id='');
}