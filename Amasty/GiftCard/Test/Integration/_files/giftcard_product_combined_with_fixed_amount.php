<?php

require 'giftcard_codepool_with_code.php';
require 'giftcard_image.php';

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

$amountData = [
    'value' => 10,
    'website_id' => 0
];

$extensionAttributes = $objectManager->create(\Magento\Catalog\Api\Data\ProductExtension::class);
$giftCardAmountFactory = $objectManager->create(\Amasty\GiftCard\Api\Data\GiftCardPriceInterfaceFactory::class);
$amount = $giftCardAmountFactory->create(['data' => $amountData]);
$extensionAttributes->setAmGiftcardPrices([$amount]);

/** @var \Magento\Catalog\Model\Product $product */
$product = $objectManager->create(\Magento\Catalog\Model\Product::class);
$product->setTypeId(\Amasty\GiftCard\Model\GiftCard\Product\Type\GiftCard::TYPE_AMGIFTCARD)
    ->setAttributeSetId($product->getDefaultAttributeSetId())
    ->setWebsiteIds([1])
    ->setName('Am Gift Card With Amounts')
    ->setSku('am_giftcard_fixed_amount')
    ->setDescription('Gift Card Description')
    ->setMetaTitle('Gift Card Meta Title')
    ->setMetaKeyword('Gift Card Meta Keyword')
    ->setMetaDescription('Gift Card Meta Description')
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 22, 'is_in_stock' => 1])
    ->setQty(22)
    ->setExtensionAttributes($extensionAttributes)
    ->addData([
        'am_allow_open_amount' => '0',
        'am_giftcard_type' => '3',
        'am_giftcard_lifetime' => 15,
        'am_email_template' => '-1',
        'am_giftcard_code_set' => $codePool->getCodePoolId(),
        'am_giftcard_code_image' => $gCardImage->getImageId()
    ])->save();
