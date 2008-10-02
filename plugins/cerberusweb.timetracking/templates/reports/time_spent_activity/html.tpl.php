{if $invalidDate}<font color="red"><b>Invalid Date specified.  Please try again.</b></font>{/if}
<br>


{if !empty($time_entries)}
	<table cellspacing="0" cellpadding="3" border="0">
		<tr>
			<td><b>Entry Date</b></td>
			<td><b>Minutes</b></td>
			<td><b>Notes</b></td>
			<td></td>
		</tr>
		{foreach from=$time_entries item=activity_entry key=activity_id}
		
			<tr>
				<td colspan="3" style="border-bottom:1px solid rgb(200,200,200);">
				<h3>
				  {if empty($activity_entry.activity_name)}
				  (no activity)
				  {else}
				  {$activity_entry.activity_name}
				  {/if}
				</h3>
				</td>
			</tr>
			
		
			{foreach from=$activity_entry.entries item=time_entry key=time_entry_id}
			{if is_numeric($time_entry_id)}
			<tr>
				<td style="padding-right:20px;">{$time_entry.log_date|date_format:"%Y-%m-%d"}</td>
				<td align="right">{$time_entry.mins}</td>
				<td align="left">{$time_entry.notes}</td>
				<td></td>
			</tr>
			{/if}
			{/foreach}
			

			<tr>
				<td></td>
				<td style="border-top:1px solid rgb(200,200,200);" align="right"><b>{$activity_entry.total_mins}</b></td>
				<td></td>
				<td style="padding-left:10px;"><b></b></td>
			</tr>


	{/foreach}
	</table>
{/if}

