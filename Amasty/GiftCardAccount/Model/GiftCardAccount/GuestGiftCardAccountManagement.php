<?php
declare(strict_types=1);

namespace Amasty\GiftCardAccount\Model\GiftCardAccount;

use Amasty\GiftCardAccount\Api\Data\GiftCardAccountResponseInterface;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;

class GuestGiftCardAccountManagement implements \Amasty\GiftCardAccount\Api\GuestGiftCardAccountManagementInterface
{
    /**
     * @var QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;

    /**
     * @var \Amasty\GiftCardAccount\Api\GiftCardAccountManagementInterface
     */
    private $giftCodeManagement;

    public function __construct(
        \Amasty\GiftCardAccount\Api\GiftCardAccountManagementInterface $giftCodeManagement,
        \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory
    ) {
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->giftCodeManagement = $giftCodeManagement;
    }

    public function applyGiftCardToCart($cartId, string $couponCode): string
    {
        /** @var $quoteIdMask QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');//load due to 2.2 compatibility

        return $this->giftCodeManagement->applyGiftCardToCart($quoteIdMask->getQuoteId(), $couponCode);
    }

    public function removeGiftCardFromCart($cartId, string $giftCard): string
    {
        /** @var $quoteIdMask QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');//load due to 2.2 compatibility

        return $this->giftCodeManagement->removeGiftCardFromCart($quoteIdMask->getQuoteId(), $giftCard);
    }

    public function applyGiftCardAccountToCart(
        $cartId,
        string $giftCard
    ): GiftCardAccountResponseInterface {
        /** @var $quoteIdMask QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');//load due to 2.2 compatibility

        return $this->giftCodeManagement->applyGiftCardAccountToCart($quoteIdMask->getQuoteId(), $giftCard);
    }
}
