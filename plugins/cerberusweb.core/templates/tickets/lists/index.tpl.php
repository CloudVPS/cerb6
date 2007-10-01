<div style="position: relative; width:100%; height: 30;">
	<span style="position: absolute; left: 0; top:0;"><h1 style="display:inline;">My Workspaces</h1>&nbsp;
		{include file="file:$path/tickets/menu.tpl.php"}
	</span>
	<span style="position: absolute; right: 0; top:0;">
		<form action="{devblocks_url}{/devblocks_url}" method="post">
		<input type="hidden" name="c" value="tickets">
		<input type="hidden" name="a" value="doQuickSearch">
		<span id="tourHeaderQuickLookup"><b>Quick Search:</b></span> <select name="type">
			<option value="sender"{if $quick_search_type eq 'sender'}selected{/if}>Sender</option>
			<option value="mask"{if $quick_search_type eq 'mask'}selected{/if}>Ticket ID</option>
			<option value="subject"{if $quick_search_type eq 'subject'}selected{/if}>Subject</option>
			<option value="content"{if $quick_search_type eq 'content'}selected{/if}>Content</option>
		</select><input type="text" name="query" size="24"><input type="submit" value="go!">
		</form>
	</span>
</div>

<table border="0" cellpadding="0" cellspacing="0" width="100%">
  <tbody>
    <tr>
      <td nowrap="nowrap" width="0%" valign="top">

      {include file="file:$path/tickets/lists/dashboard_menu.tpl.php"}
      
      </td>
      <td nowrap="nowrap" width="0%"><img src="{devblocks_url}c=resource&p=cerberusweb.core&f=images/spacer.gif{/devblocks_url}" width="5" height="1"></td>
      <td nowrap="nowrap" width="100%" valign="top">
      
      <div id="tourDashboardViews"></div>
      {if !empty($views)}
		{foreach from=$views item=view name=views}
			<div id="view{$view->id}">
			{$view->render()}
			</div>
		{/foreach}
      {else}
      	<div class="block">
			<h2>No Worklists</h2>
			No worklists have been created.<br>
		</div>
		<br>
      {/if}
      
      {include file="file:$path/tickets/whos_online.tpl.php"}
      	
      </td>
    </tr>
  </tbody>
</table>

