{$view_fields = $view->getColumnsAvailable()}
{$results = $view->getData()}
{$total = $results[1]}
{$data = $results[0]}
<table cellpadding="0" cellspacing="0" border="0" class="worklist" width="100%">
	<tr>
		<td nowrap="nowrap"><span class="title">{$view->name}</span></td>
		<td nowrap="nowrap" align="right">
			<a href="javascript:;" title="{'common.customize'|devblocks_translate|capitalize}" class="minimal" onclick="genericAjaxGet('customize{$view->id}','c=internal&a=viewCustomize&id={$view->id}');toggleDiv('customize{$view->id}','block');"><span class="cerb-sprite2 sprite-gear"></span></a>
			<a href="javascript:;" title="Subtotals" class="subtotals minimal"><span class="cerb-sprite2 sprite-application-sidebar-list"></span></a>
			<a href="javascript:;" title="{'common.export'|devblocks_translate|capitalize}" class="minimal" onclick="genericAjaxGet('{$view->id}_tips','c=internal&a=viewShowExport&id={$view->id}');toggleDiv('{$view->id}_tips','block');"><span class="cerb-sprite2 sprite-application-export"></span></a>
			<a href="javascript:;" title="{'common.copy'|devblocks_translate|capitalize}" onclick="genericAjaxGet('{$view->id}_tips','c=internal&a=viewShowCopy&view_id={$view->id}');toggleDiv('{$view->id}_tips','block');"><span class="cerb-sprite2 sprite-applications"></span></a>
			<a href="javascript:;" title="{'common.refresh'|devblocks_translate|capitalize}" class="minimal" onclick="genericAjaxGet('view{$view->id}','c=internal&a=viewRefresh&id={$view->id}');"><span class="cerb-sprite2 sprite-arrow-circle-135-left"></span></a>
			<input type="checkbox" class="select-all">
		</td>
	</tr>
</table>

<div id="{$view->id}_tips" class="block" style="display:none;margin:10px;padding:5px;">Loading...</div>
<form id="customize{$view->id}" name="customize{$view->id}" action="#" onsubmit="return false;" style="display:none;"></form>
<form id="viewForm{$view->id}" name="viewForm{$view->id}" action="{devblocks_url}{/devblocks_url}" method="post">
<input type="hidden" name="view_id" value="{$view->id}">
<input type="hidden" name="c" value="mail">
<input type="hidden" name="a" value="handleSectionAction">
<input type="hidden" name="section" value="drafts">
<input type="hidden" name="action" value="">
<input type="hidden" name="explore_from" value="0">
<table cellpadding="1" cellspacing="0" border="0" width="100%" class="worklistBody">

	{* Column Headers *}
	<thead>
	<tr>
		{foreach from=$view->view_columns item=header name=headers}
			{* start table header, insert column title and link *}
			<th nowrap="nowrap">
			<a href="javascript:;" onclick="genericAjaxGet('view{$view->id}','c=internal&a=viewSortBy&id={$view->id}&sortBy={$header}');">{$view_fields.$header->db_label|capitalize}</a>
			
			{* add arrow if sorting by this column, finish table header tag *}
			{if $header==$view->renderSortBy}
				{if $view->renderSortAsc}
					<span class="cerb-sprite sprite-sort_ascending"></span>
				{else}
					<span class="cerb-sprite sprite-sort_descending"></span>
				{/if}
			{/if}
			</th>
		{/foreach}
	</tr>
	</thead>

	{* Column Data *}
	{foreach from=$data item=result key=idx name=results}

	{if $smarty.foreach.results.iteration % 2}
		{assign var=tableRowClass value="even"}
	{else}
		{assign var=tableRowClass value="odd"}
	{/if}
	<tbody style="cursor:pointer;">
		<tr class="{$tableRowClass}">
			<td colspan="{$smarty.foreach.headers.total}">
				<input type="checkbox" name="row_id[]" value="{$result.m_id}" style="display:none;">
				
				{if !$result.m_is_queued}
					{if $result.m_type=="mail.compose"}
						<a href="javascript:;" onclick="genericAjaxPopup('peek','c=internal&a=showPeekPopup&context={CerberusContexts::CONTEXT_TICKET}&context_id=0&view_id={$view->id}&draft_id={$result.m_id}',null,false,'650');" class="subject">{if empty($result.m_subject)}(no subject){else}{$result.m_subject}{/if}</a>
					{elseif $result.m_type=="ticket.reply"}
						<a href="{devblocks_url}c=profiles&type=ticket&id={$result.m_ticket_id}{/devblocks_url}#draft{$result.m_id}" class="subject">{if empty($result.m_subject)}(no subject){else}{$result.m_subject}{/if}</a>
					{elseif $result.m_type=="ticket.forward"}
						<a href="{devblocks_url}c=profiles&type=ticket&id={$result.m_ticket_id}{/devblocks_url}#draft{$result.m_id}" class="subject">{if empty($result.m_subject)}(no subject){else}{$result.m_subject}{/if}</a>
					{/if}
				{else}
					<b class="subject">{if empty($result.m_subject)}(no subject){else}{$result.m_subject}{/if}</b>
				{/if}
				{if $active_worker->is_superuser||$result.m_worker_id==$active_worker->id}<button type="button" class="peek" style="visibility:hidden;padding:1px;margin:0px 5px;" onclick="genericAjaxPopup('peek','c=mail&a=handleSectionAction&section=drafts&action=showDraftsPeek&view_id={$view->id}&id={$result.m_id}', null, false, '500');"><span class="cerb-sprite2 sprite-document-search-result" style="margin-left:2px" title="{'views.peek'|devblocks_translate}"></span></button>{/if}
			</td>
		</tr>
		<tr class="{$tableRowClass}">
		{foreach from=$view->view_columns item=column name=columns}
			{if substr($column,0,3)=="cf_"}
				{include file="devblocks:cerberusweb.core::internal/custom_fields/view/cell_renderer.tpl"}
			{elseif $column=="m_updated" || $column=="m_queue_delivery_date"}
			<td>
				<abbr title="{$result.$column|devblocks_date}">{$result.$column|devblocks_prettytime}</abbr>
			</td>
			{elseif $column=="m_worker_id"}
			<td>
				{if is_null($workers)}
					{$workers = DAO_Worker::getAll()}
				{/if}
				{if isset($workers.{$result.$column})}
					{$worker = $workers.{$result.$column}}
					{$worker->getName()}
				{/if}
			</td>
			{elseif $column=="m_is_queued"}
			<td>
				{if $result.$column}
					{'common.yes'|devblocks_translate|capitalize}
				{else}
					{'common.no'|devblocks_translate|capitalize}
				{/if}
			</td>
			{elseif $column=="m_type"}
			<td>
				{$label_key = $result.$column}
				{if isset($label_map_type.$label_key)}
					{$label_map_type.$label_key}
				{else}
					{$label_key}
				{/if}
			</td>
			{else}
			<td>{$result.$column}</td>
			{/if}
		{/foreach}
		</tr>
	</tbody>
	{/foreach}
</table>

<div style="padding-top:5px;">
	<div style="float:right;">
		{math assign=fromRow equation="(x*y)+1" x=$view->renderPage y=$view->renderLimit}
		{math assign=toRow equation="(x-1)+y" x=$fromRow y=$view->renderLimit}
		{math assign=nextPage equation="x+1" x=$view->renderPage}
		{math assign=prevPage equation="x-1" x=$view->renderPage}
		{math assign=lastPage equation="ceil(x/y)-1" x=$total y=$view->renderLimit}
		
		{* Sanity checks *}
		{if $toRow > $total}{assign var=toRow value=$total}{/if}
		{if $fromRow > $toRow}{assign var=fromRow value=$toRow}{/if}
		
		{if $view->renderPage > 0}
			<a href="javascript:;" onclick="genericAjaxGet('view{$view->id}','c=internal&a=viewPage&id={$view->id}&page=0');">&lt;&lt;</a>
			<a href="javascript:;" onclick="genericAjaxGet('view{$view->id}','c=internal&a=viewPage&id={$view->id}&page={$prevPage}');">&lt;{'common.previous_short'|devblocks_translate|capitalize}</a>
		{/if}
		({'views.showing_from_to'|devblocks_translate:$fromRow:$toRow:$total})
		{if $toRow < $total}
			<a href="javascript:;" onclick="genericAjaxGet('view{$view->id}','c=internal&a=viewPage&id={$view->id}&page={$nextPage}');">{'common.next'|devblocks_translate|capitalize}&gt;</a>
			<a href="javascript:;" onclick="genericAjaxGet('view{$view->id}','c=internal&a=viewPage&id={$view->id}&page={$lastPage}');">&gt;&gt;</a>
		{/if}
	</div>
	
	{if $total}
	<div style="float:left;" id="{$view->id}_actions">
		<button type="button" class="action-always-show action-bulkupdate" onclick="genericAjaxPopup('peek','c=mail&a=handleSectionAction&section=drafts&action=showDraftsBulkPanel&view_id={$view->id}&ids=' + Devblocks.getFormEnabledCheckboxValues('viewForm{$view->id}','row_id[]'),null,false,'500');"><span class="cerb-sprite2 sprite-folder-gear"></span> bulk update</button>
	</div>
	{/if}
</div>

<div style="clear:both;"></div>

</form>

{include file="devblocks:cerberusweb.core::internal/views/view_common_jquery_ui.tpl"}

<script type="text/javascript">
$frm = $('#viewForm{$view->id}');

{if $pref_keyboard_shortcuts}
$frm.bind('keyboard_shortcut',function(event) {
	//console.log("{$view->id} received " + (indirect ? 'indirect' : 'direct') + " keyboard event for: " + event.keypress_event.which);
	
	$view_actions = $('#{$view->id}_actions');
	
	hotkey_activated = true;

	switch(event.keypress_event.which) {
		case 98: // (b) bulk update
			$btn = $view_actions.find('button.action-bulkupdate');
		
			if(event.indirect) {
				$btn.select().focus();
				
			} else {
				$btn.click();
			}
			break;
		
		default:
			hotkey_activated = false;
			break;
	}

	if(hotkey_activated)
		event.preventDefault();
});
{/if}
</script>