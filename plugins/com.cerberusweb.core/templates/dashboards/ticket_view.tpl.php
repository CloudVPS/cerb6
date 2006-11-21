<table cellpadding="0" cellspacing="0" border="0" class="tableBlue" width="100%" class="tableBg">
	<tr>
		<td nowrap="nowrap" class="tableThBlue">My Tickets</td>
		<td nowrap="nowrap" class="tableThBlue" align="right">
			<a href="javascript:;" onclick="" class="tableThLink">refresh</a><span style="font-size:12px"> | </span>
			<a href="index.php?c=core.module.dashboard&a=searchview&id={$id}" class="tableThLink">search</a><span style="font-size:12px"> | </span>
			<a href="javascript:;" onclick="getCustomize({$id});" class="tableThLink">customize</a>
		</td>
	</tr>
</table>
<div id="customize{$id}"></div>
<table cellpadding="0" cellspacing="0" border="0" width="100%" class="tableRowBg">

	{* Column Headers *}
	<tr class="tableTh">
		<th style="text-align:center"><a href="#">all</a></th>
		<th><a href="#">ID</a></th>
		<th><a href="#">Status</a></th>
		<th><a href="#">Last Wrote</a></th>
	</tr>

	{* Column Data *}
	{foreach from=$tickets item=ticket key=idx name=tickets}
		<tr class="{if $smarty.foreach.tickets.iteration % 2}tableRowBg{else}tableRowAltBg{/if}">
			<td align="center" rowspan="2"><input type="checkbox" name="ticket_id[]" value=""></td>
			<td colspan="3"><a href="index.php?c=core.module.dashboard&a=viewticket&id={$ticket->id}" class="ticketLink"><b>{$ticket->subject}</b></a></td>
		</tr>
		<tr class="{if $smarty.foreach.tickets.iteration % 2}tableRowBg{else}tableRowAltBg{/if}">
			<td><a href="#" style="font-size:90%">{$ticket->mask}</a></td>
			<td>{$ticket->status}</td>
			<td><a href="#" style="font-size:90%">{$ticket->last_wrote}</a></td>
		</tr>
		<tr>
			<td class="tableBg" colspan="4"></td>
		</tr>
	{/foreach}
	
</table>
<table cellpadding="2" cellspacing="0" border="0" width="100%" class="tableBg">
	<tr>
		<td>
			<select name="">
				<option value="">-- perform action --
			</select>
		</td>
	</tr>
	<tr>
		<td align="right">
			(Showing x-y of {$total})
		</td>
	</tr>
</table>
