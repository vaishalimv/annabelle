<?php

namespace Drc\Mobileapp\Api\Loyalty;
 
interface LoyaltyVoucher
{

    /**
     * Redeem voucher
     *
     * @api 
     * @param mixed $redeemPoints
     * @return array[]
     */ 
    public function redeemVoucher($redeemPoints);

    /**
     * Get active vouchers
     *
     * @api 
     * @param mixed $customerId
     * @return array[]
     */ 
    public function getActiveVouchers($customerId);

    /**
     * Update offline used voucher
     *
     * @param  string $voucherCode
     * @return array
     */
    public function updateOfflineUsedVouchers($voucherCode);

    /**
     * Update used voucher after checkout
     *
     * @param  string $orderID
     * @param  string $customerID
     * @return void
     */
    public function updateUsedVouhcer($orderID, $customerID);

}
