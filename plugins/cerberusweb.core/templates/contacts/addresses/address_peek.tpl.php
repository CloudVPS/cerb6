<table cellpadding="0" cellspacing="0" border="0" width="98%">
	<tr>
		<td align="left" width="0%" nowrap="nowrap" style="padding-right:5px;"><img src="{devblocks_url}c=resource&p=cerberusweb.core&f=images/businessmen.gif{/devblocks_url}" align="absmiddle"></td>
		<td align="left" width="100%" nowrap="nowrap"><h1>Address Book</h1></td>
	</tr>
</table>

<form action="{devblocks_url}{/devblocks_url}" method="POST" id="formAddressPeek" name="formAddressPeek" onsubmit="return false;">
<!-- <input type="hidden" name="action_id" value="{$id}"> -->
<input type="hidden" name="c" value="contacts">
<input type="hidden" name="a" value="saveContact">
<input type="hidden" name="id" value="{$address.a_id}">
<input type="hidden" name="view_id" value="{$view_id}">

<table cellpadding="0" cellspacing="2" border="0" width="98%">
	<tr>
		<td width="0%" nowrap="nowrap" align="right">E-mail: </td>
		<td width="100%">
			{if $id == 0}
				{if !empty($email)}
					<input type="hidden" name="email" value="{$email}">
					<b>{$email}</b>
				{else}
					<input type="text" name="email" style="width:98%;" value="{$email}">
				{/if}
			{else}
				<b>{$address.a_email}</b>

				{* Domain Shortcut *}
				{assign var=email_parts value=$address.a_email|explode:'@'}
				{if is_array($email_parts) && 2==count($email_parts)}
					(<a href="http://{$email_parts.1}" target="_blank">{$email_parts.1}</a>)
				{/if}
			{/if}
		</td>
	</tr>
	<tr>
		<td width="0%" nowrap="nowrap" align="right">First Name: </td>
		<td width="100%"><input type="text" name="first_name" value="{$address.a_first_name}" style="width:98%;"></td>
	</tr>
	<tr>
		<td width="0%" nowrap="nowrap" align="right">Last Name: </td>
		<td width="100%"><input type="text" name="last_name" value="{$address.a_last_name}" style="width:98%;"></td>
	</tr>
	<tr>
		<td width="0%" nowrap="nowrap" align="right" valign="top">Organization: </td>
		<td width="100%" valign="top">
			<div id="contactautocomplete" style="width:98%;" class="yui-ac">
				<input type="text" name="contact_org" id="contactinput" value="{if !empty($address.a_contact_org_id)}{$address.o_name}{else}{$org_name}{/if}" class="yui-ac-input">
				<div id="contactcontainer" class="yui-ac-container"></div>
			</div>			
			<input type="hidden" name="contact_orgid" value="{if !empty($address.a_contact_org_id)}{$address.a_contact_org_id}{else}{$org_id}{/if}"/>
			<br>
			<br>
		</td>
	</tr>
	<tr>
		<td width="0%" nowrap="nowrap" align="right">Phone: </td>
		<td width="100%"><input type="text" name="phone" value="{$address.a_phone}" style="width:98%;"></td>
	</tr>
	{if !empty($slas)}
	<tr>
		<td width="0%" nowrap="nowrap" align="right">Service Level: </td>
		<td width="100%">
			<select name="sla_id">
				<option value="0">-- none --</option>
				{foreach from=$slas item=sla key=sla_id}
					<option value="{$sla_id}" {if $sla_id==$address.a_sla_id}selected{/if}>{$sla->name|escape}</option>
				{/foreach}
			</select>
		</td>
	</tr>
	{/if}
	{if $id != 0}
	<tr>
		<td colspan="2" align="center">
			<input type="hidden" name="closed" value="0">
			<a href="javascript:;" onclick="document.formAddressPeek.a.value='showAddressTickets';document.formAddressPeek.closed.value='0';document.formAddressPeek.submit();">{$open_count} open ticket(s)</a>
			 | 
			<a href="javascript:;" onclick="document.formAddressPeek.a.value='showAddressTickets';document.formAddressPeek.closed.value='1';document.formAddressPeek.submit();">{$closed_count} closed ticket(s)</a>
		</td>
	</tr>
	{/if}
</table>

<button type="button" onclick="genericPanel.hide();genericAjaxPost('formAddressPeek', 'view{$view_id}')"><img src="{devblocks_url}c=resource&p=cerberusweb.core&f=images/check.gif{/devblocks_url}" align="top"> {$translate->_('common.save_changes')}</button>
<button type="button" onclick="genericPanel.hide();"><img src="{devblocks_url}c=resource&p=cerberusweb.core&f=images/delete.gif{/devblocks_url}" align="top"> {$translate->_('common.close')|capitalize}</button>
<br>
</form>
