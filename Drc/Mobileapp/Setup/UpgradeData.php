<?php
namespace Drc\Mobileapp\Setup;

use Magento\Framework\Setup\{
ModuleDataSetupInterface,
ModuleContextInterface,
UpgradeDataInterface
};

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;

/**
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface
{
	/**
	 * EAV setup factory
	 *
	 * @var EavSetupFactory
	 */
	private $eavSetupFactory;

	protected $categorySetupFactory;


	/**
	 * Init
	 *
	 * @param EavSetupFactory $eavSetupFactory
	 */
	public function __construct(EavSetupFactory $eavSetupFactory,\Magento\Catalog\Setup\CategorySetupFactory $categorySetupFactory)
	{
	    $this->eavSetupFactory = $eavSetupFactory;
	    $this->categorySetupFactory = $categorySetupFactory;
	}

	/**
	 * {@inheritdoc}
	 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
	 */
	public function upgrade(
		ModuleDataSetupInterface $setup, 
		ModuleContextInterface $context
		)
	{
	    $setup->startSetup();

	    /** @var EavSetup $eavSetup */
	    $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
	    $eavSetup = $this->categorySetupFactory->create(['setup' => $setup]);         

	    /**
	     * Add attributes to the eav/attribute
	     */

	    if ($context->getVersion() && version_compare($context->getVersion(), '0.0.5') < 0) {

	      $eavSetup->addAttribute(
	          \Magento\Catalog\Model\Category::ENTITY,
	          'category_mobile_banner',
	          [
	              'type' => 'varchar',
	              'label' => 'Mobile Banner',
	              'input' => 'image',
	              'backend' => 'Magento\Catalog\Model\Category\Attribute\Backend\Image',
	              'required' => false,
	              'sort_order' => 5,
	              'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
	              'group' => 'General Information',
	          ]
	      );

	    }

	    $setup->endSetup();

	  }
} 
