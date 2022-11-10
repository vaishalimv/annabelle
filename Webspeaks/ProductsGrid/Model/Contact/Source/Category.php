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

class Category implements \Magento\Framework\Option\ArrayInterface
{
    protected $_categoryFactory;
    protected $_categoryCollectionFactory;

    public function __construct(
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
    )
    {
        $this->_categoryFactory = $categoryFactory;
        $this->_categoryCollectionFactory = $categoryCollectionFactory;
    }

    public function getCategoryCollection($isActive = true, $level = false, $sortBy = false, $pageSize = false)
    {
        $collection = $this->_categoryCollectionFactory->create();
        $collection->addAttributeToSelect('*')->addAttributeToFilter('include_in_menu', 1);

        // select only active categories
        if ($isActive) {
            $collection->addIsActiveFilter();
        }

        // select categories of certain level
        if ($level) {
            $collection->addLevelFilter($level);
        }

        // sort categories by some value
        if ($sortBy) {
            $collection->addOrderField($sortBy);
        }

        // select certain number of categories
        if ($pageSize) {
            $collection->setPageSize($pageSize);
        }

        return $collection;
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
        array_shift($ret);
        return $ret;
    }

    private function _toArray()
    {
        $categories = $this->getCategoryCollection(true, false, false, false);

        $catagoryList = array();
        foreach ($categories as $category)
        {
            $catagoryList[$category->getEntityId()] = __($this->_getParentName($category->getPath()) . $category->getName());
        }

        return $catagoryList;
    }

    private function _getParentName($path = '')
    {
        $parentName = '';
        $rootCats = array(1,2);

        $catTree = explode("/", $path);
        // Deleting category itself
        array_pop($catTree);

        if($catTree && (count($catTree) > count($rootCats)))
        {
            foreach ($catTree as $catId)
            {
                if(!in_array($catId, $rootCats))
                {
                    $category = $this->_categoryFactory->create()->load($catId);
                    $categoryName = $category->getName();
                    $parentName .= $categoryName . ' -> ';
                }
            }
        }

        return $parentName;
    }

}
