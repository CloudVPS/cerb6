<b>Operator:</b><br>
<blockquote style="margin:5px;">
	<select name="oper">
		<option value="in">in list</option>
		<option value="not in">not in list</option>
	</select>
</blockquote>

<b>Workers:</b><br>
{foreach from=$workers item=worker key=worker_id}
<label><input name="worker_id[]" type="checkbox" value="{$worker_id}"><span style="font-weight:bold;color:rgb(0,120,0);">{$worker->getName()}</span></label><br>
{/foreach}

<br>
<button type="submit"><img src="{devblocks_url}c=resource&p=cerberusweb.core&f=images/check.gif{/devblocks_url}" align="top"> {$translate->_('common.save_changes')|capitalize}</button>