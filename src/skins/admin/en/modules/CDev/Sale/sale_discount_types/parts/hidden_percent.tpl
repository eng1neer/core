{* vim: set ts=2 sw=2 sts=2 et: *}

{**
 * Product element
 *
 * @author    Creative Development LLC <info@cdev.ru>
 * @copyright Copyright (c) 2011-2012 Creative Development LLC <info@cdev.ru>. All rights reserved
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.litecommerce.com/
 *
 * @ListChild (list="sale_discount_types", weight="40")
 *}

 <input IF="getParam(#discountType#)=%\XLite\Model\Product::SALE_DISCOUNT_TYPE_PERCENT%"
   type="hidden"
   id="sale-price-value"
   name="{getNamePostedData(#salePriceValue#)}"
   value="{getPercentOffValue()}" />
