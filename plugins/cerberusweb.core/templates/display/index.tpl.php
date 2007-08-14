<h1>{$ticket->subject}</h1>

{assign var=ticket_team_id value=$ticket->team_id}
{assign var=ticket_team value=$teams.$ticket_team_id}
{assign var=ticket_category_id value=$ticket->category_id}
{assign var=ticket_team_category_set value=$team_categories.$ticket_team_id}
{assign var=ticket_category value=$ticket_team_category_set.$ticket_category_id}

{if !empty($ticket->next_action) && !$ticket->is_closed}<b>Next Action:</b> {$ticket->next_action}<br>{/if}
<b>Team:</b> {$teams.$ticket_team_id->name} &nbsp; 
<b>Bucket:</b> {if !empty($ticket_category_id)}{$ticket_category->name}{else}Inbox{/if} &nbsp; 
<br>
<b>Ticket ID:</b> {$ticket->mask}<br>
<!-- {if !empty($ticket->interesting_words)}<b>Interesting Words:</b> {$ticket->interesting_words}<br>{/if} -->
<!-- <b>Next Action:</b> <input type="text" name="next_step" size="80" value="{$ticket->next_action}" maxlength="255"><br>  -->
<br>
<div id="tourDisplayProperties"></div>
<form action="{devblocks_url}{/devblocks_url}" method="post">
	<input type="hidden" name="c" value="display">
	<input type="hidden" name="a" value="updateProperties">
	<input type="hidden" name="id" value="{$ticket->id}">
	<input type="hidden" name="closed" value="{if $ticket->is_closed}1{else}0{/if}">
	<input type="hidden" name="deleted" value="{if $ticket->is_deleted}1{else}0{/if}">
	<input type="hidden" name="spam" value="0">
	
	{if !$ticket->is_deleted}
		{if $ticket->is_closed}
			<button type="button" onclick="this.form.closed.value=0;this.form.submit();"><img src="{devblocks_url}c=resource&p=cerberusweb.core&f=images/folder_out.gif{/devblocks_url}" align="top"> Re-open</button>
		{else}
			<button type="button" onclick="this.form.closed.value=1;this.form.submit();"><img src="{devblocks_url}c=resource&p=cerberusweb.core&f=images/folder_ok.gif{/devblocks_url}" align="top"> Close</button>
		{/if}
		
		{if empty($ticket->spam_training)}
			<button type="button" onclick="this.form.spam.value=1;this.form.submit();"><img src="{devblocks_url}c=resource&p=cerberusweb.core&f=images/spam.gif{/devblocks_url}" align="top"> Report Spam</button>
		{/if}
	{/if}
	
	{if $ticket->is_deleted}
		<button type="button" onclick="this.form.deleted.value=0;this.form.closed.value=0;this.form.submit();"><img src="{devblocks_url}c=resource&p=cerberusweb.core&f=images/delete_gray.gif{/devblocks_url}" align="top"> Undelete</button>
	{else}
		{if $active_worker && ($active_worker->is_superuser || $active_worker->can_delete)}
		<button type="button" onclick="this.form.deleted.value=1;this.form.closed.value=1;this.form.submit();"><img src="{devblocks_url}c=resource&p=cerberusweb.core&f=images/delete.gif{/devblocks_url}" align="top"> Delete</button>
		{/if}
	{/if}
	
	{if !$ticket->is_deleted}
   	<select name="bucket_id" onchange="this.form.submit();">
   		<option value="">-- move to --</option>
   		{if empty($ticket->category_id)}{assign var=t_or_c value="t"}{else}{assign var=t_or_c value="c"}{/if}
   		<optgroup label="Inboxes">
   		{foreach from=$teams item=team}
   			<option value="t{$team->id}">{$team->name}{if $t_or_c=='t' && $ticket->team_id==$team->id} (current bucket){/if}</option>
   		{/foreach}
   		</optgroup>
   		{foreach from=$team_categories item=categories key=teamId}
   			{assign var=team value=$teams.$teamId}
   			<optgroup label="-- {$team->name} --">
   			{foreach from=$categories item=category}
 				<option value="c{$category->id}">{$category->name}{if $t_or_c=='c' && $ticket->category_id==$category->id} (current bucket){/if}</option>
 			{/foreach}
 			</optgroup>
  		{/foreach}
   	</select>
   	{/if}
	
	<button type="button" onclick="document.location='{devblocks_url}c=display&id={$ticket->mask}{/devblocks_url}';"><img src="{devblocks_url}c=resource&p=cerberusweb.core&f=images/replace2.gif{/devblocks_url}" align="top"> Refresh</button>
	<br>
</form>
<br>

<div id="displayOptions"></div> 
<br>

<script>
{literal}
var tabView = new YAHOO.widget.TabView();

tabView.addTab( new YAHOO.widget.Tab({
    label: 'Conversation',
    dataSrc: '{/literal}{devblocks_url}ajax.php?c=display&a=showConversation&ticket_id={$ticket->id}{/devblocks_url}{literal}',
    cacheData: true,
    active: true
}));

tabView.addTab( new YAHOO.widget.Tab({
    label: 'Recipients',
    dataSrc: '{/literal}{devblocks_url}ajax.php?c=display&a=showManageRecipients&ticket_id={$ticket->id}{/devblocks_url}{literal}',
    cacheData: true
}));

tabView.addTab( new YAHOO.widget.Tab({
    label: 'Sender History',
    dataSrc: '{/literal}{devblocks_url}ajax.php?c=display&a=showContactHistory&ticket_id={$ticket->id}{/devblocks_url}{literal}',
    cacheData: true
}));

tabView.appendTo('displayOptions');
{/literal}
</script>

<script>
	var displayAjax = new cDisplayTicketAjax('{$ticket->id}');
</script>