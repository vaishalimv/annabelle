<?php
namespace Drc\Mobileapp\Model\Loyalty;

class LoyaltyVoucher implements \Drc\Mobileapp\Api\Loyalty\LoyaltyVoucher
{
    /**
     * @var \Fledge\Loyalty\Helper\Data
     */
    protected $helper;

    /**
     * @var \Drc\Mobileapp\Helper\Data
     */
    protected $mobileAppHelper;
	
    /**
     * Redeem voucher constructor
     *
     * @param \Fledge\Loyalty\Helper\Data $helper
     * @param \Drc\Mobileapp\Helper\Data $mobileAppHelper
     */
	public function __construct(
        \Fledge\Loyalty\Helper\Data $helper,
        \Drc\Mobileapp\Helper\Data $mobileAppHelper
	) {
        $this->helper = $helper;
        $this->mobileAppHelper = $mobileAppHelper;
	}

	/**
     * Redeem voucher
     *
     * @param  mixed $redeemPoints
     * @return array
     */
    public function redeemVoucher($redeemPoints)
    {
		if(isset($redeemPoints['customer_id']) && !empty($redeemPoints['customer_id'])) {
            $customer = $this->helper->getCustomerById($redeemPoints['customer_id']);
            if($customer && $customer->getId()) {
                $customerBarcode = $customer->getCustomAttribute('barcode')->getValue();
                $userID = $customer->getCustomAttribute('user_id')->getValue();
                $mobileNumber = $customer->getCustomAttribute('mobile_number')->getValue();
                $dialCode = $customer->getCustomAttribute('mobile_country_code')->getValue();
                $mobileCountryCode = str_replace('+','',$dialCode);
                $points = (isset($redeemPoints['points']) && !empty($redeemPoints['points'])) ? $redeemPoints['points']: '';

                if(!empty($customerBarcode) && !empty($userID) && !empty($mobileNumber) && !empty($mobileCountryCode) && !empty($points)) {
                    $mobileNumber = $mobileCountryCode.$mobileNumber;
                    $voucherDetails = $this->helper->getRedeemVoucher($mobileNumber, $customerBarcode, $userID, $points, $customer);

                    if($voucherDetails) {
                        $message = __('You redeem the %1 points', $points);
                        $response[] = ['status'=> '200', 'success' => true, 'message' => $message];
                    } else {
                        $message = __('Something went wrong while redeem the points.');
                        $response[] = ['status'=> '400', 'success' => false, 'message' => $message];
                    }
                } else {
                    $message = __("You havn't opted loyalty programm yet.");
                    $response[] = ['status'=> '400', 'success' => false, 'message' => $message];
                }

            } else {
                $message = __("Requested customer doesn't exist.");
                $response[] = ['status'=> '400', 'success' => false, 'message' => $message];
            }
        } else {
            $message = __("Customer data not found.");
            $response[] = ['status'=> '400', 'success' => false, 'message' => $message];
        }

        return $response;
    }

    /**
     * Get active vouchers
     *
     * @param  mixed $customerId
     * @return array
     */
    public function getActiveVouchers($customerId)
    {
        if($customerId) {
            $customer = $this->helper->getCustomerById($customerId);
            if($customer && $customer->getId()) {
                $activeCards = $this->mobileAppHelper->getCardsFront($customerId);

                if(!empty($activeCards)) {
                    $message = __('');
                    $response[] = ['status'=> '200', 'success' => true, 'message' => $message, 'data' => $activeCards];
                } else {
                    $message = __("There is no any active vouchers.");
                    $response[] = ['status'=> '400', 'success' => false, 'message' => $message, 'data' => []];
                }

            } else {
                $message = __("Requested customer doesn't exist.");
                $response[] = ['status'=> '400', 'success' => false, 'message' => $message, 'data' => []];
            }
        } else {
            $message = __("Customer data not found.");
            $response[] = ['status'=> '400', 'success' => false, 'message' => $message, 'data' => []];
        }

        return $response;
    }

    /**
     * Update offline used voucher
     *
     * @param  string $voucherCode
     * @return array
     */
    public function updateOfflineUsedVouchers($voucherCode)
    {
        if(!empty($voucherCode)) {
            $isVoucherStatusUpdate = $this->helper->updateOfflineUsedVouhcerStatus($voucherCode);

            if($isVoucherStatusUpdate) {
                $message = __('');
                $response[] = ['status'=> '200', 'success' => true, 'message' => $message];
            } else {
                $message = __('Something went wrong while update offline used voucher status.');
                $response[] = ['status'=> '400', 'success' => true, 'message' => $message];
            }    
        } else {
            $message = __('Voucher not found to update the status.');
            $response[] = ['status'=> '400', 'success' => true, 'message' => $message];
        }

        

        return $response;
    }

    /**
     * Update used voucher after checkout
     *
     * @param  string $orderID
     * @param  string $customerID
     * @return void
     */
    public function updateUsedVouhcer($orderID, $customerID) {
        if($this->helper->isModuleEnabled() && $orderID && $customerID) {
            $this->helper->updateRedeemVouhcer($orderID, $customerID);
        } else {
            $message = __('Voucher not found to update the status.');
            $response[] = ['status'=> '400', 'success' => true, 'message' => $message];
        }
    }
}