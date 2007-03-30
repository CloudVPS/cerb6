{include file="header.tpl.php"}
<table cellspacing="0" cellpadding="2" border="0" width="100%">
	<tr>
		<td align="left" valign="bottom"><img src="{devblocks_url}images/logo.jpg{/devblocks_url}"></td>
		<td align="right" valign="bottom" style="line-height:150%;">
		{if empty($visit)}
		{$translate->_('header.not_signed_in')} [<a href="{devblocks_url}c=login{/devblocks_url}">{$translate->_('header.signon')|lower}</a>]
		{else}
		{$translate->_('header.signed_in')} 
		<a href="#" title="{$translate->_('header.my_flagged_tickets')|capitalize}"><img src="{devblocks_url}images/flag_red.gif{/devblocks_url}" align="bottom" title="{$translate->_('header.my_flagged_tickets')|capitalize}" border="0"></a>
		<a href="#" title="{$translate->_('header.my_flagged_tickets')|capitalize}">5</a> 
		<a href="#" title="{$translate->_('header.my_suggested_tickets')|capitalize}"><img src="{devblocks_url}images/hand_paper.gif{/devblocks_url}" align="bottom" title="{$translate->_('header.my_suggested_tickets')|capitalize}" border="0"></a>
		<a href="#" title="{$translate->_('header.my_suggested_tickets')|capitalize}">9</a>
		[ <a href="javascript:;" onclick="ajax.showHistoryPanel(this);">{$translate->_('header.history')|lower}</a> ]
		[ <a href="{devblocks_url}c=preferences{/devblocks_url}">{$translate->_('header.preferences')|lower}</a> ]
		[ <a href="{devblocks_url}c=login&a=signout{/devblocks_url}">{$translate->_('header.signoff')|lower}</a> ]<br>
		<b>Quick Find:</b> <select name="">
			<option value="">Ticket ID/Mask</option>
			<option value="">Requester</option>
			<option value="">Subject</option>
			<option value="">Content</option>
		</select><input type="text" size="24"><input type="button" value="Search">
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
