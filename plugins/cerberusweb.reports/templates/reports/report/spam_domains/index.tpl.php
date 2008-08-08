<div id="headerSubMenu">
	<div style="padding-bottom:5px;">
	</div>
</div>

<h2>Top 100 Spam/Nonspam Domains</h2>
<br>

<table cellpadding="2" cellspacing="0" border="0">
	<tr>
		<td width="50%" align="center" valign="top">
			<h3>Top 100 Spam Domains</h3>
			<table cellpadding="5" cellspacing="0">
				<tr>
					<td><b>Domain</b></td>
					<td align="center"><b>#spam</b></td>
					<td align="center"><b>#nonspam</b></td>
					<td align="center"><b>%</b></td>
				</tr>
				{foreach from=$top_spam_domains key=domain item=counts}
				<tr>
					<td>{if strlen($domain) > 45}<abbr title="{$domain|escape}">{$domain|truncate:45|escape}</abbr>{else}{$domain|escape}{/if}</td>
					<td align="center" style="color:rgb(200,0,0);font-weight:bold;">{$counts.0}</td>
					<td align="center" style="color:rgb(0,200,0);font-weight:bold;">{$counts.1}</td>
					<td align="center">{if $counts.0 + $counts.1 > 0}{math equation="(s/(s+n))*100" s=$counts.0 n=$counts.1 format="%0.1f"}%{/if}</td>
				</tr>
				{/foreach}
			</table>
		</td>
		<td width="50%" align="center" style="padding-left:30px;" valign="top">
			<h3>Top 100 Nonspam Domains</h3>
			<table cellpadding="5" cellspacing="0">
				<tr>
					<td><b>Domain</b></td>
					<td align="center"><b>#nonspam</b></td>
					<td align="center"><b>#spam</b></td>
					<td align="center"><b>%</b></td>
				</tr>
				{foreach from=$top_nonspam_domains key=domain item=counts}
				<tr>
					<td>{if strlen($domain) > 45}<abbr title="{$domain|escape}">{$domain|truncate:45|escape}</abbr>{else}{$domain|escape}{/if}</td>
					<td align="center" style="color:rgb(0,200,0);font-weight:bold;">{$counts.1}</td>
					<td align="center" style="color:rgb(200,0,0);font-weight:bold;">{$counts.0}</td>
					<td align="center">{if $counts.0 + $counts.1 > 0}{math equation="(n/(n+s))*100" s=$counts.0 n=$counts.1 format="%0.1f"}%{/if}</td>
				</tr>
				{/foreach}
			</table>
		</td>
	</tr>
</table>

