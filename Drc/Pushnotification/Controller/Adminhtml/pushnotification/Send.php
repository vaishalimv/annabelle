<?php

namespace Drc\Pushnotification\Controller\Adminhtml\pushnotification;
use Magento\Framework\App\Action\Action;

class Send extends \Magento\Framework\App\Action\Action
{
	/**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    protected $_cacheTypeList;

    /**
     * @var \Magento\Framework\App\Cache\StateInterface
     */
    protected $_cacheState;

    /**
     * @var \Magento\Framework\App\Cache\Frontend\Pool
     */
    protected $_cacheFrontendPool;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;
    
    protected $request;
    
    protected $messageManager;
    
    protected $logger;

    protected $_storeConfig;

    private $objectManager;

    protected $pushnotificationcollection;

    protected $customdeviceFactory;

    protected $_categoryFactory;

    protected $_productRepository;

    /**
     * @param Action\Context $context
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\App\Cache\StateInterface $cacheState
     * @param \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
       \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\App\Cache\StateInterface $cacheState,
        \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \Drc\Pushnotification\Model\PushnotificationFactory $pushnotificationcollection,
        \Drc\Pushnotification\Model\CustomerDevicesFactory $customdeviceFactory,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Model\ProductRepository $productRepository
    ) {
    	parent::__construct($context);
        $this->_cacheTypeList = $cacheTypeList;
        $this->_cacheState = $cacheState;
        $this->_cacheFrontendPool = $cacheFrontendPool;
        $this->resultPageFactory = $resultPageFactory;
        $this->storeManager = $storeManager;
        $this->request = $request;
        $this->_messageManager = $messageManager;
        $this->logger = $logger;
        $this->_storeConfig = $scopeConfig;
        $this->objectManager = $objectmanager;
        $this->pushnotificationcollection = $pushnotificationcollection;
        $this->customdeviceFactory = $customdeviceFactory;
        $this->_categoryFactory = $categoryFactory; 
        $this->_productRepository = $productRepository;
    }

    public function execute()
    {
    	$pid = $this->getRequest()->getParam('pid');
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    	$serverKey = $this->_storeConfig->getValue('pushnotification/subcribe_newsletter/push_server_key', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

    	$server_key = $serverKey;
	    $baseurl = $this->storeManager->getStore()->getBaseUrl();

    	if($pid)
    	{
			$Collection = $this->pushnotificationcollection->create()->getCollection();
			$Collection->addFieldtoFilter("pushnotification_id",$pid);

			/* ios devices */			
	      	$iosdevicesCol = $this->customdeviceFactory->create()->getCollection()->addFieldtoFilter('device_type','i');

	      	$iosdevices=[];
	      	foreach ($iosdevicesCol as $ikey => $ivalue) {
	      		$iosdevices[]=$ivalue->getDeviceToken();
	      	}
            
	      	$this->sendNotification($this->storeManager,$baseurl,$server_key,$objectManager,$Collection,$iosdevices,'i');

	      	/* android devices */

	      	$andevicesCol = $this->customdeviceFactory->create()->getCollection()->addFieldtoFilter('device_type','a');

	      	$andevices=[];
	      	foreach ($andevicesCol as $nkey => $nvalue) {
	      		$andevices[]=$nvalue->getDeviceToken();
	      	}
	      	
			$this->sendNotification($this->storeManager,$baseurl,$server_key,$this->objectManager,$Collection,$andevices,'a');			

			$this->_messageManager->addSuccess(__("Success"));
			return $this->_redirect('*/*/index');
	    }else{

	    	/* Cron script */
	    	echo $currentTIme='Current time : '.date("Y-m-d H:i:s").'='.strtotime(date("Y-m-d H:i:s"));
	    	echo "Cron initialized";

	    	$this->logger->info($currentTIme);
	    	$this->logger->info("Cron initialized");

	    	$cronCol = $this->pushnotificationcollection->create()->getCollection();
			$cronCol->addFieldtoFilter("status",0);

			$ct=strtotime(date("Y-m-d H:i:s"));
			
			if(!empty($cronCol->getData()))
			{
				foreach ($cronCol as $ck => $cv) {
					if( $ct==strtotime($cv->getDateTime()) ){

				      	$baseurl = $this->storeManager->getStore()->getBaseUrl();

				      	$colldata=$cronCol->getData()[0];

				      	/* ios devices */
				      	$iosdevicesCol = $this->customdeviceFactory->create()->getCollection()->addFieldtoFilter('device_type','i');

				      	$iosdevices=[];
				      	foreach ($iosdevicesCol as $ikey => $ivalue) {
				      		$iosdevices[]=$ivalue->getDeviceToken();
				      	}

				      	$this->CronSendNotification($this->storeManager,$baseurl,$server_key,$$objectManager,$colldata,$cronCol,$iosdevices,'i');

				      	/* android devices */

				      	$anroid_devicesCol = $this->customdeviceFactory->create()->getCollection()->addFieldtoFilter('device_type','a');
				      	
				      	$andevices=[];
				      	foreach ($anroid_devicesCol as $key => $nvalue) {
				      		$andevices[]=$nvalue->getDeviceToken();
				      	}
				      	$this->CronSendNotification($this->storeManager,$baseurl,$server_key,$objectManager,$colldata,$cronCol,$andevices,'a');
				      	
						$this->_messageManager->addSuccess(__("Success"));
                        return $this->_redirect('*/*/index');
					}
				}	
			}
	    }
	}

	public function sendNotification($storeManager,$baseurl,$server_key,$objectManager,$Collection,$devices,$dtype)
	{
		$colldata=$Collection->getData()[0];   			
		$category_title='';
        $product_title='';
        if(isset($colldata['category_id']))
        {
            $categoryObj =  $this->_categoryFactory->create()->load($colldata['category_id']);
            if(is_object($categoryObj)){

                $category_title=$categoryObj->getName();
            }
        }

        if(isset($colldata['product_sku'])){

            $productObj = $this->_productRepository->get($colldata['product_sku']);

            if(is_object($productObj)){
                $product_title=$productObj->getName();    
            }
        }
		
		$title=$colldata['title'];
		$body=$colldata['description'];
		$productsku=$colldata['product_sku'];
		$categoryid=$colldata['category_id'];
		$imgUrl = ($colldata['image'] != '' ? $baseurl.'pub/media/'.$colldata['image'] : '');
		$type=$colldata['url'];

		/*
			Note : Use `to` for single devices and
			`registration_ids` for multiple devices
		 */

		$sendData = [
		  'registration_ids' => $devices,
		  'priority' => 'high',
		  'mutable_content'=>true,
		  'data' => [
	        "title"=> $title,
	        "message"=>$body,
	        "image_url"=>$imgUrl,
	        "categoryid"=>$categoryid,
	        "category_title"=>$category_title,
	        "productsku"=>$productsku,
	        "product_title"=>$product_title,
	        "type"=>$type,
	      ]
		];

		if($dtype=='i')
		{
			$sendData['notification'] =array(
					        'title' => $title,
					        'body' =>$body,
					        'sound'=>'default'
					    );
		}elseif($dtype=='a')
		{
			$sendData['data']['body']=$body;
			$sendData['data']['sound']='default';
		}

		$data = json_encode($sendData);
		$url = 'https://fcm.googleapis.com/fcm/send';
		
		//header with content_type api key
		$headers = array(
		    'Content-Type:application/json',
		    'Authorization:key='.$server_key
		);
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		$result = curl_exec($ch);
		if ($result === FALSE) {
		    die('Oops! FCM Send Error: ' . curl_error($ch));
		}
		curl_close($ch);

		$this->logger->info($data);
		$this->logger->info($result);
		$checkRes = json_decode($result);

		if(isset($checkRes->success))
		{
			if( $checkRes->success != 0)
			{
				/* set sent_on date */
				if(!empty($Collection->getData()))
	            {
	                //device found then update token with deviceId
	                foreach($Collection as $item)
	                {
	                    $item->setSentOn(date("Y-m-d H:i:s"));
	                    $item->setStatus('1');
	                }
	                $Collection->save();
	            }
			}
		}
	}

	public function CronSendNotification($storeManager,$baseurl,$server_key,$objectManager,$colldata,$cronCol,$devices,$dtype)
	{
		$category_title='';
        $product_title='';
        if(isset($colldata['category_id']))
        {
            $categoryObj =$this->_categoryFactory->create()->load($colldata['category_id']);

            if(is_object($categoryObj)){

                $category_title=$categoryObj->getName();
            }
        }

        if(isset($colldata['product_sku'])){
            $productObj = $this->_productRepository->get($colldata['product_sku']);
            if(is_object($productObj)){
                $product_title=$productObj->getName();    
            }
        }

		$title=$colldata['title'];
		$body=$colldata['description'];
		$productsku=$colldata['product_sku'];
		$categoryid=$colldata['category_id'];
		$imgUrl = ($colldata['image'] != '' ? $baseurl.'pub/media/'.$colldata['image'] : '');
		$type=$colldata['url'];
		

		$sendData = [
		  'registration_ids' => $devices,
		  'priority' => 'high',
		  'data' => [
	        "title"=> $title,
	        "message"=>$body,
	        "media-attachment"=>$imgUrl,
	        "categoryid"=>$categoryid,
	        "category_title"=>$category_title,
	        "productsku"=>$productsku,
	        "product_title"=>$product_title,
	        "type"=>$type,
	      ]
		];

		if($dtype=='i')
		{
			$sendData['content_available']= true;
		    $sendData['mutable_content']=true;
			$sendData['notification'] =array(
					        'title' => $title,
					        'body' =>$body,
					        'sound'=>'default'
					    );
		}elseif($dtype=='a')
		{
			$sendData['data']['body']=$body;
			$sendData['data']['sound']='default';
		}
		$data = json_encode($sendData);
		$url = 'https://fcm.googleapis.com/fcm/send';
		$headers = array(
		    'Content-Type:application/json',
		    'Authorization:key='.$server_key
		);
		//CURL request to route notification to FCM connection server (provided by Google)
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		$result = curl_exec($ch);
		if ($result === FALSE) {
		    die('Oops! FCM Send Error: ' . curl_error($ch));
		}

		curl_close($ch);

		$this->logger->info("Cron log:".$result);
		$this->logger->info($result);
		$checkRes = json_decode($result);

		if(isset($checkRes->success))
		{
			if( $checkRes->success != 0)
			{
				/* set sent_on date */
				if(!empty($cronCol->getData()))
	            {
	                //device found then update token with deviceId
	                foreach($cronCol as $item)
	                {
	                	$item->setSentOn(date("Y-m-d H:i:s"));
	                    $item->setStatus("1");
	                }
	                $cronCol->save();
	            }
			}
		}
	}
}