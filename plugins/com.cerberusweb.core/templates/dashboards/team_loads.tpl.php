<table cellpadding="0" cellspacing="0" border="0" class="tableOrange" width="220" class="tableBg">
	<tr>
		<td class="tableThOrange" nowrap="nowrap"> <img src="images/businessmen.gif"> {$translate->say('dashboard.team_loads')|capitalize}</td>
	</tr>
	<tr>
		<td>
			<table cellpadding="0" cellspacing="1" border="0" width="100%" class="tableBg">
				{foreach from=$teams item=team}
					{if $team_total_count}
						{math assign=percent equation="(x/y)*50" x=$team->count y=$team_total_count format="%0.0f"}
					{/if}
				<tr>
					<td class="tableCellBg" width="100%" style="padding:2px;"><a href="index.php?c=core.module.dashboard&a=clickteam&id={$team->id}"><b>{$team->name}</b></a> ({$team->count})</td>
					<td class="tableCellBgIndent" width="0%" nowrap="nowrap" style="width:50px;"><img src="images/cerb_graph.gif" width="{$percent}" height="15"><img src="images/cer_graph_cap.gif" height="15" width="1"></td>
				</tr>
				{foreachelse}
				<tr>
					<td colspan="2" class="tableCellBg">{$translate->say('dashboard.no_teams')}</td>
				</tr>
				{/foreach}
			</table>
		</td>
	</tr>
</table>
