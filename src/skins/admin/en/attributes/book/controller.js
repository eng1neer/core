/* vim: set ts=2 sw=2 sts=2 et: */

/**
 * JS support for the attributes book
 *  
 * @author    Creative Development LLC <info@cdev.ru> 
 * @copyright Copyright (c) 2011 Creative Development LLC <info@cdev.ru>. All rights reserved
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://www.litecommerce.com/
 * @since     1.0.14
 */

jQuery().ready(
  function() {
    jQuery('button#new_attribute_button').click(
      function () {
        jQuery('div.group.hidden').hide();
      }
    );

    jQuery('button#new_group_button').click(
      function () {
        jQuery('div.attribute.hidden').hide();
      }
    );

    jQuery('div.attribute-change-label').click(
      function () {
        jQuery(this).nextAll('table.attribute-properties').toggle();
      }
    );

    jQuery('div.group div.delete.group').click(
      function () {
        var row = jQuery(this).closest('li.row');
        var box = jQuery(this).children('input[name*="toDelete"]');

        row.toggleClass('row-to-delete');
        row.find('div.group-attributes').toggleClass('row-to-delete');

        box.val(true == box.val() ? 0 : 1);
      }
    );

    jQuery('div.attribute div.delete.attribute').click(
      function () {
        var row = jQuery(this).closest('li.row');
        var box = jQuery(this).children('input[name*="toDelete"]');

        row.toggleClass('row-to-delete');
        box.val(true == box.val() ? 0 : 1);
      }
    );

    var typeSelector = jQuery('select[name="postedData[_][attributes][_][class]"]');

    typeSelector.change(
      function () {
        var parentRow = jQuery(this).parents('tr');

        parentRow.nextAll('tr.additional-properties').hide();
        parentRow.nextAll('tr#' + jQuery("option:selected",this).val().toLowerCase() + '_additional').show();
      }
    );

    typeSelector.change();
  }
);
