<?php
namespace Drc\Mobileapp\Model\Config\Source;

class CategoryAttribute extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = [
                ['value' => 'circle', 'label' => __('Circle')], 
                ['value' => 'square', 'label' => __('Square')]
            ];
        }
        return $this->_options;
    }
}
