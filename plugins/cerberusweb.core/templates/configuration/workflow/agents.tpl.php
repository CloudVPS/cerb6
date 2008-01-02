<table cellpadding="0" cellspacing="5" border="0" width="100%">
	<tr>
		
		<td width="0%" nowrap="nowrap" valign="top">
		
			<div class="block">
			<table cellpadding="2" cellspacing="0" border="0">
				<tr>
					<td><h2>Workers</h2></td>
				</tr>
				<tr>
					<td>
						{* [WGM]: Please respect our licensing and support the project! *}
						{if (empty($license) || empty($license.key)) && count($workers) >= 3}
						You have reached your Cerberus Helpdesk free version limit of 3 workers.<br>
						[ <a href="{devblocks_url}c=config&a=licenses{/devblocks_url}" style="color:rgb(0,160,0);">Enter License</a> ]
						[ <a href="http://www.cerberusweb.com/purchase.php" target="_blank" style="color:rgb(0,160,0);">Buy License</a> ]
						{else}
						[ <a href="javascript:;" onclick="configAjax.getWorker('0');">add new worker</a> ]
						{/if}
					</td>
				</tr>
				<tr>
					<td>
						<div style="margin:0px;padding:3px;height:150px;width:200px;overflow:auto;">
						{if !empty($workers)}
							{foreach from=$workers item=agent}
							&#187; <a href="javascript:;" onclick="configAjax.getWorker('{$agent->id}')" title="{if !empty($agent->title)}{$agent->title}{/if}">{if !empty($agent->last_name)}{$agent->last_name}{/if}{if !empty($agent->first_name) && !empty($agent->last_name)}, {/if}{if !empty($agent->first_name)}{$agent->first_name}{/if}</a><br>
							{/foreach}
						{/if}
						</div>
					</td>
				</tr>
			</table>
			</div>
			
		</td>
		
		<td width="100%" valign="top">
			<form action="{devblocks_url}{/devblocks_url}#workers" method="post" id="configWorker">
				{include file="$path/configuration/workflow/edit_worker.tpl.php" worker=null}
			</form>
		</td>
		
	</tr>
</table>

