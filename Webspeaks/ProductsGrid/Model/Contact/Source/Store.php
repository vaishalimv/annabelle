<?php
/**
 * Drc_AmriHome extension
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 * 
 * @category  Drc
 * @package   Drc_AmriHome
 * @copyright Copyright (c) 2018
 * @license   http://opensource.org/licenses/mit-license.php MIT License
 */
namespace Webspeaks\ProductsGrid\Model\Contact\Source;
use \Magento\Store\Model\StoreRepository;

class Store implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var Rate
     */
    protected $_storeRepository;
      
    /**
     * @param StoreRepository      $storeRepository
     */
    public function __construct(
        StoreRepository $storeRepository
    ) {
        $this->_storeRepository = $storeRepository;
    }
   
    public function _toArray()
    {
        $stores = $this->_storeRepository->getList();
        $websiteIds = array();
        $storeList = array();
        foreach ($stores as $store) {
           // $websiteId = $store["website_id"];
            $storeId = $store["store_id"];
            $storeName = $store["name"];
            $storeList[$storeId] = $storeName;
            // array_push($websiteIds, $websiteId);
        }
        return $storeList;
    }
    public function toOptionArray()
    {
        $arr = $this->_toArray();
        $ret = [];

        foreach ($arr as $key => $value)
        {
            $ret[] = [
                'value' => $key,
                'label' => $value
            ];
        }
       //array_shift($ret);    
        return $ret;
    }
}
