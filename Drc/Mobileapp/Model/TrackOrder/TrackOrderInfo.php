<?php
namespace Drc\Mobileapp\Model\TrackOrder;

use Drc\Mobileapp\Api\TrackOrder\WebApiTrackOrderInterface;

class TrackOrderInfo implements WebApiTrackOrderInterface
{
    protected $helper;

    protected $orderRepository;

    public function __construct(
        \Fledge\TrackOrder\Helper\Data $helper,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
    ) {
        $this->helper = $helper;
        $this->orderRepository = $orderRepository;
    }

	/**
     * Returns Trackorder
     * @param int $orderId
     * @return mixed
    */
    public function getTrackOrderDetails($orderId)
    {
        $trackingDetails = [];
        $orderCreatedAt = [];
        $incrementId = [];
        $order = $this->orderRepository->get($orderId);;
        if($order && $order->getId()) {
            $trackingCollection = $order->getTracksCollection();
            if ($trackingCollection->count() > 0) {
                foreach ($trackingCollection as $tracking) {
                    if(!empty($tracking->getTitle())) {
                        $orderCreatedAt = date('F d, Y', strtotime($order->getCreatedAt()));
                        $incrementId = $order->getIncrementId();
                        /* DHL Method */
                        if($tracking->getCarrierCode() == 'dhl') {
                            if (!empty($tracking->getTrackNumber())){
                            $trackingUrl = $this->helper->getDhlTrackingUrl();
                            $trackingApiKey = $this->helper->getDhlApiKey();

                            if (!empty($trackingUrl) && !empty($trackingApiKey)){
                                $trackingUrl = 'https://api-eu.dhl.com/track/shipments?trackingNumber=4984178835';//$trackingUrl.'?trackingNumber='.$tracking->getTrackNumber();
                                $trackingInfo = $this->helper->curlCall($trackingUrl, $trackingApiKey);
                                if(
                                    isset($trackingInfo['shipments']) &&
                                    !empty($trackingInfo['shipments']) &&
                                    isset($trackingInfo['shipments'][0]) &&
                                    !empty($trackingInfo['shipments'][0]) &&
                                    isset($trackingInfo['shipments'][0]['events']) &&
                                    !empty($trackingInfo['shipments'][0]['events'])
                                ) {
                                    $trackingData = $trackingInfo['shipments'][0]['events'];
                                        foreach($trackingData as $trackingStatus ) {
                                             $trackingDetails[] = [
                                                'tracking_time' => (isset($trackingStatus['timestamp'])) ? date('h:i A', strtotime($trackingStatus['timestamp'])): '',
                                                'tracking_date'=> (isset($trackingStatus['timestamp'])) ? date('F d, Y', strtotime($trackingStatus['timestamp'])): '',
                                                'tracking_activity' => (isset($trackingStatus['description'])) ? $trackingStatus['description']: '',
                                             ];                                               
                                        }
                                    }
                                }
                                } else{
                                     $trackingDetails[] = [
                                        'tracking_time' => (isset($trackingStatus['timestamp'])) ? date('h:i A', strtotime($order->getCreatedAt())): '',
                                        'tracking_date'=> (isset($trackingStatus['timestamp'])) ? date('F d, Y', strtotime($order->getCreatedAt())): '',
                                        'message'=> 'Order Placed',
                                     ];                                     
                                }
                            }

                        /* SMSA Method */ 
                        elseif ($tracking->getCarrierCode() == 'smsa') {

                            if (!empty($tracking->getTrackNumber())){
                                $trackingUrl = $this->helper->getSmsaTrackingUrl();
                                $passkey = $this->helper->getSmsaPassKey();

                            if (!empty($trackingUrl) && !empty($passkey)){ 
                                $trackingNumber = '290220105375';//$tracking->getTrackNumber();
                                $trackingUrl = $trackingUrl . '?awbNo=' . $trackingNumber . '&passkey=' . $passkey;
                                $trackingInfo = $this->helper->getSmsaTrackingInfo($trackingUrl, $passkey);
                                if (!empty($trackingInfo) && isset($trackingInfo['Tracking']))
                                { 
                                    $trackingData = $trackingInfo['Tracking'];
                                    if (!empty($trackingData)) { 
                                        foreach ($trackingData as $trackingStatus){
                                            $trackingDetails[] = [
                                                'tracking_time'=> (isset($trackingStatus['Date'])) ? date('h:i A', strtotime($trackingStatus['Date'])): '',
                                                'tracking_date'=>(isset($trackingStatus['Date'])) ? date('F d, Y', strtotime($trackingStatus['Date'])): '',
                                                'tracking_activity' => (isset($trackingStatus['Activity'])) ? $trackingStatus['Activity']: '',
                                                ];                                              
                                            }
                                        }
                                    }
                                }
                            }
                            else{
                                $trackingDetails[] = [
                                    'tracking_time'=> (isset($trackingStatus['timestamp'])) ? date('h:i A', strtotime($order->getCreatedAt())): '',
                                    'tracking_date' => (isset($trackingStatus['timestamp'])) ? date('F d, Y', strtotime($order->getCreatedAt())): '',
                                    'message' => 'Order Placed',
                                ];
                                
                            }
                        }
                        /* Emirates Post Method */ 
                        elseif ($tracking->getCarrierCode() == 'emiratespost') {
                            if (!empty($tracking->getTrackNumber())) {
                                $trackingUrl = $this->helper->getEmiratesPostTrackingUrl();
                                if (!empty($trackingUrl)) {
                                    $trackingNumber = $tracking->getTrackNumber();
                                    $trackingUrl = $trackingUrl . '?track_id=' . $trackingNumber;
                                    $trackingInfo = $this->helper->getEmiratesPostTrackingInfo($trackingUrl);
                                
                                 if (!empty($trackingInfo) && isset($trackingInfo['track_final_result'])) {
                                    $trackingData = $trackingInfo['track_final_result'];
                                    if (!empty($trackingData)) {
                                        foreach ($trackingData as $trackingStatus) {
                                            $trackingDetails[] = [
                                                'tracking_time'=> (isset($trackingStatus['time_stamp'])) ? date('h:i A', strtotime(str_replace('/', '-', $trackingStatus['time_stamp']))): '',
                                                'tracking_time' => (isset($trackingStatus['time_stamp'])) ? date('F d, Y', strtotime(str_replace('/', '-', $trackingStatus['time_stamp']))): '',
                                                'tracking_activity' => (isset($trackingStatus['remarks_en'])) ? $trackingStatus['remarks_en']: '',
                                                ];            
                                            }       
                                        } 
                                    }
                                }
                            }
                            else{
                                $trackingDetails[] = [
                                    'tracking_time' => date('h:i A', strtotime($order->getCreatedAt())),
                                    'tracking_time' => date('F d, Y', strtotime($order->getCreatedAt())),
                                    'message' => 'Order Placed',  
                                ];
                            }
                        }

                        $trackingDetail = ['0' =>['status'=> '200','message'=> 'Tracking Info loading successfully.', 'carrier_code' => $tracking->getCarrierCode() ,'orderCreatedAt' => 'Ordered on '.$orderCreatedAt, 'incrementId' =>$incrementId, 'data' => $trackingDetails]];  

                        return $trackingDetail;
                    }
                }
            }
        }
    }
}