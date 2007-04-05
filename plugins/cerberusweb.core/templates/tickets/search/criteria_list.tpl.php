<table cellpadding="2" cellspacing="0" width="200" border="0" class="tableGreen">
	<tr>
		<th class="tableThGreen"><img src="{devblocks_url}images/find.gif{/devblocks_url}"> Search Criteria</th>
	</tr>
	<tr style="border-bottom:1px solid rgb(200,200,200);">
		<td>
			{if $view->type=='S'}
				Search: <b>{$view->name}</b><br>
			{/if}
			<a href="{devblocks_url}c=tickets&a=resetCriteria{/devblocks_url}">reset</a> |
			<a href="javascript:;" onclick="ajax.getSaveSearch('{$divName}');">save as</a> |
			<a href="javascript:;" onclick="ajax.getLoadSearch('{$divName}');">load</a>
			{if $view->type=='S'} | <a href="javascript:;" onclick="ajax.deleteSearch('{$view->id}');">delete</a>{/if}
			<br>
			<form id="{$divName}_control"></form>
		</td>
	</tr>
	<tr>
		<td style="background-color:rgb(255,255,255);">
			<table cellpadding="2" cellspacing="0" border="0">
				<tr>
					<td colspan="2" align="left">
						<a href="javascript:;" onclick="addCriteria('{$divName}');">Add new criteria</a> 
						<a href="javascript:;" onclick="addCriteria('{$divName}');"><img src="{devblocks_url}images/data_add.gif{/devblocks_url}" align="absmiddle" border="0"></a> 
					</td>
				</tr>
				{if !empty($params)}
				{foreach from=$params item=param}
					<tr>
						<td width="100%">
						{if $param->field=='t_mask'}
							<img src="{devblocks_url}images/data_find.gif{/devblocks_url}" align="absmiddle"> 
							{$translate->_('ticket.mask')|capitalize} 
							{$param->operator} 
							<b>{$param->value}</b>
						{elseif $param->field=="t_status"}
							<img src="{devblocks_url}images/data_find.gif{/devblocks_url}" align="absmiddle"> 
							{$translate->_('ticket.status')|capitalize} 
							{$param->operator}
							{foreach from=$param->value item=p name=params}
							<b>{$p}</b>
							 {if !$smarty.foreach.params.last} or {/if}
							{/foreach}
						{elseif $param->field=="tm_id"}
							<img src="{devblocks_url}images/data_find.gif{/devblocks_url}" align="absmiddle"> 
							{$translate->_('common.team')|capitalize}
							{$param->operator}
							{foreach from=$param->value item=p name=params}
							<b>{$p}</b>
							 {if !$smarty.foreach.params.last} or {/if}
							{/foreach}
						{elseif $param->field=="t_priority"}
							<img src="{devblocks_url}images/data_find.gif{/devblocks_url}" align="absmiddle"> 
							{$translate->_('ticket.priority')|capitalize} 
							{$param->operator}
							{foreach from=$param->value item=p name=params}
							<b>{$p}</b>
							 {if !$smarty.foreach.params.last} or {/if}
							{/foreach}
						{elseif $param->field=="t_subject"}
							<img src="{devblocks_url}images/data_find.gif{/devblocks_url}" align="absmiddle"> 
							{$translate->_('ticket.subject')|capitalize} 
							{$param->operator} 
							<b>{$param->value}</b>
						{elseif $param->field=="ra_email"}
							<img src="{devblocks_url}images/data_find.gif{/devblocks_url}" align="absmiddle"> 
							{$translate->_('requester')|capitalize} 
							{$param->operator} 
							<b>{$param->value}</b>
						{elseif $param->field=="msg_content"}
							<img src="{devblocks_url}images/data_find.gif{/devblocks_url}" align="absmiddle"> 
							{$translate->_('message.content')|capitalize} 
							{$param->operator} 
							<b>{$param->value}</b>
						{else}
						{/if}
						</td>
						<td width="0%" nowrap="nowrap" valign="top"><a href="{devblocks_url}c=tickets&a=removeCriteria&field={$param->field}{/devblocks_url}"><img src="{devblocks_url}images/data_error.gif{/devblocks_url}" border="0" align="absmiddle"></a></td>
					</tr>
				{/foreach}
				{/if}
			</table>
		</td>
	</tr>
</table>