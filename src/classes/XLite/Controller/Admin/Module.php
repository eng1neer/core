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
 * @category   LiteCommerce
 * @package    XLite
 * @subpackage Controller
 * @author     Creative Development LLC <info@cdev.ru> 
 * @copyright  Copyright (c) 2011 Creative Development LLC <info@cdev.ru>. All rights reserved
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @version    GIT: $Id$
 * @link       http://www.litecommerce.com/
 * @see        ____file_see____
 * @since      3.0.0
 */

namespace XLite\Controller\Admin;

/**
 * Module settings
 * 
 * @package XLite
 * @see     ____class_see____
 * @since   3.0.0
 */
class Module extends \XLite\Controller\Admin\AAdmin
{
    /**
     * Module object
     * 
     * @var    mixed
     * @access protected
     * @see    ____var_see____
     * @since  3.0.0
     */
    protected $module;

    /**
     * Get current module ID
     * 
     * @return integer
     * @access protected
     * @see    ____func_see____
     * @since  3.0.0
     */
    protected function getModuleID()
    {
        return \XLite\Core\Request::getInstance()->moduleId;
    }

    /**
     * Return current module object
     * 
     * @return \XLite\Model\Module
     * @access protected
     * @see    ____func_see____
     * @since  3.0.0
     */
    protected function getModule()
    {
        if (!isset($this->module)) {
            $this->module = \XLite\Core\Database::getRepo('\XLite\Model\Module')->find($this->getModuleID());

            if (!$this->module) {
               throw new \Exception('Add-on does not exist (ID#' . $this->getModuleID() . ')');
            }
        }

        return $this->module;
    }

    /**
     * Return current module options
     * 
     * @return array 
     * @access protected
     * @since  3.0.0
     */
    public function getOptions()
    {
        return \XLite\Core\Database::getRepo('\XLite\Model\Config')
            ->getByCategory($this->getModule()->getActualName(), true, true);
    }
 
    /**
     * Common method to determine current location
     *
     * @return string
     * @access protected
     * @see    ____func_see____
     * @since  3.0.0
     */
    public function getLocation()
    {
        return $this->getModule()->getName() . ' (' . $this->getModule()->getAuthor() . ')';
    }

    /**
     * Add part to the location nodes list
     *
     * @return void
     * @access protected
     * @see    ____func_see____
     * @since  3.0.0
     */
    protected function addBaseLocation()
    {
        parent::addBaseLocation();

        $this->addLocationNode('Manage modules', $this->buildURL('modules'));
    }

    /**
     * Update module settings 
     * 
     * @return void
     * @access protected
     * @since  3.0.0
     */
    protected function doActionUpdate()
    {
        foreach ($this->getOptions() as $option) {

            $name  = $option->name;
            $value = \XLite\Core\Request::getInstance()->$name;

            switch ($option->type) {

                case 'checkbox':
                    $value = isset($value) ? 'Y' : 'N';
                    break;

                case 'serialized':
                    $value = serialize($value);
                    break;

                default:
                    $value = trim($value);
            }

            \XLite\Core\Database::getRepo('\XLite\Model\Config')->createOption(
                array(
                    'category' => $this->getModule()->getActualName(),
                    'name'     => $name,
                    'value'    => $value,
                    'type'     => $type
                )
            );
        }

        $this->setReturnUrl($this->buildUrl('modules'));
    }
}
