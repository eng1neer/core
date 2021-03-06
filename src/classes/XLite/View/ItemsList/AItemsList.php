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
 * @since     1.0.0
 */

namespace XLite\View\ItemsList;

/**
 * Base class for all lists
 *
 * @see   ____class_see____
 * @since 1.0.0
 */
abstract class AItemsList extends \XLite\View\Container
{
    /**
     * Widget param names
     */
    const PARAM_SORT_BY      = 'sortBy';
    const PARAM_SORT_ORDER   = 'sortOrder';

    /**
     * SQL orderby directions
     */
    const SORT_ORDER_ASC  = 'asc';
    const SORT_ORDER_DESC = 'desc';

    /**
     * Default layout template
     *
     * @var   string
     * @see   ____var_see____
     * @since 1.0.0
     */
    protected $defaultTemplate = 'common/dialog.tpl';

    /**
     * commonParams
     *
     * @var   array
     * @see   ____var_see____
     * @since 1.0.0
     */
    protected $commonParams;

    /**
     * pager
     *
     * @var   \XLite\View\Pager\APager
     * @see   ____var_see____
     * @since 1.0.0
     */
    protected $pager;

    /**
     * itemsCount
     *
     * @var   integer
     * @see   ____var_see____
     * @since 1.0.0
     */
    protected $itemsCount;

    /**
     * sortByModes
     *
     * @var   array
     * @see   ____var_see____
     * @since 1.0.0
     */
    protected $sortByModes = array();

    /**
     * sortOrderModes
     *
     * @var   array
     * @see   ____var_see____
     * @since 1.0.0
     */
    protected $sortOrderModes = array(
        self::SORT_ORDER_ASC  => 'Ascending',
        self::SORT_ORDER_DESC => 'Descending',
    );

    /**
     * Sorting widget IDs list
     *
     * @var   array
     * @see   ____var_see____
     * @since 1.0.0
     */
    protected static $sortWidgetIds = array();

    /**
     * Return dir which contains the page body template
     *
     * @return string
     * @see    ____func_see____
     * @since  1.0.0
     */
    abstract protected function getPageBodyDir();

    /**
     * Return class name for the list pager
     *
     * @return string
     * @see    ____func_see____
     * @since  1.0.0
     */
    abstract protected function getPagerClass();

    /**
     * Return products list
     *
     * @param \XLite\Core\CommonCell $cnd       Search condition
     * @param boolean                $countOnly Return items list or only its size OPTIONAL
     *
     * @return array|integer
     * @see    ____func_see____
     * @since  1.0.0
     */
    abstract protected function getData(\XLite\Core\CommonCell $cnd, $countOnly = false);

    /**
     * Get session cell name for the certain list items widget
     *
     * @return string
     * @see    ____func_see____
     * @since  1.0.0
     */
    static public function getSessionCellName()
    {
        return str_replace('\\', '', get_called_class());
    }

    /**
     * Initialize widget (set attributes)
     *
     * @param array $params Widget params
     *
     * @return void
     * @see    ____func_see____
     * @since  1.0.0
     */
    public function setWidgetParams(array $params)
    {
        parent::setWidgetParams($params);

        // Do not change call order
        $this->widgetParams += $this->getPager()->getWidgetParams();
        $this->requestParams = array_merge($this->requestParams, $this->getPager()->getRequestParams());
    }

    /**
     * getActionURL
     *
     * @param array $params Params to modify OPTIONAL
     *
     * @return string
     * @see    ____func_see____
     * @since  1.0.0
     */
    public function getActionURL(array $params = array())
    {
        return $this->getURL($params + $this->getURLParams());
    }

    /**
     * Get a list of JavaScript files
     *
     * @return array
     * @see    ____func_see____
     * @since  1.0.0
     */
    public function getJSFiles()
    {
        $list = parent::getJSFiles();

        // Static call of the non-static function
        $list[] = self::getDir() . '/items_list.js';

        return $list;
    }

    /**
     * Register files from common repository
     *
     * @return array
     * @see    ____func_see____
     * @since  1.0.0
     */
    public function getCommonFiles()
    {
        $list = parent::getCommonFiles();
        $list['js'][] = 'js/jquery.blockUI.js';

        return $list;
    }

    /**
     * Get a list of CSS files
     *
     * @return array
     * @see    ____func_see____
     * @since  1.0.0
     */
    public function getCSSFiles()
    {
        $list = parent::getCSSFiles();
        // Static call of the non-static function
        $list[] = self::getDir() . '/items_list.css';
        $list = self::preparePagerCSSFiles($list);

        return $list;
    }

    /**
     * Returns a list of CSS classes (separated with a space character) to be attached to the items list
     *
     * @return string
     * @see    ____func_see____
     * @since  1.0.0
     */
    public function getListCSSClasses()
    {
        return 'items-list';
    }

    /**
     * Return inner head for list widgets
     *
     * @return string
     * @see    ____func_see____
     * @since  1.0.0
     */
    protected function getListHead()
    {
        return parent::getHead();
    }

    /**
     * Return number of items in products list
     *
     * @return array
     * @see    ____func_see____
     * @since  1.0.0
     */
    protected function getItemsCount()
    {
        if (!isset($this->itemsCount)) {
            $this->itemsCount = $this->getData($this->getSearchCondition(), true);
        }

        return $this->itemsCount;
    }

    /**
     * Return name of the base widgets list
     *
     * @return string
     * @see    ____func_see____
     * @since  1.0.0
     */
    protected function getListName()
    {
        return 'itemsList';
    }

    /**
     * Get widget templates directory
     * NOTE: do not use "$this" pointer here (see "getBody()" and "get[CSS/JS]Files()")
     *
     * @return string
     * @see    ____func_see____
     * @since  1.0.0
     */
    protected function getDir()
    {
        return 'items_list';
    }

    /**
     * prepare CSS file list for use with pager
     *
     * @param array $list CSS file list
     *
     * @return array
     * @see    ____func_see____
     * @since  1.0.0
     */
    protected function preparePagerCSSFiles($list)
    {
        return array_merge($list, self::getPager()->getCSSFiles());
    }

    /**
     * Return file name for the center part template
     *
     * @return string
     * @see    ____func_see____
     * @since  1.0.0
     */
    protected function getBody()
    {
        // Static call of the non-static function
        return self::getDir() . LC_DS . $this->getBodyTemplate();
    }

    /**
     * Return default template
     * See setWidgetParams()
     *
     * @return string
     * @see    ____func_see____
     * @since  1.0.0
     */
    protected function getDefaultTemplate()
    {
        return $this->defaultTemplate;
    }

    /**
     * getPageBodyTemplate
     *
     * @return string
     * @see    ____func_see____
     * @since  1.0.0
     */
    protected function getPageBodyTemplate()
    {
        return $this->getDir() . LC_DS . $this->getPageBodyDir() . LC_DS . $this->getPageBodyFile();
    }

    /**
     * getPageBodyFile
     *
     * @return string
     * @see    ____func_see____
     * @since  1.0.0
     */
    protected function getPageBodyFile()
    {
        return 'body.tpl';
    }

    /**
     * getEmptyListTemplate
     *
     * @return string
     * @see    ____func_see____
     * @since  1.0.0
     */
    protected function getEmptyListTemplate()
    {
        return $this->getEmptyListDir() . LC_DS . $this->getEmptyListFile();
    }

    /**
     * Return "empty list" catalog
     *
     * @return string
     * @see    ____func_see____
     * @since  1.0.0
     */
    protected function getEmptyListDir()
    {
        return self::getDir();
    }

    /**
     * getEmptyListFile
     *
     * @return string
     * @see    ____func_see____
     * @since  1.0.0
     */
    protected function getEmptyListFile()
    {
        return 'empty.tpl';
    }

    /**
     * isEmptyListTemplateVisible
     *
     * @return string
     * @see    ____func_see____
     * @since  1.0.0
     */
    protected function isEmptyListTemplateVisible()
    {
        return false === $this->hasResults();
    }

    /**
     * Get pager parameters list
     *
     * @return array
     * @see    ____func_see____
     * @since  1.0.0
     */
    protected function getPagerParams()
    {
        return array(
            \XLite\View\Pager\APager::PARAM_ITEMS_COUNT => $this->getItemsCount(),
            \XLite\View\Pager\APager::PARAM_LIST        => $this,
        );
    }

    /**
     * Get pager
     *
     * @return \XLite\View\Pager\APager
     * @see    ____func_see____
     * @since  1.0.0
     */
    protected function getPager()
    {
        if (!isset($this->pager)) {
            $this->pager = $this->getWidget($this->getPagerParams(), $this->getPagerClass());
        }

        return $this->pager;
    }

    /**
     * Return params list to use for search
     *
     * @return \XLite\Core\CommonCell
     * @see    ____func_see____
     * @since  1.0.0
     */
    protected function getSearchCondition()
    {
        return new \XLite\Core\CommonCell();
    }

    /**
     * getPageData
     *
     * @return array
     * @see    ____func_see____
     * @since  1.0.0
     */
    protected function getPageData()
    {
        return $this->getData($this->getPager()->getLimitCondition(null, null, $this->getSearchCondition()));
    }

    /**
     * getSortOrderDefault
     *
     * @return string
     * @see    ____func_see____
     * @since  1.0.0
     */
    protected function getSortOrderModeDefault()
    {
        return self::SORT_ORDER_ASC;
    }

    /**
     * getSortByModeDefault
     *
     * @return string
     * @see    ____func_see____
     * @since  1.0.0
     */
    protected function getSortByModeDefault()
    {
        return null;
    }

    /**
     * getSortBy
     *
     * @return string
     * @see    ____func_see____
     * @since  1.0.0
     */
    protected function getSortBy()
    {
        return $this->getParam(self::PARAM_SORT_BY);
    }

    /**
     * getSortOrder
     *
     * @return string
     * @see    ____func_see____
     * @since  1.0.0
     */
    protected function getSortOrder()
    {
        return $this->getParam(self::PARAM_SORT_ORDER);
    }

    /**
     * Define widget parameters
     *
     * @return void
     * @see    ____func_see____
     * @since  1.0.0
     */
    protected function defineWidgetParams()
    {
        parent::defineWidgetParams();

        if (!empty($this->sortByModes)) {

            $this->widgetParams += array(
                self::PARAM_SORT_BY => new \XLite\Model\WidgetParam\Set(
                    'Sort by', $this->getSortByModeDefault(), false, $this->sortByModes
                ),
                self::PARAM_SORT_ORDER => new \XLite\Model\WidgetParam\Set(
                    'Sort order', $this->getSortOrderModeDefault(), false, $this->sortOrderModes
                ),
            );
        }
    }

    /**
     * getJSHandlerClassName
     *
     * @return string
     * @see    ____func_see____
     * @since  1.0.0
     */
    protected function getJSHandlerClassName()
    {
        return 'ItemsList';
    }

    /**
     * getJSArray
     *
     * @param array $params Params to use
     *
     * @return string
     * @see    ____func_see____
     * @since  1.0.0
     */
    protected function getJSArray(array $params)
    {
        $result = array();

        foreach ($params as $name => $value) {
            $result[] = $name . ': \'' . addslashes($value) . '\'';
        }

        return '{' . implode(', ', $result) . '}';
    }

    /**
     * Get URL common parameters
     *
     * @return array
     * @see    ____func_see____
     * @since  1.0.0
     */
    protected function getCommonParams()
    {
        if (!isset($this->commonParams)) {
            $this->commonParams = array(
                self::PARAM_SESSION_CELL => $this->getSessionCell()
            );
        }

        return $this->commonParams;
    }

    /**
     * Get AJAX-specific URL parameters
     *
     * @return array
     * @see    ____func_see____
     * @since  1.0.0
     */
    protected function getAJAXSpecificParams()
    {
        return array(
            self::PARAM_AJAX_WIDGET => get_class($this),
            self::PARAM_AJAX_TARGET => \XLite\Core\Request::getInstance()->target,
        );
    }

    /**
     * getURLParams
     *
     * @return array
     * @see    ____func_see____
     * @since  1.0.0
     */
    protected function getURLParams()
    {
        return array('target' => \XLite\Core\Request::getInstance()->target) + $this->getCommonParams();
    }

    /**
     * getURLAJAXParams
     *
     * @return array
     * @see    ____func_see____
     * @since  1.0.0
     */
    protected function getURLAJAXParams()
    {
        return $this->getCommonParams() + $this->getAJAXSpecificParams();
    }

    /**
     * getURLParams
     *
     * @return string
     * @see    ____func_see____
     * @since  1.0.0
     */
    protected function getURLParamsJS()
    {
        return $this->getJSArray($this->getURLParams());
    }

    /**
     * getURLAJAXParams
     *
     * @return string
     * @see    ____func_see____
     * @since  1.0.0
     */
    protected function getURLAJAXParamsJS()
    {
        return $this->getJSArray($this->getURLAJAXParams());
    }

    /**
     * Get sorting widget unique ID
     *
     * @param boolean $getLast Get last ID or next OPTIONAL
     *
     * @return string
     * @see    ____func_see____
     * @since  1.0.0
     */
    protected function getSortWidgetId($getLast = false)
    {
        $class = get_called_class();

        if (!isset(static::$sortWidgetIds[$class])) {
            static::$sortWidgetIds[$class] = 0;
        }

        if (!$getLast) {
            static::$sortWidgetIds[$class]++;
        }

        return str_replace('\\', '-', $class) . '-sortby-' . static::$sortWidgetIds[$class];
    }

    /**
     * isSortByModeSelected
     *
     * @param string $sortByMode Value to check
     *
     * @return boolean
     * @see    ____func_see____
     * @since  1.0.0
     */
    protected function isSortByModeSelected($sortByMode)
    {
        return $this->getParam(self::PARAM_SORT_BY) == $sortByMode;
    }

    /**
     * isSortOrderAsc
     *
     * @return boolean
     * @see    ____func_see____
     * @since  1.0.0
     */
    protected function isSortOrderAsc()
    {
        return self::SORT_ORDER_ASC == $this->getParam(self::PARAM_SORT_ORDER);
    }

    /**
     * getSortOrderToChange
     *
     * @return string
     * @see    ____func_see____
     * @since  1.0.0
     */
    protected function getSortOrderToChange()
    {
        return $this->isSortOrderAsc() ? self::SORT_ORDER_DESC : self::SORT_ORDER_ASC;
    }

    /**
     * Check if widget is visible
     *
     * @return boolean
     * @see    ____func_see____
     * @since  1.0.0
     */
    protected function isVisible()
    {
        return parent::isVisible() && ($this->isDisplayWithEmptyList() || $this->hasResults());
    }

    /**
     * Auxiliary method to check visibility
     *
     * @return boolean
     * @see    ____func_see____
     * @since  1.0.0
     */
    protected function isDisplayWithEmptyList()
    {
        return false;
    }

    /**
     * Check if there are any results to display in list
     *
     * @return void
     * @see    ____func_see____
     * @since  1.0.0
     */
    protected function hasResults()
    {
        return 0 < $this->getItemsCount();
    }

    /**
     * isHeaderVisible
     *
     * @return boolean
     * @see    ____func_see____
     * @since  1.0.0
     */
    protected function isHeaderVisible()
    {
        return false;
    }

    /**
     * Check if head title is visible
     *
     * @return boolean
     * @see    ____func_see____
     * @since  1.0.0
     */
    protected function isHeadVisible()
    {
        return false;
    }

    /**
     * Check if pager is visible
     *
     * @return boolean
     * @see    ____func_see____
     * @since  1.0.0
     */
    protected function isPagerVisible()
    {
        return $this->getPager()->isVisible();
    }

    /**
     * isFooterVisible
     *
     * @return boolean
     * @see    ____func_see____
     * @since  1.0.0
     */
    protected function isFooterVisible()
    {
        return false;
    }

    /**
     * Define so called "request" parameters
     *
     * @return void
     * @see    ____func_see____
     * @since  1.0.0
     */
    protected function defineRequestParams()
    {
        parent::defineRequestParams();

        $this->requestParams[] = self::PARAM_SORT_BY;
        $this->requestParams[] = self::PARAM_SORT_ORDER;
    }
}
