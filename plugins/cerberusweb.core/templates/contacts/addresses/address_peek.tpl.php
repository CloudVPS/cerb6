<table cellpadding="0" cellspacing="0" border="0" width="98%">
	<tr>
		<td align="left" width="0%" nowrap="nowrap" style="padding-right:5px;"><img src="{devblocks_url}c=resource&p=cerberusweb.core&f=images/businessmen.gif{/devblocks_url}" align="absmiddle"></td>
		<td align="left" width="100%" nowrap="nowrap"><h1>Address Book</h1></td>
	</tr>
</table>

<form action="{devblocks_url}{/devblocks_url}" method="POST" id="formAddressPeek" name="formAddressPeek">
<!-- <input type="hidden" name="action_id" value="{$id}"> -->
<input type="hidden" name="c" value="contacts">
<input type="hidden" name="a" value="">
<input type="hidden" name="id" value="{$address.a_id}">
<input type="hidden" name="view_id" value="{$view_id}">

<div style="height:150px;overflow:auto;border:1px solid rgb(180,180,180);margin:2px;padding:3px;background-color:rgb(255,255,255);">
<table cellpadding="0" cellspacing="2" border="0">
	<tr>
		<td width="0%" nowrap="nowrap" align="right">E-mail: </td>
		<td width="100%"><b>{$address.a_email}</b></td>
	</tr>
	<tr>
		<td width="0%" nowrap="nowrap" align="right">First Name: </td>
		<td width="100%"><input type="text" name="first_name" value="{$address.a_first_name}" style="width:50%;"></td>
	</tr>
	<tr>
		<td width="0%" nowrap="nowrap" align="right">Last Name: </td>
		<td width="100%"><input type="text" name="last_name" value="{$address.a_last_name}" style="width:50%;"></td>
	</tr>
	<tr>
		<td width="0%" nowrap="nowrap" align="right">Organization: </td>
		<td width="100%"><input type="text" name="contact_org" value="{$address.o_name}" style="width:50%;"></td>
	</tr>
</table>
</div>

<input type="button" value="{$translate->_('common.save_changes')}" onclick="ajax.s('{$view_id}');">
<br>
</form>