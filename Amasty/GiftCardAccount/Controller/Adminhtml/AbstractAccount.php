<?php
declare(strict_types=1);

namespace Amasty\GiftCardAccount\Controller\Adminhtml;

abstract class AbstractAccount extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Amasty_GiftCard::giftcard_account';
}
