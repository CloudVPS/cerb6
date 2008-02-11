<input type="hidden" name="c" value="internal">
<input type="hidden" name="a" value="viewSaveCustomize">
<input type="hidden" name="id" value="{$view->id}">
<div style="background-color: #EEEEEE;padding:5px;">
<h1>{$translate->_('common.customize')|capitalize}</h1>

{* Custom Views *}
{if substr($view->id,0,5)=="cust_"}
	{assign var=is_custom value=true}
{else}
	{assign var=is_custom value=false}
{/if}

{if $is_custom}
<b>List Title:</b><br>
<input type="text" name="title" value="{$view->name|escape}" size="64" autocomplete="off"><br>
<br>
{/if}

<b>{$translate->_('dashboard.columns')|capitalize}:</b><br>
{section start=0 step=1 loop=15 name=columns}
{assign var=index value=$smarty.section.columns.index}
{math equation="x+1" x=$index format="%02d"}: 
<select name="columns[]">
	<option value=""></option>
	
	{foreach from=$optColumns item=optColumn}
		{if substr($optColumn->token,0,3) != "cf_"}
			{* [TODO] These should be excluded in the abstract class, not here *}
			{if $optColumn->token=="a_contact_org_id"}
			{elseif $optColumn->token=="a_id"}
			{elseif $optColumn->token=="c_id"}
			{else}
				{if !empty($optColumn->db_label) && !empty($optColumn->token)}
					<option value="{$optColumn->token}" {if $view->view_columns.$index==$optColumn->token}selected{/if}>{$translate->_($optColumn->db_label)|capitalize}</option>
				{/if}
			{/if}
		{/if}
	{/foreach}
	
	{if 1}
	<optgroup label="Custom Fields">
	{foreach from=$optColumns item=optColumn}
		{if substr($optColumn->token,0,3) == "cf_"}
			{if !empty($optColumn->db_label) && !empty($optColumn->token)}
			<option value="{$optColumn->token}" {if $view->view_columns.$index==$optColumn->token}selected{/if}>{$translate->_($optColumn->db_label)|capitalize}</option>
			{/if}
		{/if}
	{/foreach}
	</optgroup>
	{/if}
	
</select>
<br>
{/section}
<br>
<b>{$translate->_('dashboard.num_rows')|capitalize}:</b> <input type="text" name="num_rows" size="3" maxlength="3" value="{$view->renderLimit}"><br>
<br>

{if $is_custom}
<b>Criteria:</b><br>
<div id="viewCustomFilters{$view->id}" style="margin:10px;">
{include file="$path/internal/views/customize_view_criteria.tpl.php"}
</div>
<br>
{/if}

<button type="button" onclick="this.form.a.value='viewSaveCustomize';genericAjaxPost('customize{$view->id}','view{$view->id}','c=internal');"><img src="{devblocks_url}c=resource&p=cerberusweb.core&f=images/check.gif{/devblocks_url}" align="top"> {$translate->_('common.save_changes')|capitalize}</button>
<button type="button" onclick="toggleDiv('customize{$view->id}','none');"><img src="{devblocks_url}c=resource&p=cerberusweb.core&f=images/delete.gif{/devblocks_url}" align="top"> {$translate->_('common.cancel')|capitalize}</button>
{if $is_custom}<button type="button" onclick="if(confirm('Are you sure you want to permanently delete this worklist?')){literal}{{/literal}this.form.a.value='viewDelete';genericAjaxPost('customize{$view->id}','view{$view->id}','c=internal');{literal}}{/literal}"><img src="{devblocks_url}c=resource&p=cerberusweb.core&f=images/delete2.gif{/devblocks_url}" align="top"> {$translate->_('common.delete')|capitalize}</button>{/if}
<!-- <button type="button" onclick="genericAjaxGet('view{$view->id}','c=internal&a=viewRefresh&id={$view->id}');"><img src="{devblocks_url}c=resource&p=cerberusweb.core&f=images/delete.gif{/devblocks_url}" align="top"> {$translate->_('common.close')|capitalize}</button> -->

<br>
<br>
</div>