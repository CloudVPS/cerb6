<form action="{devblocks_url}{/devblocks_url}" method="post" id="frmTimeEntry">
<input type="hidden" name="c" value="timetracking">
<input type="hidden" name="a" value="saveEntry">
{if !empty($model) && !empty($model->id)}<input type="hidden" name="id" value="{$model->id}">{/if}
<input type="hidden" name="do_delete" value="0">

<h1>{$translate->_('timetracking.ui.timetracking')}</h1>

<div style="height:350px;overflow:auto;margin:2px;padding:3px;">

<table cellpadding="2" cellspacing="0" width="100%">
	<tr>
		<td width="1%" nowrap="nowrap"><b>{$translate->_('timetracking.ui.entry_panel.activity')}</b></td>
		<td width="99%" nowrap="nowrap"><b>{$translate->_('timetracking.ui.entry_panel.time_spent')}</b></td>
	</tr>
	<tr>
		<td nowrap="nowrap">
			<select name="activity_id">
				{if !empty($nonbillable_activities)}
				<optgroup label="{$translate->_('timetracking.ui.non_billable')}">
					{foreach from=$nonbillable_activities item=activity}
					<option value="{$activity->id}" {if $model->activity_id==$activity->id}selected{/if}>{$activity->name|escape}</option>
					{/foreach}
				</optgroup>
				{/if}
				{if !empty($billable_activities)}
				<optgroup label="{$translate->_('timetracking.ui.billable')}">
					{foreach from=$billable_activities item=activity}
					<option value="{$activity->id}" {if $model->activity_id==$activity->id}selected{/if}>{$activity->name|escape}</option>
					{/foreach}
				</optgroup>
				{/if}
			</select>
		</td>
		<td><input type="text" name="time_actual_mins" size="5" value="{$model->time_actual_mins}"> {$translate->_('timetracking.ui.entry_panel.mins')}</td>
	</tr>
</table>
<br>

<b>{$translate->_('timetracking.ui.entry_panel.note')}</b> {$translate->_('timetracking.ui.entry_panel.note_hint')}<br>
<input type="text" name="notes" size="45" maxlength="255" style="width:98%;" value="{$model->notes|escape}"><br>
<br>

<b>{$translate->_('timetracking.ui.entry_panel.debit_time_client')}</b> {$translate->_('timetracking.ui.entry_panel.debit_time_client_hint')}<br>
<div id="contactautocomplete" style="width:98%;" class="yui-ac">
	<input type="text" name="org" id="contactinput" value="{$org->name|escape}" class="yui-ac-input">
	<div id="contactcontainer" class="yui-ac-container"></div>
	<br>
</div>
<br>

<table cellpadding="0" cellspacing="2" border="0" width="98%">
	<!-- Custom Fields -->
	<tr>
		<td colspan="2" align="center">&nbsp;</td>
	</tr>
	{foreach from=$time_fields item=f key=f_id}
		<tr>
			<td valign="top" width="0%" nowrap="nowrap" align="right">
				<input type="hidden" name="field_ids[]" value="{$f_id}">
				<span style="font-size:90%;">{$f->name}:</span>
			</td>
			<td valign="top" width="100%">
				{if $f->type=='S'}
					<input type="text" name="field_{$f_id}" size="45" maxlength="255" value="{$time_field_values.$f_id}"><br>
				{elseif $f->type=='N'}
					<input type="text" name="field_{$f_id}" size="45" maxlength="255" value="{$time_field_values.$f_id}"><br>
				{elseif $f->type=='T'}
					<textarea name="field_{$f_id}" rows="4" cols="50" style="width:98%;">{$time_field_values.$f_id}</textarea><br>
				{elseif $f->type=='C'}
					<input type="checkbox" name="field_{$f_id}" value="1" {if $time_field_values.$f_id}checked{/if}><br>
				{elseif $f->type=='D'}
					<select name="field_{$f_id}">{* [TODO] Fix selected *}
						<option value=""></option>
						{foreach from=$f->options item=opt}
						<option value="{$opt|escape}" {if $opt==$time_field_values.$f_id}selected{/if}>{$opt}</option>
						{/foreach}
					</select><br>
				{elseif $f->type=='E'}
					<input type="text" name="field_{$f_id}" size="30" maxlength="255" value="{if !empty($time_field_values.$f_id)}{$time_field_values.$f_id|devblocks_date}{/if}"><button type="button" onclick="ajax.getDateChooser('dateCustom{$f_id}',this.form.field_{$f_id});">&nbsp;<img src="{devblocks_url}c=resource&p=cerberusweb.core&f=images/calendar.gif{/devblocks_url}" align="top">&nbsp;</button>
					<div id="dateCustom{$f_id}" style="display:none;position:absolute;z-index:1;"></div>
				{/if}	
			</td>
		</tr>
	{/foreach}
</table>

{if !empty($source)}
<b>{$translate->_('timetracking.ui.entry_panel.reference')}</b><br>
<input type="hidden" name="source_extension_id" value="{$model->source_extension_id}">
<input type="hidden" name="source_id" value="{$model->source_id}">
<a href="{$source->getLink($model->source_id)}" target="_blank">{$source->getLinkText($model->source_id)}</a><br>
<br>
{/if}

</div>

{if empty($model->id)}
<button type="button" onclick="genericAjaxPost('frmTimeEntry','','c=timetracking&a=saveEntry',{literal}function(o){timeTrackingTimer.finish();}{/literal});"><img src="{devblocks_url}c=resource&p=cerberusweb.core&f=images/check.gif{/devblocks_url}" align="top"> {$translate->_('timetracking.ui.entry_panel.save_finish')}</button>
<button type="button" onclick="timeTrackingTimer.play();genericPanel.hide();"><img src="{devblocks_url}c=resource&p=cerberusweb.timetracking&f=images/16x16/media_play_green.png{/devblocks_url}" align="top"> {$translate->_('timetracking.ui.entry_panel.resume')}</button>
<button type="button" onclick="timeTrackingTimer.finish();"><img src="{devblocks_url}c=resource&p=cerberusweb.timetracking&f=images/16x16/media_stop_red.png{/devblocks_url}" align="top"> {$translate->_('common.cancel')|capitalize}</button>
{else}
<button type="button" onclick="genericAjaxPost('frmTimeEntry','','c=timetracking&a=saveEntry');genericPanel.hide();"><img src="{devblocks_url}c=resource&p=cerberusweb.core&f=images/check.gif{/devblocks_url}" align="top"> {$translate->_('common.save_changes')|capitalize}</button>
{if $active_worker->is_superuser || $active_worker->id == $model->worker_id}<button type="button" onclick="if(confirm('Permanently delete this time tracking entry?')){literal}{{/literal}this.form.do_delete.value='1';genericAjaxPost('frmTimeEntry','','c=timetracking&a=saveEntry');genericPanel.hide();{literal}}{/literal}"><img src="{devblocks_url}c=resource&p=cerberusweb.core&f=images/document_delete.gif{/devblocks_url}" align="top"> {$translate->_('common.delete')|capitalize}</button>{/if}
<button type="button" onclick="genericPanel.hide();"><img src="{devblocks_url}c=resource&p=cerberusweb.core&f=images/delete.gif{/devblocks_url}" align="top"> {$translate->_('common.cancel')|capitalize}</button>
{/if}
</form>