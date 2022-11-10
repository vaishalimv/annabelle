<?php

namespace Drc\Pushnotification\Block\Adminhtml\Pushnotification\Edit\Tab;

/**
 * Pushnotification edit form main tab
 */
class Main extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @var \Drc\Pushnotification\Model\Status
     */
    protected $_status;

    protected $categoryOptions;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Drc\Pushnotification\Model\Category $categoryOptions,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Drc\Pushnotification\Model\Status $status,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->categoryOptions = $categoryOptions;
        $this->_status = $status;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        /* @var $model \Drc\Pushnotification\Model\BlogPosts */
        $model = $this->_coreRegistry->registry('pushnotification');

        $isElementDisabled = false;

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('page_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Item Information')]);

        if ($model->getId()) {
            $fieldset->addField('pushnotification_id', 'hidden', ['name' => 'pushnotification_id']);
        }
        $fieldset->addField(
            'title',
            'text',
            [
                'name' => 'title',
                'label' => __('Pushnotification Title'),
                'title' => __('Pushnotification Title'),
				'required' => true,
                'disabled' => $isElementDisabled
            ]
        );
		$fieldset->addField(
            'description',
            'textarea',
            [
                'name' => 'description',
                'label' => __('Pushnotification Description'),
                'title' => __('Pushnotification Description'),
				'required' => true,
                'disabled' => $isElementDisabled
            ]
        );							
        $fieldset->addField(
            'image',
            'image',
            [
                'name' => 'image',
                'label' => __('Pushnotification Image'),
                'title' => __('Pushnotification Image'),
                'note'=> 'Please upload `50 X 50` resolution image.',
				'required' => true,
                'disabled' => $isElementDisabled
            ]
        );
        $fieldset->addField(
            'url',
            'select',
            [
                'name' => 'url',
                'label' => __('Type'),
                'title' => __('Type'),
				"values" =>      [
                    ["value" => 0,"label" => __("General")],
                    ["value" => 1,"label" => __("Category")],
                    ["value" => 2,"label" => __("Product Sku")],
                ],
                'disabled' => $isElementDisabled
            ]
        );
        $fieldset->addField(
            'notification_type',
            'select',
            [
                'name' => 'notification_type',
                'label' => __('Notification Type'),
                'title' => __('Notification Type'),
                "values" =>      [
                    ["value" => 'read_more',"label" => __("Read More")],
                    ["value" => 'use_code',"label" => __("Use Code")],
                    ["value" => 'shop_now',"label" => __("Shop Now")],
                ],
                'disabled' => $isElementDisabled
            ]
        );
		$fieldset->addField(
            'category_id',
            'select',
            [
                'name' => 'category_id',
                'label' => __('Category Id'),
                'title' => __('Category Id'),
                'values'=> $this->categoryOptions->toOptionArray(),
                'disabled' => $isElementDisabled
            ]
        );
        $fieldset->addField(
            'product_sku',
            'text',
            [
                'name' => 'product_sku',
                'label' => __('Product Sku'),
                'title' => __('Product Sku'),
                'disabled' => $isElementDisabled
            ]
        );		
        $fieldset->addField(
            'date_time',
            'date',
            [
                'name' => 'date_time',
                'label' => __('Date'),
                'date_format' => 'yyyy-MM-dd',
                'time_format' => 'hh:mm:ss'
            ]
        );				
        $fieldset->addField(
            'status',
            'select',
            [
                'label' => __('Status'),
                'title' => __('Status'),
                'name' => 'status',
				
                'options' => \Drc\Pushnotification\Block\Adminhtml\Pushnotification\Grid::getOptionArray4(),
                'disabled' => $isElementDisabled
            ]
        );
        $this->setChild(
            'form_after',
            $this->getLayout()->createBlock('\Magento\Backend\Block\Widget\Form\Element\Dependence')
            ->addFieldMap("page_url", "url")
            ->addFieldMap("page_category_id", "category_id")
            ->addFieldMap("page_product_sku", "product_sku")
            ->addFieldDependence("category_id", "url", 1)
            ->addFieldDependence("product_sku", "url", 2)
        );
        if (!$model->getId()) {
            $model->setData('is_active', $isElementDisabled ? '0' : '1');
        }

        $form->setValues($model->getData());
        $this->setForm($form);
		
        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Item Information');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Item Information');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
    
    public function getTargetOptionArray(){
    	return array(
    				'_self' => "Self",
					'_blank' => "New Page",
    				);
    }
}
