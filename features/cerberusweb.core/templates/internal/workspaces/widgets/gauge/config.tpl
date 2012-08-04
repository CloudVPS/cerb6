<div id="widget{$widget->id}ConfigTabs">
	<ul>
		<li><a href="#widget{$widget->id}ConfigTabThresholds">Thresholds</a></li>
		<li><a href="#widget{$widget->id}ConfigTabDatasource">Data Source</a></li>
	</ul>
	
    <div id="widget{$widget->id}ConfigTabThresholds">
    	<table cellpadding="0" cellspacing="0" border="0" width="100%">
    		<tr>
    			<td width="50%"><b>Label</b></td>
    			<td width="30%"><b>Max. Value</b></td>
    			<td width="20%"><b>Color</b></td>
    		</tr>
    		{section name=thresholds loop=4}
    		<tr>
    			<td style="padding-right:10px;" valign="top">
    				<input type="text" name="params[threshold_labels][]" value="{$widget->params.threshold_labels.{$smarty.section.thresholds.index}}" style="width:100%;">
    			</td>
    			<td style="padding-right:10px;" valign="top">
    				<input type="text" name="params[threshold_values][]" value="{$widget->params.threshold_values.{$smarty.section.thresholds.index}}" style="width:100%;">
    			</td>
    			<td valign="top">
    				<input type="hidden" name="params[threshold_colors][]" value="{$widget->params.threshold_colors.{$smarty.section.thresholds.index}}" style="width:100%;" class="color-picker">
    			</td>
    		</tr>
    		{/section}
    	</table>
	</div>
	
    <div id="widget{$widget->id}ConfigTabDatasource">
    	<label><input type="radio" name="params[datasource]" value="" {if empty($widget->params.datasource)}checked="checked"{/if}> Manual</label>
    	<label><input type="radio" name="params[datasource]" value="worklist" {if $widget->params.datasource=='worklist'}checked="checked"{/if}> Worklist</label>
    	<label><input type="radio" name="params[datasource]" value="sensor" {if $widget->params.datasource=='sensor'}checked="checked"{/if}> Sensor</label>
    	
    	<fieldset class="peek manual" style="display:{if empty($widget->params.datasource)}block{else}none{/if};">
    	<table cellspacing="0" cellpadding="0" border="0">
    		<tr>
    			<td>
					<b>Metric Value:</b>
    			</td>
    			<td>
    				<b>Type:</b>
    			</td>
    			<td>
    				<b>Prefix:</b>
    			</td>
    			<td>
    				<b>Suffix:</b>
    			</td>
    		</tr>
    		<tr>
    			<td>
					<input type="text" name="params[metric_value]" value="{$widget->params.metric_value}">
    			</td>
    			<td>
    				{$types = [number, decimal, percent]}
    				<select name="params[metric_type]">
    					{foreach from=$types item=type}
    					<option value="{$type}" {if $widget->params.metric_type==$type}selected="selected"{/if}>{$type}</option>
    					{/foreach}
    				</select>
    			</td>
    			<td>
					<input type="text" name="params[metric_prefix]" value="{$widget->params.metric_prefix}" size="10">
    			</td>
    			<td>
					<input type="text" name="params[metric_suffix]" value="{$widget->params.metric_suffix}" size="10">
    			</td>
    		</tr>
    	</table>
    	</fieldset>
    	
    	<fieldset class="peek worklist" style="display:{if $widget->params.datasource=='worklist'}block{else}none{/if};">
    		{$div_popup_worklist = uniqid()}
			
			<b>Load </b>
			
			<select name="params[view_context]" class="context">
				{foreach from=$context_mfts item=context_mft key=context_id}
				<option value="{$context_id}" {if $widget->params.view_context==$context_id}selected="selected"{/if}>{$context_mft->name}</option>
				{/foreach}
			</select>
			
			<b> data using</b> 
			
			<div id="popup{$div_popup_worklist}" class="badge badge-lightgray" style="font-weight:bold;color:rgb(80,80,80);cursor:pointer;display:inline;"><span class="name">Worklist</span> &#x25be;</div>
			
			<input type="hidden" name="params[view_model]" value="{$params.view_model}" class="model">
    	</fieldset>
    	
    	<fieldset class="peek sensor" style="display:{if $widget->params.datasource=='sensor'}block{else}none{/if};">
    		<b>Sensor:</b>
    		<select name="params[sensor_id]">
    			{foreach from=$sensors item=sensor}
    			<option value="{$sensor->id}" {if $widget->params.sensor_id==$sensor->id}selected="selected"{/if}>{$sensor->name}</option>
    			{/foreach}
    		</select>
    	</fieldset>
	</div>
</div>

<script type="text/javascript">
	$tabs = $('#widget{$widget->id}ConfigTabs').tabs();
	
	$tabs.find('input:hidden.color-picker').miniColors({
		color_favorites: ['#CF2C1D','#FEAF03','#34434E','#57970A']
	});
	
	$datasource_tab = $('#widget{$widget->id}ConfigTabDatasource');
	$radios = $datasource_tab.find('> label input:radio');
	$fieldsets = $datasource_tab.find('> fieldset');
	
	$radios.click(function(e) {
		val = $(this).val();
		
		if(val == '')
			val = 'manual';
		
		$fieldsets = $(this).closest('div').find('> fieldset');
		$fieldsets.hide();
		
		$datasource_tab.find('fieldset.' + val).show();
	});
	
	$('#popup{$div_popup_worklist}').click(function(e) {
		context = $(this).siblings('select.context').val();
		$chooser=genericAjaxPopup('chooser','c=internal&a=chooserOpenParams&context='+context+'&view_id={"widget{$widget->id}_worklist"}',null,true,'750');
		$chooser.bind('chooser_save',function(event) {
			if(null != event.view_model) {
				//$('#popup{$div_popup_worklist}').find('span.name').html(event.view_name);
				$('#popup{$div_popup_worklist}').parent().find('input:hidden.model').val(event.view_model);
			}
		});
	});
</script>