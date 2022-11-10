<?php

namespace Drc\Pushnotification\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $installer = $setup;

        $installer->startSetup();
        
        $connection = $setup->getConnection();
        if (version_compare($context->getVersion(), '1.0.5', '<')) {
            $installer->run('CREATE TABLE IF NOT EXISTS `customer_devices` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `customer_id` int(11),
              `device_type` varchar(500) DEFAULT NULL,
              `device_token` varchar(255) DEFAULT NULL,
              `device_id` varchar(255) DEFAULT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1');
        }

        if (version_compare($context->getVersion(), '1.0.9', '<')) {
         $installer->run('ALTER TABLE `pushnotification`
          ADD `sent_on` datetime NULL');
        }
        
        if (version_compare($context->getVersion(), '2.0.0') < 0) {
            $this->addNotificationColumn($connection, $installer);
        }
        
        $installer->endSetup();
    }
    private function addNotificationColumn($connection, $installer)
    {
        $table = $installer->getTable('pushnotification');
        $connection->addColumn($table, 'notification_type',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 254,
                'nullable' => NULL,
                'comment' => 'notification_type',
                'after' => 'sent_on'
            ],
            null
        );
    }
}