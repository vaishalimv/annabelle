<?php

namespace Drc\Mobileapp\Model\ForceUpdate;

class SystemconfigManagement implements \Drc\Mobileapp\Api\Forceupdate\SystemconfigManagementInterface
{

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;


    public function __construct(
     \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
     \Magento\Store\Model\StoreManagerInterface $storeManager       
     )
    {
         $this->scopeConfig = $scopeConfig;
         $this->storeManager = $storeManager;
    }
    /**
     * {@inheritdoc}
     */
    public function getForceUpdateConfiguration()
    {
        $storeCode = $this->storeManager->getStore()->getCode();
        $storeConfigFinaData = [];

        $arrayPaths = [
            ["path" => "forceupdate/force_update/force_update_title"],
            ["path" => "forceupdate/force_update/force_update_body"],
            ["path" => "forceupdate/force_update/force_update_android_version"],
            ["path" => "forceupdate/force_update/force_update_ios_version"],
            ["path" => "forceupdate/force_update/is_force_update"],
            ["path" => "forceupdate/force_update/force_update_google_playstore_link"],
            ["path" => "forceupdate/force_update/force_update_ios_playstore_link"],
        ];

        foreach ($arrayPaths as $paths) {
            $strArray = explode('/',$paths['path']);
            $lastElement = end($strArray);
            $storeConfigFinaData[0][$lastElement] = $this->scopeConfig
                   ->getValue(
                       $paths['path'],
                       \Magento\Store\Model\ScopeInterface::SCOPE_STORES,
                       $storeCode
                    );
        }
        return $storeConfigFinaData;
    }
}