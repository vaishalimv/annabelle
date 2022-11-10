<?php
namespace Drc\Mobileapp\Api\Category;
 
interface WebApiCategoryInterface{
    
    /**
     * Returns products
     *
     * @param int $category_id
     * @param string $customer_id
     * @param int $page_id
     * @param mixed $height
     * @param mixed $width
     * @param mixed $sortingfilter 
     * @return mixed
     */
    public function getCategoryFilterNewVersion($customer_id = '',$category_id,$page_id,$sortingfilter = null,$height, $width);    
    
    /**
     * Returns shop under products
     *
     * @param int $category_id
     * @param string $customer_id
     * @param int $page_id
     * @param mixed $height
     * @param mixed $width
     * @param mixed $sortingfilter 
     * @return mixed
     */
    public function getShopUnderProduct($customer_id = '',$category_id,$page_id,$sortingfilter = null,$height, $width); 


    /**
     * Returns All categories 
     *
     * @return mixed
     */
    public function getAllcategories();
     
}