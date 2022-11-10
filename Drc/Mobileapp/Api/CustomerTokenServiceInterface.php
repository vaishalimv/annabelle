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
interface CustomerTokenServiceInterface
{
    /**
     * Create access token for admin given the customer credentials.
     *
     * @param string $username
     * @param string $password
     * @param string $devicetype
     * @param string $devicetoken
     * @param string $deviceid
     * @return string Token created
     * @throws \Magento\Framework\Exception\AuthenticationException
     */
    public function createCustomerAccessToken($username, $password, $devicetoken, $devicetype ,$deviceid);

    /**
     * Revoke token by customer id.
     *
     * @param string $email
     * @param string $devicetype
     * @param string $devicetoken
     * @param string $deviceid
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function revokeCustomerAccessToken($email, $devicetoken, $devicetype ,$deviceid);
}
