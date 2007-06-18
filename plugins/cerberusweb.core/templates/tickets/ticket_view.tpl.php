<table cellpadding="0" cellspacing="0" border="0" class="tableBlue" width="100%" class="tableBg">
	<tr>
		<td nowrap="nowrap" class="tableThBlue">{$view->name}</td>
		<td nowrap="nowrap" class="tableThBlue" align="right">
			<a href="javascript:;" onclick="ajax.getRefresh('{$view->id}');" class="tableThLink">{$translate->_('common.refresh')|lower}</a><span style="font-size:12px"> | </span>
			<!-- <a href="javascript:;" onclick="" class="tableThLink">read all</a><span style="font-size:12px"> | </span> -->
			{if $view->id != 'search'}<a href="{devblocks_url}c=tickets&a=searchview&id={$view->id}{/devblocks_url}" class="tableThLink">{$translate->_('common.search')|lower}</a><span style="font-size:12px"> | </span>{/if}
			<a href="javascript:;" onclick="ajax.getCustomize('{$view->id}');" class="tableThLink">{$translate->_('common.customize')|lower}</a>
		</td>
	</tr>
</table>
<form id="customize{$view->id}" action="#" onsubmit="return false;" style="display:none;"></form>
<form id="viewForm{$view->id}" name="viewForm{$view->id}">
<!-- 
<input type="hidden" name="c" value="tickets">
<input type="hidden" name="a" value="runAction">
 -->
<input type="hidden" name="id" value="{$view->id}">
<table cellpadding="0" cellspacing="0" border="0" width="100%" class="tableRowBg">

	{* Column Headers *}
	<tr class="tableTh">
		<th style="text-align:center"><input type="checkbox" onclick="checkAll('view{$view->id}',this.checked);"></th>
		{foreach from=$view->view_columns item=header name=headers}
			{if $header=="t_mask"}
			<th><a href="javascript:;" onclick="ajax.getSortBy('{$view->id}','t_mask');">{$translate->_('ticket.id')}</a></th>
			{*
			{elseif $header=="t_priority"}
			<th><a href="javascript:;" onclick="ajax.getSortBy('{$view->id}','t_priority');">{$translate->_('ticket.priority')}</a></th>
			*}
			{elseif $header=="t_last_wrote"}
			<th><a href="javascript:;" onclick="ajax.getSortBy('{$view->id}','t_last_wrote');">{$translate->_('ticket.last_wrote')}</a></th>
			{elseif $header=="t_first_wrote"}
			<th><a href="javascript:;" onclick="ajax.getSortBy('{$view->id}','t_first_wrote');">{$translate->_('ticket.first_wrote')}</a></th>
			{elseif $header=="t_created_date"}
			<th><a href="javascript:;" onclick="ajax.getSortBy('{$view->id}','t_created_date');">{$translate->_('ticket.created')}</a></th>
			{elseif $header=="t_updated_date"}
			<th><a href="javascript:;" onclick="ajax.getSortBy('{$view->id}','t_updated_date');">{$translate->_('ticket.updated')}</a></th>
			{elseif $header=="t_due_date"}
			<th><a href="javascript:;" onclick="ajax.getSortBy('{$view->id}','t_due_date');">{$translate->_('ticket.due')}</a></th>
{*			{elseif $header=="t_tasks"}
			<th><a href="javascript:;" onclick="ajax.getSortBy('{$view->id}','t_tasks');">{$translate->_('common.tasks')}</a></th> *}
			{elseif $header=="m_name"}
			<th><a href="javascript:;" onclick="ajax.getSortBy('{$view->id}','m_name');">{$translate->_('ticket.mailbox')}</a></th>
			{elseif $header=="t_spam_score"}
			<th><a href="javascript:;" onclick="ajax.getSortBy('{$view->id}','t_spam_score');">{$translate->_('common.spam')}</a></th>
			{elseif $header=="t_next_action"}
			<th><a href="javascript:;" onclick="ajax.getSortBy('{$view->id}','t_next_action');">{$translate->_('ticket.next_action')}</a></th>
			{elseif $header=="tm_name"}
			<th><a href="javascript:;" onclick="ajax.getSortBy('{$view->id}','tm_name');">{$translate->_('common.team')}</a></th>
			{elseif $header=="cat_name"}
			<th><a href="javascript:;" onclick="ajax.getSortBy('{$view->id}','cat_name');">{$translate->_('common.bucket')|capitalize}</a></th>
			{/if}
		{/foreach}
	</tr>

	{* Column Data *}
	{assign var=results value=$view->getTickets()}
	{assign var=total value=$results[1]}
	{assign var=tickets value=$results[0]}
	{foreach from=$tickets item=result key=idx name=results}

	{assign var=rowIdPrefix value="row_"|cat:$view->id|cat:"_"|cat:$result.t_id}
	{if $smarty.foreach.results.iteration % 2}
		{assign var=tableRowBg value="tableRowBg"}
	{else}
		{assign var=tableRowBg value="tableRowAltBg"}
	{/if}
	
		<tr class="{$tableRowBg}" id="{$rowIdPrefix}_s" onmouseover="toggleClass(this.id,'tableRowHover');toggleClass('{$rowIdPrefix}','tableRowHover');" onmouseout="toggleClass(this.id,'{$tableRowBg}');toggleClass('{$rowIdPrefix}','{$tableRowBg}');" onclick="if(getEventTarget(event)=='TD') checkAll('{$rowIdPrefix}_s');">
			<td align="center" rowspan="2"><input type="checkbox" name="ticket_id[]" value="{$result.t_id}"></td>
			<td colspan="{math equation="x" x=$smarty.foreach.headers.total}"><a href="{devblocks_url}c=display&id={$result.t_mask}#latest{/devblocks_url}" class="ticketLink" style="font-size:12px;"><b id="subject_{$result.t_id}_{$view->id}">{if $result.t_is_closed}<strike>{$result.t_subject}</strike>{else}{$result.t_subject}{/if}</b></a> <a href="javascript:;" onclick="ajax.scheduleTicketPreview('{$result.t_id}',this);" title="Preview">&raquo;</a></td>
		</tr>
		<tr class="{$tableRowBg}" id="{$rowIdPrefix}" onmouseover="toggleClass(this.id,'tableRowHover');toggleClass('{$rowIdPrefix}_s','tableRowHover');" onmouseout="toggleClass(this.id,'{$tableRowBg}');toggleClass('{$rowIdPrefix}_s','{$tableRowBg}');" onclick="if(getEventTarget(event)=='TD') checkAll('{$rowIdPrefix}_s');">
		{foreach from=$view->view_columns item=column name=columns}
			{if $column=="t_mask"}
			<td><a href="{devblocks_url}c=display&id={$result.t_mask}{/devblocks_url}">{$result.t_mask}</a></td>
			{*
			{elseif $column=="t_priority"}
			<td>
				{if $result.t_priority >= 75}
					<img src="{devblocks_url}c=resource&p=cerberusweb.core&f=images/star_red.gif{/devblocks_url}" title="{$result.t_priority}">
				{elseif $result.t_priority >= 50}
					<img src="{devblocks_url}c=resource&p=cerberusweb.core&f=images/star_yellow.gif{/devblocks_url}" title="{$result.t_priority}">
				{elseif $result.t_priority >= 25}
					<img src="{devblocks_url}c=resource&p=cerberusweb.core&f=images/star_green.gif{/devblocks_url}" title="{$result.t_priority}">
				{else}
					<img src="{devblocks_url}c=resource&p=cerberusweb.core&f=images/star_alpha.gif{/devblocks_url}" title="{$result.t_priority}">
				{/if}
			</td>
			*}
			{elseif $column=="t_last_wrote"}
			<td><a href="javascript:;" onclick="genericAjaxPanel('c=tickets&a=showContactPanel&address={$ticket->last_wrote}',this);">{$result.t_last_wrote}</a></td>
			{elseif $column=="t_first_wrote"}
			<td><a href="javascript:;" onclick="genericAjaxPanel('c=tickets&a=showContactPanel&address={$ticket->first_wrote}',this);">{$result.t_first_wrote}</a></td>
			{elseif $column=="t_created_date"}
			<td>{$result.t_created_date|date_format}</td>
			{elseif $column=="t_updated_date"}
			<td>{$result.t_updated_date|date_format}</td>
			{elseif $column=="t_due_date"}
			<td>{if $result.t_due_date}{$result.t_due_date|date_format}{/if}</td>
			{*{elseif $column=="t_tasks"}
			<td align='center'>{if !empty($result.t_tasks)}{$result.t_tasks}{/if}</td>*}
			{elseif $column=="tm_name"}
			<td><a href="{devblocks_url}c=tickets&a=dashboards&m=team&id={$result.tm_id}{/devblocks_url}">{$result.tm_name}</a></td>
			{elseif $column=="cat_name"}
			<td>{$result.cat_name}</td>
			{elseif $column=="t_next_action"}
			<td title="{$result.t_next_action}">{$result.t_next_action|truncate:35:'...'}</td>
			{elseif $column=="t_spam_score"}
			<td>
				{math assign=score equation="x*100" format="%0.2f%%" x=$result.t_spam_score}
				{if empty($result.t_spam_training)}
				<!---<a href="javascript:;"><img src="{devblocks_url}c=resource&p=cerberusweb.core&f=images/warning.gif{/devblocks_url}" align="top" border="0" title="Not Spam ({$score})"></a>--->
				<!---<a href="javascript:;"><img src="{devblocks_url}c=resource&p=cerberusweb.core&f=images/check_gray.gif{/devblocks_url}" align="top" border="0" title="Not Spam ({$score})"></a>--->
				<a href="javascript:;" onclick="toggleDiv('{$rowIdPrefix}_s','none');toggleDiv('{$rowIdPrefix}','none');genericAjaxGet(null,'c=tickets&a=reportSpam&id={$result.t_id}');"><img src="{devblocks_url}c=resource&p=cerberusweb.core&f=images/{if $result.t_spam_score >= .90}warning.gif{else}warning_gray.gif{/if}{/devblocks_url}" align="top" border="0" title="Report Spam ({$score})
				{if !empty($result.t_interesting_words)}{$result.t_interesting_words}{/if}"></a>
				{/if}
			</td>
			{/if}
		{/foreach}
		</tr>
	{/foreach}
	
</table>
<table cellpadding="2" cellspacing="0" border="0" width="100%" class="tableBg">
	<tr>
		<td colspan="2">
		    <!-- 
			<select name="action_id" onchange="toggleDiv('action{$view->id}',(this.selectedIndex>0)?'inline':'none');">
				<option value="">-- perform shortcut --</option>
				<optgroup label="Shared Shortcuts" style="color:rgb(0,180,0);">
				{foreach from=$viewActions item=action}
				<option value="{$action->id}">{$action->name}</option>
				{/foreach}
				</optgroup>
			</select>
			<span id="action{$view->id}" style="display:none;">
				<input type="button" value="Apply" onclick="ajax.viewRunAction('{$view->id}');">
				<a href="javascript:;" onclick="genericAjaxPanel('c=tickets&a=showViewActions&id='+selectValue(document.getElementById('viewForm{$view->id}').action_id)+'&view_id={$view->id}',this,true,'500px');">edit shortcut</a> | 
			</span>
			 -->
			
			<span id="tourDashboardBatch"><button type="button" onclick="ajax.showBatchPanel('{$view->id}','{$dashboard_team_id}');">bulk update</button></span> <!-- genericAjaxPanel('c=tickets&a=showBatchPanel&view_id={$view->id}',this,true,'500px'); -->
			<!-- <button type="button" onclick="ajax.showCategorizePanel('{$view->id}');">move</button>  -->
			<button type="button" onclick="ajax.viewCloseTickets('{$view->id}',0);">close</button>
			<button type="button" onclick="ajax.viewCloseTickets('{$view->id}',1);">report spam</button>
			<button type="button" onclick="ajax.viewCloseTickets('{$view->id}',2);">delete</button>
			
			<input type="hidden" name="move_to" value="">
			<select name="move_to_select" onchange="this.form.move_to.value=this.form.move_to_select[this.selectedIndex].value;ajax.viewMoveTickets('{$view->id}');">
				<option value="">-- move to --</option>
				<optgroup label="Inboxes" style="">
					{foreach from=$teams item=team}
						<option value="t{$team->id}">{$team->name}</option>
					{/foreach}
					</optgroup>
					{foreach from=$team_categories item=categories key=teamId}
						{assign var=team value=$teams.$teamId}
						<optgroup label="-- {$team->name} --">
						{foreach from=$categories item=category}
					<option value="c{$category->id}">{$category->name}</option>
				{/foreach}
				</optgroup>
				{/foreach}
			</select>
			
		</td>
	</tr>
	<tr>
		<td align="left" valign="top">
			{if !empty($move_to_counts)}
			<span style="font-size:100%;">
			<b>Move: </b>
				{foreach from=$move_to_counts item=move_count key=move_code}
					{assign var=move_to_name value=$category_name_hash.$move_code}
					{if !empty($move_to_name)}
						<b>&raquo;</b><a href="javascript:;" onclick="document.viewForm{$view->id}.move_to.value='{$move_code}';ajax.viewMoveTickets('{$view->id}');" title="Used {$move_count} times.">{$move_to_name}</a>
					{/if}
				{/foreach}
			</span>
			{/if}
		</td>
		<td align="right" valign="top" nowrap="nowrap">
			{math assign=fromRow equation="(x*y)+1" x=$view->renderPage y=$view->renderLimit}
			{math assign=toRow equation="(x-1)+y" x=$fromRow y=$view->renderLimit}
			{math assign=nextPage equation="x+1" x=$view->renderPage}
			{math assign=prevPage equation="x-1" x=$view->renderPage}
			{math assign=lastPage equation="ceil(x/y)-1" x=$total y=$view->renderLimit}
			
			{* Sanity checks *}
			{if $toRow > $total}{assign var=toRow value=$total}{/if}
			{if $fromRow > $toRow}{assign var=fromRow value=$toRow}{/if}
			
			{if $view->renderPage > 0}
				<a href="javascript:;" onclick="ajax.getPage('{$view->id}',0);">&lt;&lt;</a>
				<a href="javascript:;" onclick="ajax.getPage('{$view->id}','{$prevPage}');">&lt;{$translate->_('common.prev')|capitalize}</a>
			{/if}
			(Showing {$fromRow}-{$toRow} of {$total})
			{if $toRow < $total}
				<a href="javascript:;" onclick="ajax.getPage('{$view->id}','{$nextPage}');">{$translate->_('common.next')|capitalize}&gt;</a>
				<a href="javascript:;" onclick="ajax.getPage('{$view->id}','{$lastPage}');">&gt;&gt;</a>
			{/if}
		</td>
	</tr>
</table>
</form>
<br>