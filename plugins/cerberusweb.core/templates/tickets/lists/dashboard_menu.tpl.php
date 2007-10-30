<div id="tourDashboardActions"></div>

{if !empty($workspaces)}
<div id="workspacePanel">
<div class="block">
<table cellpadding="0" cellspacing="0" border="0" width="220">
	<tr>
		<td nowrap="nowrap"><h2>Workspace</h2></td>
	</tr>
	<tr>
		<td>
			<table cellpadding="0" cellspacing="1" border="0" width="100%">
				<tr>
					<td width="100%">
						<form method="POST" action="{devblocks_url}{/devblocks_url}" id="dashboardMenuForm">
						<input type="hidden" name="c" value="tickets">
						<input type="hidden" name="a" value="changeMyWorkspace">
				      	<select name="workspace" onchange="this.form.submit();">
				      		<option value="" {if empty($current_workspace)}selected{/if}>- My Tickets -</option>
			      			{foreach from=$workspaces item=workspace}
				      			<option value="{$workspace|escape}" {if $current_workspace==$workspace}selected{/if}>{$workspace}</option>
			      			{/foreach}
				      	</select>
				      	</form>
					</td>
				</tr>

				<tr>
					<td width="100%" style="padding:2px;">
						 <a href="javascript:;" onclick="genericAjaxPanel('c=tickets&a=showAddListPanel',this,false,'450px');">{$translate->_('dashboard.add_view')|lower}</a>
					</td>
				</tr>

				<tr>
					<td width="100%" style="padding:2px;">
						<img src="{devblocks_url}c=resource&p=cerberusweb.core&f=images/mail2.gif{/devblocks_url}" align="top"> <a href="{devblocks_url}c=tickets&a=compose{/devblocks_url}">Send Mail</a><br>
					</td>
				</tr>
				
			</table>
		</td>
	</tr>
</table>
</div>
<br>
{/if}

{if empty($workspaces)}
<div class="subtle" style="margin:0px;">
<table cellpadding="0" cellspacing="0" border="0" width="220">
	<tr>
		<td>
		<b>You haven't created any custom worklists.</b><br>
		<a href="javascript:;" onclick="genericAjaxPanel('c=tickets&a=showAddListPanel',this,false,'450px');">Click here</a> to create your first worklist.<br>
		</td>
	</tr>
</table>
</div>
<br>
{/if}

</div>