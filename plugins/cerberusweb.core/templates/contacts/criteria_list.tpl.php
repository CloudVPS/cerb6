{if !empty($view->params)}
<div class="block">
<form action="{devblocks_url}{/devblocks_url}" method="POST" name="{$view->id}_criteriaForm">
<input type="hidden" name="c" value="contacts">
<input type="hidden" name="a" value="viewRemoveCriteria">
<input type="hidden" name="id" value="{$view->id}">
<input type="hidden" name="contacts_page" value="{$contacts_page}">
<input type="hidden" name="field" value="">
<table cellpadding="2" cellspacing="0" width="200" border="0">
	<tr>
		<td nowrap="nowrap">
			<h2 style="display:inline;">Current Criteria</h2>
			[ <a href="javascript:;" onclick="document.{$view->id}_criteriaForm.a.value='viewResetCriteria';document.{$view->id}_criteriaForm.submit();">reset</a> ]
		</td>
	</tr>
	<tr>
		<td>
			<table cellpadding="2" cellspacing="0" border="0">
				{if !empty($view->params)}
				{foreach from=$view->params item=param}
				{assign var=field value=$param->field}
					<tr>
						<td width="100%">
						{if $param->field=='c_name'}
							<img src="{devblocks_url}c=resource&p=cerberusweb.core&f=images/data_find.gif{/devblocks_url}" align="absmiddle"> 
							{$translate->_($search_columns.$field->db_label)|capitalize} 
							{$param->operator} 
							<b>{$param->value}</b>
						{else}
							<img src="{devblocks_url}c=resource&p=cerberusweb.core&f=images/data_find.gif{/devblocks_url}" align="absmiddle"> 
							{$translate->_($search_columns.$field->db_label)|capitalize} 
							{$param->operator}
							<b>{$param->value}</b>
						{/if}
						</td>
						<td width="0%" nowrap="nowrap" valign="top"><a href="javascript:;" onclick="document.{$view->id}_criteriaForm.field.value='{$param->field}';document.{$view->id}_criteriaForm.submit();"><img src="{devblocks_url}c=resource&p=cerberusweb.core&f=images/data_error.gif{/devblocks_url}" border="0" align="absmiddle"></a></td>
					</tr>
				{/foreach}
				{/if}
			</table>
		</td>
	</tr>
</table>
</form>
</div>
<br>
{/if}

<div class="block">
	<form action="{devblocks_url}{/devblocks_url}" method="POST">
	<input type="hidden" name="c" value="contacts">
	<input type="hidden" name="a" value="viewAddCriteria">
	<input type="hidden" name="id" value="{$view->id}">
	<input type="hidden" name="contacts_page" value="{$contacts_page}">
	
	<h2>Add Criteria</h2>
	<b>Field:</b><br>
	<blockquote style="margin:5px;">
		<select name="field" onchange="genericAjaxGet('addCriteriaOptions','c=contacts&a=viewGetCriteria&id={$view->id}&field='+selectValue(this));">
			<option value="">-- choose --</option>
			{foreach from=$search_columns item=column key=token}
				{if $token=="c_id"}
				{elseif $token=="a_id"}
				{elseif $token=="a_contact_org_id"}
				{else}
				<option value="{$token}">{$translate->_($column->db_label)|capitalize}</option>
				{/if}
			{/foreach}
		</select>
	</blockquote>

	<div id="addCriteriaOptions"></div>
	
	</form>
</div>