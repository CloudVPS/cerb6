<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8">
	<title>{$page_title}</title>
	<style type="text/css">
	{literal}
		body { margin:0;padding:0; }
		body, td { color:rgb(80,80,80); }
		form { margin: 0px; padding:0px; }
		a { color: rgb(51, 102, 255); }
		h1 {
			border-bottom:1px solid rgb(180,180,180);
			margin-bottom:10px;
			font-size:140%;
			color:rgb(50,180,50);
		}
		
		#navMenu {
			border-bottom: 1px solid rgb(180, 180, 180);
			border-top: 1px solid rgb(180, 180, 180);
			text-align: left;
			width: 100%;
			padding:3px;
			background-color: rgb(235, 235, 255);
			color: color: rgb(50,50,50);
		}
		
		#navMenu A {
			color: rgb(50,50,50);
		}
		
		DIV.error {
			border:1px solid rgb(180,0,0);
			background-color:rgb(255,235,235);
			color:rgb(180,0,0);
			font-weight:bold;
			margin:10px;
			padding:5px;
		}
		
		DIV.success {
			border:1px solid rgb(0,180,0);
			background-color:rgb(235,255,235);
			color:rgb(0,180,0);
			font-weight:bold;
			margin:10px;
			padding:5px;
		}
	{/literal}
	</style>

	<link rel="stylesheet" type="text/css" href="{devblocks_url}c=resource&p=cerberusweb.core&f=scripts/yui/fonts/fonts-min.css{/devblocks_url}">
</head>

<body class="yui-skin-sam">
<table cellpadding="5" cellspacing="0" border="0" width="100%">
<tr>
	<td width="100%">
		{if empty($logo_url)}
			<a href="{devblocks_url}c=home{/devblocks_url}"><img src="{devblocks_url}c=resource&p=usermeet.core&f=images/cerb_logo.jpg{/devblocks_url}" alt="Logo" border="0"></a><br>
		{else}
			<a href="{devblocks_url}c=home{/devblocks_url}"><img src="{$logo_url}" alt="Logo" border="0"></a><br>
		{/if}
	</td>
	<td align="right" valign="bottom" nowrap="nowrap" width="0%">
		{if $allow_logins}
		{if !empty($active_user)}
			<form action="{devblocks_url}{/devblocks_url}" method="post" name="loginForm">
			<input type="hidden" name="a" value="doLogout">
			Logged in as <b>{$active_user->email}</b> [ <a href="javascript:;" onclick="document.loginForm.submit();" style="color:rgb(0,0,0);">logout</a> ]
			</form> 
		{else}
			<form action="{devblocks_url}{/devblocks_url}" method="post" name="loginForm">
			<input type="hidden" name="a" value="doLogin">
			<table cellspacing="0" cellpadding="0" border="0" width="100%">
			<tr>
				<td align="left" valign="bottom" nowrap="nowrap"><i>e-mail address:</i></td>
				<td align="left" valign="bottom" nowrap="nowrap"><i>password:</i></td>
			</tr>
			<tr>
				<td valign="top" nowrap="nowrap"><input type="text" name="email" size="16"></td>
				<td valign="top" nowrap="nowrap"><input type="password" name="pass" size="8"><button type="submit"><img src="{devblocks_url}c=resource&p=usermeet.sc&f=images/lock.gif{/devblocks_url}" align="top"> Log in</button> 
				<a href="{devblocks_url}c=register{/devblocks_url}" style="color:rgb(0,0,0);">register</a> | <a href="{devblocks_url}c=register&a=forgot{/devblocks_url}" style="color:rgb(0,0,0);">forgot?</a> 
				</td>
			</tr>
			</table>
			</form> 
		{/if}
		{/if}
	</td>
</tr>
</table>
<table id="navMenu" style="" border="0" cellpadding="0" cellspacing="0">
	<tbody>
		<tr>
			<td>
				 &nbsp;
				 {foreach from=$menu item=item name=menu}
				 	{if !empty($item.icon)}<img src="{devblocks_url}c=resource&p=usermeet.sc&f={$item.icon}{/devblocks_url}" align="top">{/if} <a href="{devblocks_url}c={$item.uri}{/devblocks_url}">{$item.menu_title}</a>
				 	{if !$smarty.foreach.menu.last} | {/if}
				 {/foreach}
			</td>
			<td align="right" nowrap="nowrap" valign="top">
				<form action="{devblocks_url}{/devblocks_url}" method="post">
				<input type="hidden" name="a" value="doSearch">
				<b style="color:rgb(20,120,20);">search help:</b> <input name="query" value="" size="16" type="text"><button type="submit"><img src="{devblocks_url}c=resource&p=usermeet.sc&f=images/data_view.gif{/devblocks_url}" align="top"></button>
				</form>
			</td>
		</tr>
	</tbody>
</table>

<table style="text-align: left; width: 100%;" border="0" cellpadding="0" cellspacing="0">
	<tbody>
		<tr>
			<td style="padding: 5px; vertical-align: top;">
			{$module->writeResponse($module_response)}
			</td>
		</tr>
	</tbody>
</table>

<table style="padding: 10px;padding-top:15px; width: 100%;" border="0" cellpadding="0" cellspacing="0">
	<tbody>
		<tr>
			<td>{$footer_html}</td>
			<td style="text-align:right;vertical-align:top;color:rgb(150,150,150);font-size:11px;">powered by <a href="http://www.cerberusweb.com/" style="color:rgb(50,120,50);font-weight:bold;">Cerberus Helpdesk 4.0</a></td>
		</tr>
	</tbody>
</table>
<br>
<br>

</body>
</html>
