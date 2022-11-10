<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Drc\Mobileapp\Api;

/**
 * Interface providing token generation for Customers
 *
 * @api
 */
interface UpdateTokenInterface
{
    /**
     * Create access token for admin given the customer credentials.
     *
     * @param string $deviceid
     * @param string $devicetoken
     * @return string Token created
     * @throws \Magento\Framework\Exception\AuthenticationException
     */
    public function UpdateDeviceToken($deviceid, $devicetoken);
}
