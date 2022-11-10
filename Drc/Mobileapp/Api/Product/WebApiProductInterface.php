<?php
namespace Drc\Mobileapp\Api\Product;
 
interface WebApiProductInterface{
     /**
     * GET product by it ID
     *
     * @api
     * @param mixed $customer_id
     * @param string $id
     * @param mixed $height
     * @param mixed $width
     * @return \Magento\Catalog\Api\Data\ProductInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
	 * 
	 */
    public function getDetails($customer_id,$product_id,$height,$width);
    
    /**
     * GET Related Products by it ID
     *
     * @api
     * @param mixed $customer_id
     * @param string $product_id
     * @param mixed $height
     * @param mixed $width
     * @return \Magento\Catalog\Api\Data\ProductInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
	 * 
	 */
	public function getRelatedProducts($customer_id,$product_id,$height,$width);	
}