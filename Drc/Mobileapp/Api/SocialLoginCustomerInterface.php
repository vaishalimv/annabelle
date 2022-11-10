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
interface SocialLoginCustomerInterface
{
      /**
     * Returns greeting message to user
     *
     * @api
     * @param string $first_name Users first name.
     * @param string $last_name Users last name.
     * @param string $email Users email.
     * @return string Token created
     */
    public function socialLogin();
}