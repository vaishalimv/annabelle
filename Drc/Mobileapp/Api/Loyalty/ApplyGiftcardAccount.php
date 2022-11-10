<?php

namespace Drc\Mobileapp\Api\Loyalty;

use Amasty\GiftCardAccount\Api\Data\GiftCardAccountResponseInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
 
interface ApplyGiftcardAccount
{

    /**
     * Add gift card to the cart.
     *
     * @param string|int $cartId
     * @param string $giftCardCode
     *
     * @throws CouldNotSaveException
     * @return string
     * @deprecated
     */
    public function applyGiftCardToCart($cartId, string $giftCardCode): string;

    /**
     * Remove GiftCard Account entity
     *
     * @param string|int $cartId
     * @param string $giftCardCode
     *
     * @throws CouldNotDeleteException
     * @return string
     */
    public function removeGiftCardFromCart($cartId, string $giftCardCode): string;
}
