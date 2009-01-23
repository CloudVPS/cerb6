<form action="{devblocks_url}{/devblocks_url}" method="post" id="frmOrgFields">
<input type="hidden" name="c" value="contacts">
<input type="hidden" name="a" value="saveOrgProperties">
<input type="hidden" name="org_id" value="{$org_id}">

<blockquote style="margin:10px;">

	<table cellpadding="0" cellspacing="2" border="0" width="98%">
		<tr>
			<td width="0%" nowrap="nowrap" align="right">Name: </td>
			<td width="100%"><input type="text" name="org_name" value="{$contact->name|escape}" style="width:98%;"></td>
		</tr>
		<tr>
			<td align="right" valign="top">Street: </td>
			<td><textarea name="street" style="width:98%;height:50px;">{$contact->street}</textarea></td>
		</tr>
		<tr>
			<td align="right">City: </td>
			<td><input type="text" name="city" value="{$contact->city|escape}" style="width:98%;"></td>
		</tr>
		<tr>
			<td align="right">State/Prov.: </td>
			<td><input type="text" name="province" value="{$contact->province|escape}" style="width:98%;"></td>
		</tr>
		<tr>
			<td align="right">Postal: </td>
			<td><input type="text" name="postal" value="{$contact->postal|escape}" style="width:98%;"></td>
		</tr>
		<tr>
			<td align="right">Country: </td>
			<td>
			
				<div id="org_country_autocomplete" style="width:98%;" class="yui-ac">
					<input type="text" name="country" id="org_country_input" value="{$contact->country|escape}" class="yui-ac-input">
					<div id="org_country_container" class="yui-ac-container"></div>
				</div>			
				<br>
				<br>
			</td>
		</tr>
		<tr>
			<td align="right">Phone: </td>
			<td><input type="text" name="phone" value="{$contact->phone|escape}" style="width:98%;"></td>
		</tr>
		<tr>
			<td align="right">Fax: </td>
			<td><input type="text" name="fax" value="{$contact->fax|escape}" style="width:98%;"></td>
		</tr>
		<tr>
			<td align="right">{if !empty($contact->website)}<a href="{$contact->website|escape}" target="_blank">Website</a>{else}Website{/if}: </td>
			<td><input type="text" name="website" value="{$contact->website|escape}" style="width:98%;"></td>
		</tr>
	</table>

	<table cellpadding="2" cellspacing="1" border="0">
	<tr>
		<td colspan="2">&nbsp;</td>
	</tr>
	{foreach from=$org_fields item=f key=f_id}
		<tr>
			<td valign="top" width="1%" nowrap="nowrap">
				<input type="hidden" name="field_ids[]" value="{$f_id}">
				{$f->name}:
			</td>
			<td valign="top" width="99%">
				{if $f->type=='S'}
					<input type="text" name="field_{$f_id}" size="45" maxlength="255" value="{$org_field_values.$f_id|escape}"><br>
				{elseif $f->type=='T'}
					<textarea name="field_{$f_id}" rows="4" cols="50" style="width:98%;">{$org_field_values.$f_id}</textarea><br>
				{elseif $f->type=='C'}
					<input type="checkbox" name="field_{$f_id}" value="1" {if $org_field_values.$f_id}checked{/if}><br>
				{elseif $f->type=='D'}
					<select name="field_{$f_id}">{* [TODO] Fix selected *}
						<option value=""></option>
						{foreach from=$f->options item=opt}
						<option value="{$opt|escape}" {if $opt==$org_field_values.$f_id}selected{/if}>{$opt}</option>
						{/foreach}
					</select><br>
				{elseif $f->type=='E'}
					<input type="text" name="field_{$f_id}" size="45" maxlength="255" value="{if !empty($org_field_values.$f_id)}{$org_field_values.$f_id|devblocks_date}{/if}"><button type="button" onclick="ajax.getDateChooser('dateCustom{$f_id}',this.form.field_{$f_id});">&nbsp;<img src="{devblocks_url}c=resource&p=cerberusweb.core&f=images/calendar.gif{/devblocks_url}" align="top">&nbsp;</button>
					<div id="dateCustom{$f_id}" style="display:none;position:absolute;z-index:1;"></div>
				{/if}	
			</td>
		</tr>
	{/foreach}
	
	</table>
	<br>
	
	<button type="submit"><img src="{devblocks_url}c=resource&p=cerberusweb.core&f=images/check.gif{/devblocks_url}" align="top"> {$translate->_('common.save_changes')|capitalize}</button>
</blockquote>

</form>