<?php
if(!class_exists('C4_AbstractViewModel')) {
class C4_AbstractViewModel {};
}

$db = DevblocksPlatform::getDatabaseService();
$logger = DevblocksPlatform::getConsoleLog();
$tables = $db->metaTables();

$rs = $db->Execute("SELECT id, extension_id, params_json FROM workspace_widget");

while($row = mysql_fetch_assoc($rs)) {
	$changed = false;
	
	$widget_id = $row['id'];
	$extension_id = $row['extension_id'];
	$params_json = $row['params_json'];
	
	if(false == ($json = json_decode($params_json, true)))
		continue;
	
	switch($extension_id) {
		case 'core.workspace.widget.counter':
		case 'core.workspace.widget.gauge':
		case 'core.workspace.widget.subtotals':
		case 'core.workspace.widget.worklist':
			$pass = true;

			switch($extension_id) {
				case 'core.workspace.widget.counter':
				case 'core.workspace.widget.gauge':
					if(!isset($json['datasource'])
						|| $json['datasource'] != 'core.workspace.widget.datasource.worklist')
							$pass = false;
					break;
			}
			
			if(!$pass)
				break;
			
			if(!isset($json['view_model']))
				break;
			
			if(!isset($json['view_context']))
				break;
			
			$view_context = $json['view_context'];
			
			if(false == ($old_model = unserialize(base64_decode($json['view_model']))))
				break;
			
			$json['worklist_model'] = array(
				'context' => $view_context,
				'columns' => $old_model->view_columns,
				'params' => json_decode(json_encode($old_model->paramsEditable), true),
				'limit' => $old_model->renderLimit,
				'sort_by' => $old_model->renderSortBy,
				'sort_asc' => !empty($old_model->renderSortAsc),
				'subtotals' => $old_model->renderSubtotals,
			);
		
			switch($extension_id) {
				case 'core.workspace.widget.subtotals':
				case 'core.workspace.widget.worklist':
					unset($json['datasource']);
					break;
			}
			
			unset($json['view_context']);
			unset($json['view_model']);
			unset($json['view_id']);
			
			$changed = true;
			break;
			
		case 'core.workspace.widget.chart':
		case 'core.workspace.widget.scatterplot':
			
			if(!isset($json['series']) || !is_array($json['series']))
				break;
			
			foreach($json['series'] as $idx => $series) {
				if(!isset($series['datasource']) || $series['datasource'] != 'core.workspace.widget.datasource.worklist')
					continue;
				
				if(!isset($series['view_model']))
					continue;
				
				if(!isset($series['view_context']))
					continue;
				
				$view_context = $series['view_context'];
				
				if(false == ($old_model = unserialize(base64_decode($series['view_model']))))
					break;
				
				$series['worklist_model'] = array(
					'context' => $view_context,
					'columns' => $old_model->view_columns,
					'params' => json_decode(json_encode($old_model->paramsEditable), true),
					'limit' => $old_model->renderLimit,
					'sort_by' => $old_model->renderSortBy,
					'sort_asc' => !empty($old_model->renderSortAsc),
					'subtotals' => $old_model->renderSubtotals,
				);
				
				unset($series['view_context']);
				unset($series['view_model']);
				unset($series['view_id']);
				
				$json['series'][$idx] = $series;
				
				$changed = true;
			}
			
			break;
	}
	
	if($changed) {
		$sql = sprintf("UPDATE workspace_widget SET params_json=%s WHERE id=%d",
			$db->qstr(json_encode($json)),
			$widget_id
		);
		$db->Execute($sql);
	}
}

return TRUE;