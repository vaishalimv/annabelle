<?php
namespace Drc\Mobileapp\Api\Forceupdate;

interface SystemconfigManagementInterface
{

    /**
     * GET for getSystemconfig api
     * @param string $storeCode
     * @param string $path
     * @return string[]
     */
    public function getForceUpdateConfiguration();
}