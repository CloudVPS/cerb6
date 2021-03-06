{'portal.sc.cfg.feeds_info'|devblocks_translate}<br>
<br>

<table id="setupScAnnouncements" cellpadding="0" cellspacing="0" border="0" class="container">
	<tr>
		<td></td>
		<td>
			<b>{'portal.sc.cfg.feed_display_title'|devblocks_translate}</b>
		</td>
		<td>
			<b>{'portal.sc.cfg.feed_url'|devblocks_translate}</b>
		</td>
	</tr>

	<tbody class="template" style="display:none;margin:5px;">
	<tr>
		<td><span class="ui-icon ui-icon-arrowthick-2-n-s" style="display:inline-block;vertical-align:middle;cursor:move;" title="Click and drag to rearrange"></span></td>
		<td>
			<input type="text" name="news_rss_title[]" value="{$news_rss_title}" size="45">
		</td>
		<td>
			<input type="text" name="news_rss_url[]" value="{$news_rss_url}" size="45">
		</td>
		<td>
			<button type="button" class="remove" onclick="$(this).closest('tbody').remove();"><span class="cerb-sprite2 sprite-minus-circle"></span></button>			
		</td>
	</tr>
	</tbody>
	
	{foreach from=$news_rss item=news_rss_url key=news_rss_title}
	<tbody class="drag" style="cursor:move;margin:5px;">
	<tr>
		<td><span class="ui-icon ui-icon-arrowthick-2-n-s" style="display:inline-block;vertical-align:middle;cursor:move;" title="Click and drag to rearrange"></span></td>
		<td>
			<input type="text" name="news_rss_title[]" value="{$news_rss_title}" size="45">
		</td>
		<td>
			<input type="text" name="news_rss_url[]" value="{$news_rss_url}" size="45">
		</td>
		<td>
			<button type="button" class="remove" onclick="$(this).closest('tbody').remove();"><span class="cerb-sprite2 sprite-minus-circle"></span></button>			
		</td>
	</tr>
	</tbody>
	{/foreach}	
</table>

<button type="button" class="add"><span class="cerb-sprite2 sprite-plus-circle"></span></button>

<script type="text/javascript">
$container = $('#setupScAnnouncements.container'); 
$container
	.sortable({ items: 'TBODY.drag', placeholder:'ui-state-highlight' })
	;
$container
	.next('BUTTON.add')
	.click(function() {
		$container = $('#setupScAnnouncements.container');
		$clone = $container
			.find('TBODY.template')
			.clone()
			.addClass('drag')
			.removeClass('template')
			.show();
		$container.append($clone);
	});
</script>
