<input type="hidden" name="c" value="internal">
<input type="hidden" name="a" value="viewSaveCustomize">
<input type="hidden" name="id" value="{$view->id}">
<div class="block" style="margin:5px;">
<h1>{'common.customize'|devblocks_translate|capitalize}</h1>

{* Custom Views *}
{if substr($view->id,0,5)=="cust_"}
	{assign var=is_custom value=true}
{else}
	{assign var=is_custom value=false}
{/if}

{* Trigger Views *}
{if substr($view->id,0,9)=="_trigger_"}
	{assign var=is_trigger value=true}
{else}
	{assign var=is_trigger value=false}
{/if}

{if $is_custom || $is_trigger}
<b>List Title:</b><br>
<input type="text" name="title" value="{$view->name}" size="64" autocomplete="off"><br>
<br>
{/if}

<b>{'dashboard.columns'|devblocks_translate|capitalize}:</b> 
 &nbsp; 
<a href="javascript:;" onclick="Devblocks.resetSelectElements('customize{$view->id}','columns[]');">{'common.clear'|devblocks_translate|lower}</a>
<br>
{$columnsAvailable = $view->getColumnsAvailable()}
{section start=0 step=1 loop=15 name=columns}
{assign var=index value=$smarty.section.columns.index}
<div class="column"> 
<span class="ui-icon ui-icon-arrowthick-2-n-s" style="display:inline-block;vertical-align:middle;cursor:move;"></span>
<select name="columns[]">
	<option value=""></option>
	
	{foreach from=$columnsAvailable item=colAvail}
		{if !empty($colAvail->db_label) && !empty($colAvail->token)}
			<option value="{$colAvail->token}" {if $view->view_columns.$index==$colAvail->token}selected{/if}>{$colAvail->db_label|capitalize}</option>
		{/if}
	{/foreach}
</select>
</div>
{/section}
<br>
<b>{'dashboard.num_rows'|devblocks_translate|capitalize}:</b> <input type="text" name="num_rows" size="3" maxlength="3" value="{$view->renderLimit}"><br>
<br>

{if $is_custom}
<b>Always apply these filters to this worklist:</b><br>
<div id="viewCustom{if $is_custom}Req{/if}Filters{$view->id}" style="margin:10px;">
{include file="devblocks:cerberusweb.core::internal/views/customize_view_criteria.tpl" is_custom=true}
</div>
<br>
{/if}

<button type="button" onclick="genericAjaxPost('customize{$view->id}','view{$view->id}','c=internal&a=viewSaveCustomize');"><span class="cerb-sprite2 sprite-tick-circle"></span> {'common.save_changes'|devblocks_translate|capitalize}</button>
<button type="button" onclick="toggleDiv('customize{$view->id}','none');"><span class="cerb-sprite2 sprite-cross-circle"></span> {'common.cancel'|devblocks_translate|capitalize}</button>

<br>
<br>
</div>

<script type="text/javascript">
	$('#customize{$view->id}').sortable({ items: 'DIV.column', placeholder:'ui-state-highlight' });
</script>