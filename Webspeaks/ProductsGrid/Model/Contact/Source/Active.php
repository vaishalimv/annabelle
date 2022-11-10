<?php
/**
 * Drc_AmriHome extension
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 * 
 * @category  Drc
 * @package   Drc_AmriHome
 * @copyright Copyright (c) 2018
 * @license   http://opensource.org/licenses/mit-license.php MIT License
 */
namespace Webspeaks\ProductsGrid\Model\Contact\Source;

class Active implements \Magento\Framework\Option\ArrayInterface
{
    const YES = 1;
    const NO = 2;

    /**
     * to option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            [
                'value' => self::YES,
                'label' => __('Enable')
            ],
            [
                'value' => self::NO,
                'label' => __('Disable')
            ],
        ];
        return $options;

    }
}
