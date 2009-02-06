{include file="$path/portal/contact/header.tpl"}

{if !empty($last_error)}
	<div class="error" style="width:550px;">
		{$last_error}
	</div>
{/if}

<form action="{devblocks_url}{/devblocks_url}" method="post">
<input type="hidden" name="a" value="doStep2">
<table style="text-align: left; width: 550px;" class="search" border="0" cellpadding="5" cellspacing="5">
  <tbody>
    <tr>
      <td colspan="2">
      	<h4>{$translate->_('portal.contact.write.describe_situation')}</h4>
      	
		{foreach from=$dispatch item=to key=reason}
		{assign var=dispatchKey value=$reason|md5}
			<label><input type="radio" name="nature" value="{$dispatchKey}" {if $displayKey==$last_nature}checked{/if} onclick="this.form.submit();"> {$reason|escape}</label><br>
		{/foreach}
		<!-- <label><input type="radio" name="nature" value="" {if $displayKey==$last_nature}checked{/if}> None of the above</label><br> -->
		
		<br>
		<button type="submit"><img src="{devblocks_url}c=resource&p=usermeet.core&f=images/check.gif{/devblocks_url}" align="top" border="0"> {$translate->_('common.ok')|upper}</button>
		<button type="button" onclick="document.location='{devblocks_url}{/devblocks_url}';"><img src="{devblocks_url}c=resource&p=usermeet.core&f=images/delete.gif{/devblocks_url}" align="top" border="0"> {$translate->_('common.cancel')|capitalize}</button>
		
      </td>
    </tr>
    
    <tr>
    	<td colspan="2" align="right">
    	<span style="font-size:11px;">
    		{assign var=linked_cerberus_helpdesk value="<a href=\"http://www.cerberusweb.com/\" target=\"_blank\" style=\"color:rgb(80,150,0);font-weight:bold;\">"|cat:"Cerberus Helpdesk"|cat:"</a>&trade;"}
    		{'portal.public.powered_by'|devblocks_translate:$linked_cerberus_helpdesk}<br>
		</span>
    	</td>
    </tr>
    
  </tbody>
</table>
</form>
<br>

{include file="$path/portal/contact/footer.tpl"}
