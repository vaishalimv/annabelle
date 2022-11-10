<?php
namespace Drc\Mobileapp\Model\MobileOtp;

use Drc\Mobileapp\Api\WebApiIsMobileNumberVerified;

class IsMobileNumberVerified implements WebApiIsMobileNumberVerified
{
	protected $customerFactory;
	
	public function __construct(
    \Magento\Customer\Model\CustomerFactory $customerFactory
	) {
    	$this->customerFactory = $customerFactory;
	}

	/**
     * Returns ismobileLoyality
     *
     * @api 
     * @param int $customerId
     * @return mixed
     */ 

    public function isMobileLoyality($customerId)
    {	
		$customer = $this->customerFactory->create()->load($customerId);
        $isMobileNumberVerified = $customer->getIsMobileNumberVerified();
        $isOptedLoyaltyProgram = $customer->getOptedLoyaltyProgram();
    	if($isMobileNumberVerified && !$isOptedLoyaltyProgram || empty($isOptedLoyaltyProgram)) {
    		return false;
    	}else{
    		return true;
    	}
    }
}