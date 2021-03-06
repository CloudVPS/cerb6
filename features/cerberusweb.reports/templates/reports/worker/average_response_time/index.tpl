<fieldset class="peek">
<legend>{'reports.ui.worker.response_time'|devblocks_translate}</legend>

<b>{'reports.ui.worker.response_time.date_range'|devblocks_translate}</b>
{if $invalidDate}<font color="red"><b>{'reports.ui.invalid_date'|devblocks_translate}</b></font>{/if}

<form action="{devblocks_url}c=pages&page={$page->id}-{$page->name|devblocks_permalink}&report=report.workers.averageresponsetime{/devblocks_url}" method="POST" id="frmRange" name="frmRange">
<input type="text" name="start" id="start" size="24" value="{$start}"><button type="button" onclick="devblocksAjaxDateChooser('#start','#divCal');">&nbsp;<span class="cerb-sprite sprite-calendar"></span>&nbsp;</button>
<input type="text" name="end" id="end" size="24" value="{$end}"><button type="button" onclick="devblocksAjaxDateChooser('#end','#divCal');">&nbsp;<span class="cerb-sprite sprite-calendar"></span>&nbsp;</button>

<div id="divCal"></div>

<a href="javascript:;" onclick="$('#start').val('-1 year');$('#end').val('now');$('#btnSubmit').click();">{'reports.ui.filters.1_year'|devblocks_translate|lower}</a>
| <a href="javascript:;" onclick="$('#start').val('-6 months');$('#end').val('now');$('#btnSubmit').click();">{'reports.ui.filters.n_months'|devblocks_translate:6}</a>
| <a href="javascript:;" onclick="$('#start').val('-3 months');$('#end').val('now');$('#btnSubmit').click();">{'reports.ui.filters.n_months'|devblocks_translate:3}</a>
| <a href="javascript:;" onclick="$('#start').val('-1 month');$('#end').val('now');$('#btnSubmit').click();">{'reports.ui.filters.1_month'|devblocks_translate|lower}</a>
| <a href="javascript:;" onclick="$('#start').val('-1 week');$('#end').val('now');$('#btnSubmit').click();">{'reports.ui.filters.1_week'|devblocks_translate|lower}</a>
| <a href="javascript:;" onclick="$('#start').val('-1 day');$('#end').val('now');$('#btnSubmit').click();">{'reports.ui.filters.1_day'|devblocks_translate|lower}</a>
<div>
	<button type="submit" id="btnSubmit">{'reports.common.run_report'|devblocks_translate|capitalize}</button>
</div>
</form>
</fieldset>

<h3>{'reports.ui.worker.response_time.worker_responses'|devblocks_translate}</h3>
<div style="margin-left:20px;">
	<table cellspacing="0" cellpadding="2" border="0">
		{foreach from=$worker_responses item=responses key=worker_id}
			{if $responses.replies != 0}{math assign=response_time equation="x/y/60" x=$responses.time y=$responses.replies format="%0.1f"}
			{else}{assign var=response_time value=0}{/if}
			<tr>
				<td style="padding-right:20px;">
					<b>{$workers.$worker_id->first_name}&nbsp;{$workers.$worker_id->last_name}</b>&nbsp;&nbsp;({$workers.$worker_id->email})</b>
				</td>
				{if $response_time==0}<td valign="top"></td>
				{elseif $response_time>1440}<td valign="top">{math equation="x/1440" x=$response_time format="%0.1f"} {'common.days'|devblocks_translate|lower}</td>
				{elseif $response_time>60}<td valign="top">{math equation="x/60" x=$response_time format="%0.1f"} {'common.hours'|devblocks_translate|lower}</td>
				{else}<td valign="top">{math equation="x/60" x=$response_time format="%0.1f"} {'common.minutes'|devblocks_translate|lower}</td>{/if}
				<td valign="top">({$responses.replies} replies)</td>
			</tr>
		{/foreach}
	</table>
</div>

<h3>{'reports.ui.worker.response_time.group_responses'|devblocks_translate}</h3>
<div style="margin-left:20px;">
	<table cellspacing="0" cellpadding="2" border="0">
		{foreach from=$group_responses item=responses key=group_id}
			{if $responses.replies != 0}{math assign=response_time equation="x/y/60" x=$responses.time y=$responses.replies format="%0.1f"}
			{else}{assign var=response_time value=0}{/if}
			<tr>
				<td style="padding-right:20px;">
					<b>{$groups.$group_id->name}</b> &nbsp;
				</td>
				{if $response_time==0}<td valign="top"> &nbsp; </td>
				{elseif $response_time>1440}<td valign="top">{math equation="x/1440" x=$response_time format="%0.1f"} {'common.days'|devblocks_translate|lower} &nbsp; </td>
				{elseif $response_time>60}<td valign="top">{math equation="x/60" x=$response_time format="%0.1f"} {'common.hours'|devblocks_translate|lower} &nbsp; </td>
				{else}<td valign="top">{math equation="x/60" x=$response_time format="%0.1f"} {'common.minutes'|devblocks_translate|lower} &nbsp; </td>{/if}
				<td valign="top">({$responses.replies} replies)</td>
			</tr>
		{/foreach}
	</table>
</div>
<br>

