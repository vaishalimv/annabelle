<?php
namespace Drc\Pushnotification\Block\Adminhtml\Pushnotification;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Drc\Pushnotification\Model\pushnotificationFactory
     */
    protected $_pushnotificationFactory;

    /**
     * @var \Drc\Pushnotification\Model\Status
     */
    protected $_status;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Drc\Pushnotification\Model\pushnotificationFactory $pushnotificationFactory
     * @param \Drc\Pushnotification\Model\Status $status
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Drc\Pushnotification\Model\PushnotificationFactory $PushnotificationFactory,
        \Drc\Pushnotification\Model\Status $status,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {
        $this->_pushnotificationFactory = $PushnotificationFactory;
        $this->_status = $status;
        $this->moduleManager = $moduleManager;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('postGrid');
        $this->setDefaultSort('pushnotification_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(false);
        $this->setVarNameFilter('post_filter');
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_pushnotificationFactory->create()->getCollection();
        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }

    /**
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'pushnotification_id',
            [
                'header' => __('ID'),
                'type' => 'number',
                'index' => 'pushnotification_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
		$this->addColumn(
			'title',
			[
				'header' => __('Pushnotification Title'),
				'index' => 'title',
			]
		);
		$this->addColumn(
			'description',
			[
				'header' => __('Pushnotification Description'),
				'index' => 'description',
			]
		);
		$this->addColumn(
			'status',
			[
				'header' => __('Status'),
				'index' => 'status',
				'type' => 'options',
				'options' => \Drc\Pushnotification\Block\Adminhtml\Pushnotification\Grid::getOptionArray4()
			]
		);
        $this->addColumn(
            'date_time',
            [
                'header' => __('Pushnotification Date'),
                'index' => 'date_time',
            ]
        );
        $this->addColumn(
            'sent_on',
            [
                'header' => __('Delivered Date'),
                'index' => 'sent_on',
            ]
        );
        $this->addColumn(
            'action',
            [
                'header' => __('Action'),
                'filter' => false,
                'sortable' => false,                
                'is_system' => true,
                'path' => 'pushnotification/send/send',
                'renderer'  => 'Drc\Pushnotification\Block\Adminhtml\Pushnotification\Renderer\Link'
            ]
        );		
		$this->addExportType($this->getUrl('pushnotification/*/exportCsv', ['_current' => true]),__('CSV'));
		$this->addExportType($this->getUrl('pushnotification/*/exportExcel', ['_current' => true]),__('Excel XML'));

        $block = $this->getLayout()->getBlock('grid.bottom.links');
        if ($block) {
            $this->setChild('grid.bottom.links', $block);
        }

        return parent::_prepareColumns();
    }

    /**
     * @return $this
     */
    protected function _prepareMassaction()
    {
        return false;
        $this->setMassactionIdField('pushnotification_id');
        $this->getMassactionBlock()->setFormFieldName('pushnotification');
        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Delete'),
                'url' => $this->getUrl('pushnotification/*/massDelete'),
                'confirm' => __('Are you sure?')
            ]
        );
        $statuses = $this->_status->getOptionArray();
        $this->getMassactionBlock()->addItem(
            'status',
            [
                'label' => __('Change status'),
                'url' => $this->getUrl('pushnotification/*/massStatus', ['_current' => true]),
                'additional' => [
                    'visibility' => [
                        'name' => 'status',
                        'type' => 'select',
                        'class' => 'required-entry',
                        'label' => __('Status'),
                        'values' => $statuses
                    ]
                ]
            ]
        );
        return $this;
    }
    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('pushnotification/*/index', ['_current' => true]);
    }

    /**
     * @param \Drc\Pushnotification\Model\pushnotification|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {	
        return $this->getUrl(
            'pushnotification/*/edit',
            ['pushnotification_id' => $row->getId()]
        );
		
    }

    public function getRowId($row)
    {
        return false;   
        return $row->getId();   
    }

	static public function getOptionArray4()
	{
        $data_array=array(); 
		$data_array[0]='Enable';
		$data_array[1]='Disable';
        return($data_array);
	}

	static public function getValueArray4()
	{
        $data_array=array();
		foreach(\Drc\Pushnotification\Block\Adminhtml\Pushnotification\Grid::getOptionArray4() as $k=>$v){
           $data_array[]=array('value'=>$k,'label'=>$v);		
		}
        return($data_array);
	}
}
