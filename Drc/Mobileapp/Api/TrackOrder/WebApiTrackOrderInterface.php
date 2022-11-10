<?php
namespace Drc\Mobileapp\Api\TrackOrder;
 
interface WebApiTrackOrderInterface{
    
    /**
     * Returns Trackorder
     * @param int $orderId
     * @return mixed
     */
    public function getTrackOrderDetails($orderId);  
}