<form action="{devblocks_url}{/devblocks_url}" method="POST" id="frmContextLink" name="frmContextLink" onsubmit="bufferContextLink();return false;">
<input type="hidden" name="c" value="internal">
<input type="hidden" name="a" value="saveContextLinkAddPeek">
<input type="hidden" name="context" value="{$context}">
<input type="hidden" name="context_id" value="{$context_id}">
<input type="hidden" name="return_uri" value="{$return_uri}">

<table cellpadding="0" cellspacing="0" border="0" width="100%">
	<tr>
		<td width="1%" nowrap="nowrap">
			<select name="_context" onchange="return;ajax.contextAutocomplete('#frmContextLink input[name=_context_id]', $(this).val(), { } );">
				<option value="">-- context --</option>
				{foreach from=$context_extensions item=icontext}
				<option value="{$icontext->id}">{$icontext->name}</option>
				{/foreach}
			</select>
		</td>
		<td width="99%"><input type="text" name="_context_id" style="width:100%;"></td>
		<td width="1%" nowrap="nowrap"><button type="button" onclick="bufferContextLink();"><span class="cerb-sprite sprite-add"></span></button></td>
	</tr>
</table>
<br>

<div id="divContextLinkBuffer">
	{foreach from=$context_links item=link}
	{if isset($context_extensions.{$link->context})}
		<div>
			 <a href="javascript:;" onclick="genericAjaxGet('','c=internal&a=contextDeleteLink&context={$context|escape:'url'}&context_id={$context_id|escape:'url'}&dst_context={$link->context|escape:'url'}&dst_context_id={$link->context_id|escape:'url'}');$(this).parent().remove();">X</a> [{$context_extensions.{$link->context}->name}] {$link->context_id}
			<input type="hidden" name="dst_context[]" value="{$link->context|escape}">
			<input type="hidden" name="dst_context_id[]" value="{$link->context_id}">
		</div>
	{/if}
	{/foreach}
</div>
<br>

<button type="button" onclick="this.form.submit();"><span class="cerb-sprite sprite-check"></span> {$translate->_('common.save_changes')}</button>

{*
{if ($active_worker->hasPriv('core.tasks.actions.create') && (empty($task) || $active_worker->id==$task->worker_id))
	|| ($active_worker->hasPriv('core.tasks.actions.update_nobody') && empty($task->worker_id)) 
	|| $active_worker->hasPriv('core.tasks.actions.update_all')}
	<button type="button" onclick="genericPanel.dialog('close');genericAjaxPost('formTaskPeek', 'view{$view_id}');"><span class="cerb-sprite sprite-check"></span> {$translate->_('common.save_changes')}</button>
	{if !empty($task)}<button type="button" onclick="if(confirm('Are you sure you want to permanently delete this task?')) { $('#formTaskPeek input[name=do_delete]').val('1'); genericAjaxPost('formTaskPeek', 'view{$view_id}'); genericPanel.dialog('close'); } "><span class="cerb-sprite sprite-delete2"></span> {$translate->_('common.delete')|capitalize}</button>{/if}
{else}
	<div class="error">{'error.core.no_acl.edit'|devblocks_translate}</div>
{/if}
*}
<br>
</form>

<script language="JavaScript1.2" type="text/javascript">
	function bufferContextLink() {
		var $context = $('#frmContextLink select[name=_context]');
		var $label = $('#frmContextLink select[name=_context] :selected').text();
		var $id = $('#frmContextLink input[name=_context_id]');
		if(0==$id.val().length || 0==$context.val().length)
			return;
		var $html = $('<div>[' + $label + '] ' + $id.val() + '</div>');
		$html.prepend(' <a href="javascript:;" onclick="$(this).parent().remove();">X</a> ');
		$html.append('<input type="hidden" name="dst_context[]" value="' + $context.val() + '">');
		$html.append('<input type="hidden" name="dst_context_id[]" value="' + $id.val() + '">');
		$('#divContextLinkBuffer').append($html);
		$id.val(''); // clear
		$id.focus();
	}
	
	genericPanel.one('dialogopen',function(event,ui) {
		genericPanel.dialog('option','title','Manage Links');
		$('#frmContextLink :input:text:first').focus().select();
	} );
</script>