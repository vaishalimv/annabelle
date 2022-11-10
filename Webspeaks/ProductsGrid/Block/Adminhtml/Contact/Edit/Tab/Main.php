<?php
namespace Webspeaks\ProductsGrid\Block\Adminhtml\Contact\Edit\Tab;

class Main extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $store;

    /**
    * @var \Webspeaks\ProductsGrid\Helper\Data $helper
    */
    protected $helper;
    protected $_systemStore;
    protected $activeOptions;
    protected $categoryOptions;
    protected $imagePositionOptions;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Store\Model\System\Store $systemStore,
        \Webspeaks\ProductsGrid\Model\Contact\Source\Active $activeOptions,
        \Webspeaks\ProductsGrid\Model\Contact\Source\Category $categoryOptions,
        \Webspeaks\ProductsGrid\Model\Contact\Source\ImagePosition $imagePositionOptions,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Webspeaks\ProductsGrid\Helper\Data $helper,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->helper = $helper;
        $this->activeOptions     = $activeOptions;
        $this->categoryOptions = $categoryOptions;
        $this->imagePositionOptions = $imagePositionOptions;
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
        /* @var $model \Webspeaks\ProductsGrid\Model\Contact */
        $model = $this->_coreRegistry->registry('ws_contact');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('contact_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Section Information')]);

        if ($model->getId()) {
            $fieldset->addField('contact_id', 'hidden', ['name' => 'contact_id']);
        }
        $fieldset->addField(
            'active',
            'select',
            [
                'name' => 'active',
                'label' => __('Status'),
                'title' => __('Status'),
                'values' => $this->activeOptions->toOptionArray(),
                'required' => true,
            ]
        );
        $fieldset->addField(
            'title',
            'text',
            [
                'name' => 'title',
                'label' => __('Title'),
                'title' => __('Title'),
                'required' => true,
            ]
        );  
        $fieldset->addField(
            'description',
            'text',
            [
                'name' => 'description',
                'label' => __('Description'),
                'title' => __('Description'),
                'required' => true,
            ]
        );  
        $fieldset->addField(
            'button_title',
            'text',
            [
                'name' => 'button_title',
                'label' => __('Button Title'),
                'title' => __('Button Title'),
                'required' => true,
            ]
        );  
        $fieldset->addField(
            'category',
            'select',
            [
                'name' => 'category',
                'label' => __('Category'),
                'title' => __('Category'),
                'values'=> $this->categoryOptions->toOptionArray(),
                'required' => true,
            ]
        );
        $fieldset->addField(
            'image_position',
            'select',
            [
                'name' => 'image_position',
                'label' => __('Image Position'),
                'title' => __('Image Position'),
                'values'=> $this->imagePositionOptions->toOptionArray(),
                'note'      => __('Choose section style.'),
            ]
        );  
        $fieldset->addField(
            'image',
            'image',
            [
                'name' => 'image',
                'label' => __('Upload Image'),
                'title' => __('Upload Image'),
                'note'      => __('If you want to add Shop Now product image then upload product image banner.'),
                
            ]
        ); 
        $fieldset->addField(
            'image_text',
            'text',
            [
                'name' => 'image_text',
                'label' => __('Image Text'),
                'title' => __('Image Text'),
                'note'      => __('Enter product image text.'),
            ]
        );
        $fieldset->addField(
            'product_category',
            'select',
            [
                'name' => 'product_category',
                'label' => __('Select Category'),
                'title' => __('Select Category'),
                'values'=> $this->categoryOptions->toOptionArray(),
                'note'      => __('Select category for Shop now button.'),
            ]
        ); 
        $fieldset->addField(
            'store',
            'select',
            [
                'name' => 'store',
                'label' => __('Store'),
                'title' => __('Store'),
                'values' => $this->_systemStore->getStoreValuesForForm(false, false),
                'required' => true,
            ]
        ); 
       
        $form->setValues($model->getData());
		
		$this->setChild('form_after',$this->getLayout()
		 	->createBlock('Magento\Backend\Block\Widget\Form\Element\Dependence')
		 	->addFieldMap("contact_image_position",'image_position')
			->addFieldMap("contact_image",'image')
			->addFieldMap("contact_image_text",'image_text')
            ->addFieldMap("contact_product_category",'product_category')
			->addFieldDependence('image','image_position','2')
            ->addFieldDependence('image_text','image_position','2')
			->addFieldDependence('product_category','image_position','2')
		);
		
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
        return __('Main');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Main');
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
}