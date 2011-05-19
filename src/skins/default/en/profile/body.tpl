{* vim: set ts=2 sw=2 sts=2 et: *}

{**
 * Profile menu
 *
 * @author    Creative Development LLC <info@cdev.ru>
 * @copyright Copyright (c) 2011 Creative Development LLC <info@cdev.ru>. All rights reserved
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.litecommerce.com/
 * @since     1.0.0
 *}
<ul class="menu">
  <li FOREACH="getViewList(#menu.profile#),i,w">{w.display()}</li>
</ul>
