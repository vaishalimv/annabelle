<?php

namespace Drc\Mobileapp\Plugin\Customer\Block\Widget;
class Name
{
    public function after_construct(\Magento\Customer\Block\Widget\Name $result)
    {
        $result->setTemplate('Drc_Mobileapp::widget/name.phtml');
        return $result;
    }
}
?>