<?php

namespace Drc\Mobileapp\Api;
 
interface WebApiMobileLoginOtpInterface{
    /**
     * Returns otp
     *
     * @api 
     * @param mixed $requestMobileOtp
     * @return array[]
     */ 
    public function sendMobileLoginOtp($requestMobileOtp); 
}
