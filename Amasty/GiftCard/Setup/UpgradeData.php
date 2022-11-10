<?php

namespace Amasty\GiftCard\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var Operation\UpdateDataTo200
     */
    private $updateDataTo200;

    /**
     * @var Operation\UpdateDataTo210
     */
    private $updateDataTo210;

    /**
     * @var Operation\UpdateDataTo250
     */
    private $updateDataTo250;

    /**
     * @var Operation\UpdateDataTo260
     */
    private $updateDataTo260;

    public function __construct(
        Operation\UpdateDataTo200 $updateDataTo200,
        Operation\UpdateDataTo210 $updateDataTo210,
        Operation\UpdateDataTo250 $updateDataTo250,
        Operation\UpdateDataTo260 $updateDataTo260
    ) {
        $this->updateDataTo200 = $updateDataTo200;
        $this->updateDataTo210 = $updateDataTo210;
        $this->updateDataTo250 = $updateDataTo250;
        $this->updateDataTo260 = $updateDataTo260;
    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if ($context->getVersion() && version_compare($context->getVersion(), '2.0.0', '<')) {
            $this->updateDataTo200->upgrade($setup);
        }
        if ($context->getVersion() && version_compare($context->getVersion(), '2.0.0', '>=')
            && version_compare($context->getVersion(), '2.1.0', '<')
        ) {
            $this->updateDataTo210->upgrade($setup);
        }
        if ($context->getVersion() && version_compare($context->getVersion(), '2.0.0', '>=')
            && version_compare($context->getVersion(), '2.5.0', '<')
        ) {
            $this->updateDataTo250->upgrade($setup);
        }
        if (!$context->getVersion()
            || ($context->getVersion() && version_compare($context->getVersion(), '2.0.0', '>=')
                && version_compare($context->getVersion(), '2.6.0', '<'))
        ) {
            $this->updateDataTo260->upgrade($setup);
        }
    }
}
