<?php
namespace Drc\Mobileapp\Api\Category\Products;
 
interface WebApiProductsInterface{
	 /**
     * Returns products
     *
     * @param int $customer_id
     * @param int $category_id
	 * @param mixed $sort_by 
	 * @param int $page
	 * @param int $pageSize
     * @return mixed
     */
    public function getCategoryProducts($customer_id,$category_id,$sort_by,$page,$pageSize);
        
}