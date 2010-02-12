{* Recently viewed body *}

<widget class="XLite_Module_ProductAdviser_View_Pager" data="{newArrivalsProducts}" name="pager" itemsPerPage="{config.ProductAdviser.number_new_arrivals}" pageIDX="naPageID" extraParameter="pageID">

<table cellpadding="0" cellspacing="0" border="0">
<tbody FOREACH="pager.pageData,NA">
<tr>
	<td IF="config.General.show_thumbnails" valign="top" align="center" width="80">
		<!-- Product thumbnail -->
		<widget visible="{NA.hasThumbnail()}" template="common/product_thumbnail.tpl" href="cart.php?target=product&product_id={NA.product_id}&category_id={NA.category.category_id}" thumbnail="{NA.thumbnailURL}" margin="0px 5px 5px 5px">
	</td>
	<td valign=top>
	<a href="cart.php?target=product&amp;product_id={NA.product_id}&amp;category_id={NA.category.category_id}"><FONT class="ProductTitle">{NA.name:h}</FONT></a>
	<br>
	{truncate(NA,#brief_description#,#300#):h}<br>
	<br>
	<table cellpadding="0" cellspacing="0" border="0">
    <tr>
    	<td>
    	<FONT class="ProductPriceTitle">Price: </FONT><FONT class="ProductPrice">{price_format(NA,#listPrice#):h}</FONT><FONT class="ProductPriceTitle"> {NA.priceMessage:h}</FONT>
    	</td>
    	<td>
    	&nbsp;&nbsp;
    	</td>
    	<td>
    	(<a href="cart.php?target=product&amp;product_id={NA.product_id}&amp;category_id={NA.category.category_id}"><u>More information</u></a>)
    	</td>
    </tr>
    </table>
	</td>
</tr>
<tr IF="!NAArrayPointer=NAArraySize">
	<td colspan=2 height=1 class="TableHead"></td>
</tr>
<tr IF="!NAArrayPointer=NAArraySize">
	<td colspan=2>&nbsp;</td>
</tr>
</tbody>
</table>

<widget name="pager">
