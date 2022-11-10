<?php
namespace Drc\Mobileapp\Api\Brand;
 
interface WebApiBrandInterface{
	 
    /**
     * get all brand
     *
     * @return string
     */
    public function getAllBrand();

    /**
     * get all pro
     * @param int $option_id
	 * @param string $customer_id
	 * @param mixed $sort_by           
     * @param int $pageSize
     * @param int $page
     * @param mixed $height
     * @param mixed $width
     * @return mixed
     */
    public function getProductsByBrand($option_id, $customer_id = '',$sort_by = null, $pageSize, $page,$height,$width);
       
     /** 
     * @return string 
      * @param mixed $category_id
     */   
    public function getCategoryBrand($category_id);

}