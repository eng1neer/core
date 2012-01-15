<?php
// vim: set ts=4 sw=4 sts=4 et:

/**
 * LiteCommerce
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to licensing@litecommerce.com so we can send you a copy immediately.
 * 
 * PHP version 5.3.0
 * 
 * @category  LiteCommerce
 * @author    Creative Development LLC <info@cdev.ru> 
 * @copyright Copyright (c) 2011 Creative Development LLC <info@cdev.ru>. All rights reserved
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.litecommerce.com/
 * @see       ____file_see____
 * @since     1.0.15
 */

namespace XLite\View\FormField\Inline\Input\Text\Price;

/**
 * Product price
 * 
 * @see   ____class_see____
 * @since 1.0.15
 */
class Product extends \XLite\View\FormField\Inline\Input\Text\Price
{
    /**
     * Get field name parts
     *
     * @return array
     * @see    ____func_see____
     * @since  1.0.15
     */
    public function getNameParts()
    {
        return array_merge(parent::getNameParts(), array('price'));
    }

    /**
     * Save value 
     * 
     * @return void
     * @see    ____func_see____
     * @since  1.0.15
     */
    public function saveValue()
    {
        $this->getEntity()->setPrice($this->getValue());
    }

    /**
     * Get entity value for field
     *
     * @return mixed
     * @see    ____func_see____
     * @since  1.0.15
     */
    protected function getEntityValue()
    {
        return $this->getEntity()->getPrice();
    }

    /**
     * Get initial field parameters
     *
     * @return array
     * @see    ____func_see____
     * @since  1.0.15
     */
    protected function getFieldParams()
    {
        return parent::getFieldParams() + array('min' => 0);
    }

    /**
     * Get field label
     *
     * @return string
     * @see    ____func_see____
     * @since  1.0.15
     */
    protected function getLabel()
    {
        return \XLite\Core\Translation::lbl('Price');
    }

}
