<div class="block">
<h2>Modify Job '{$job->manifest->name}'</h2>
<br>

{assign var=enabled value=$job->getParam('enabled')}
{assign var=duration value=$job->getParam('duration',5)}
{assign var=term value=$job->getParam('term','m')}

<form action="{devblocks_url}{/devblocks_url}" method="post">
<input type="hidden" name="c" value="config">
<input type="hidden" name="a" value="saveJob">
<input type="hidden" name="id" value="{$job->manifest->id}">

<label><input type="checkbox" name="enabled" value="1" {if $enabled}checked{/if}> <b>Enabled</b></label><br>
<br>

<b>Run every:</b><br>
<input type="text" name="duration" maxlength="5" size="3" value="{$duration}">
<select name="term">
	<option value="m" {if $term=='m'}selected{/if}>minute(s)
	<option value="h" {if $term=='h'}selected{/if}>hour(s)
</select><br>
<br>

{if $job}
{$job->configure($job)}<br>
{/if}

<button type="submit"><img src="{devblocks_url}c=resource&p=cerberusweb.core&f=images/check.gif{/devblocks_url}" align="top"> {$translate->_('common.save_changes')|capitalize}</button>
<button type="button" onclick="javascript:document.location='{devblocks_url}c=config&a=jobs{/devblocks_url}';"><img src="{devblocks_url}c=resource&p=cerberusweb.core&f=images/delete.gif{/devblocks_url}" align="top"> {$translate->_('common.cancel')|capitalize}</button>

</form>
</div>