{include file="header.tpl.php"}
<table cellspacing="0" cellpadding="2" border="0" width="100%">
	<tr>
		<td align="left" valign="bottom"><img src="{devblocks_url}images/logo.jpg{/devblocks_url}"></td>
		<td align="right" valign="bottom" style="line-height:150%;">
		{if empty($visit)}
		{$translate->_('header.not_signed_in')} [<a href="{devblocks_url}c=login{/devblocks_url}">{$translate->_('header.signon')|lower}</a>]
		{else}
		{$translate->_('header.signed_in')} 
		<img src="{devblocks_url}images/hand_paper.gif{/devblocks_url}" align="bottom" border="0"> <a href="{devblocks_url}c=tickets&a=dashboards&i=my{/devblocks_url}" title="{$translate->_('header.my_flagged_tickets')|capitalize}">my tasks</a> 
		[ <a href="javascript:;" onclick="genericAjaxPanel('c=tickets&a=showHistoryPanel',this);">{$translate->_('header.history')|lower}</a> ]
		[ <a href="{devblocks_url}c=preferences{/devblocks_url}">{$translate->_('header.preferences')|lower}</a> ]
		[ <a href="{devblocks_url}c=login&a=signout{/devblocks_url}">{$translate->_('header.signoff')|lower}</a> ]<br>
		<form action="{devblocks_url}{/devblocks_url}" method="post">
		<input type="hidden" name="c" value="tickets">
		<input type="hidden" name="a" value="doQuickSearch">
		<b>Quick Find:</b> <select name="type">
			<option value="mask">Ticket Mask</option>
			<option value="req">Requester</option>
			<option value="subject">Subject</option>
			<option value="content">Content</option>
		</select><input type="text" name="query" size="24"><input type="submit" value="Search">
		</form>
		{/if}
		</td>
	</tr>
</table>

{include file="menu.tpl.php"}

{if !empty($page) && $page->isVisible()}
	{$page->render()}
{else}
	{$translate->_('header.no_page')}
{/if}

{include file="footer.tpl.php"}
