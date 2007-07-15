<div class="block">
	<!-- ************** -->

	<h2>System Settings</h2>
	<br>

	<form action="{devblocks_url}{/devblocks_url}" method="post">
	<input type="hidden" name="c" value="config">
	<input type="hidden" name="a" value="saveSettings">
	
	<b>Helpdesk Title:</b><br>
	<input type="text" name="title" value="{$settings->get('helpdesk_title')|escape:"html"}" size="64"><br>
	<br>
	
	<b>Logo URL:</b> (leave blank for default)<br>
	<input type="text" name="logo" value="{$settings->get('helpdesk_logo_url')|escape:"html"}" size="64"><br>
	<br>
	
	<b>Timezone:</b><br>
	<select name="timezone">
		<option value="">---</option>
	</select><br>
	<br>
	
	<!-- ************** -->
	
	<h2>Attachments</h2>
	<br>
	
	<b>Enabled:</b><br>
	<label><input type="checkbox" name="attachments_enabled" value="1" {if $settings->get('attachments_enabled')}checked{/if}> Allow Incoming Attachments</label><br>
	<br>
	
	<b>Max. Attachment Size:</b><br>
	<input type="text" name="attachments_max_size" value="{$settings->get('attachments_max_size')|escape:"html"}" size="5"> MB<br>
	<br>

	<!-- ************** -->

	<!-- 
	<h2>Anti-Spam</h2>
	<br>

	<b>Learning:</b><br>
	<label><input type="checkbox" value="whitelist"> Whitelist a sender after <input type='text' size='3' value='5'> non-spam messages.</label><br>
	<br>
	-->	
	<h2>IP Security</h2>
	<br>
	<b>Allow administration tools from these IPs:</b> (One IP address per line)
	<br>
	<textarea name="authorized_ips" rows="5" cols="24" style="width: 400;">{$settings->get('authorized_ips')|escape:"html"}</textarea>	
	<br>
	(Partial IP matches OK. For example: 192.168.1.)
	<br><br>
	<button type="submit"><img src="{devblocks_url}c=resource&p=cerberusweb.core&f=images/check.gif{/devblocks_url}" align="top"> {$translate->_('common.save_changes')|capitalize}</button>
	</form>
</div>