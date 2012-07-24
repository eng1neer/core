{* vim: set ts=2 sw=2 sts=2 et: *}

{**
 * Add user button. Admin area.
 *
 * @author    Creative Development LLC <info@cdev.ru>
 * @copyright Copyright (c) 2011-2012 Creative Development LLC <info@cdev.ru>. All rights reserved
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.litecommerce.com/
 *
 * @ListChild (list="itemsList.profile.search.footer", weight="20")
 *}

<widget class="\XLite\View\Button\AddUser" label="{t(#Add user#)}" location="{buildURL(#profile#,##,_ARRAY_(#mode#^#register#))}" />
