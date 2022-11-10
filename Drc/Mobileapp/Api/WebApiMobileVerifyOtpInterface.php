<?php

namespace Drc\Mobileapp\Api;
 
interface WebApiMobileVerifyOtpInterface{

    /**
     * Returns verify otp
     *
     * @api 
     * @param mixed $verifyMobileOtp
     * @return array[]
     */ 
    public function mobileVerifyOtp($verifyMobileOtp); 
}
