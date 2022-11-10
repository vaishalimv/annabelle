<?php

namespace Drc\Mobileapp\Api;
 
interface WebApiIsMobileNumberVerified{
    
    /**
     * Returns ismobileLoyality
     *
     * @api 
     * @param int $customerId
     * @return mixed
     */ 
    public function isMobileLoyality($customerId); 
}
