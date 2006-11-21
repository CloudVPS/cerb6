{include file="header.tpl.php"}

<table cellspacing="0" cellpadding="2" border="0" width="100%">
	<tr>
		<td align="left" valign="bottom"><img src="images/logo.jpg"></td>
		<td align="right" valign="bottom">
		{if empty($visit)}
		Not signed in [<a href="?c=core.module.signin&a=show">sign in</a>]
		{else}
		Signed in as <b>{$visit->login}</b> [<a href="?c=core.module.signin&a=signout">sign off</a>]
		{/if}
		</td>
	</tr>
</table>

{include file="menu.tpl.php"}

{if !empty($module)}
	{$module->render()}
{else}
	No module selected.
{/if}

{include file="footer.tpl.php"}
