<?php
namespace Drc\Mobileapp\Model\Category;
 
class DataProvider extends \Magento\Catalog\Model\Category\DataProvider
{
 
	protected function getFieldsMap()
	{
    	$fields = parent::getFieldsMap();
        $fields['content'][] = 'category_mobile_banner'; // custom image field
    	
    	return $fields;
	}
}