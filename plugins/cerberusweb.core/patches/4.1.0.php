<?php
/***********************************************************************
| Cerberus Helpdesk(tm) developed by WebGroup Media, LLC.
|-----------------------------------------------------------------------
| All source code & content (c) Copyright 2007, WebGroup Media LLC
|   unless specifically noted otherwise.
|
| This source code is released under the Cerberus Public License.
| The latest version of this license can be found here:
| http://www.cerberusweb.com/license.php
|
| By using this software, you acknowledge having read this license
| and agree to be bound thereby.
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/
/*
 * IMPORTANT LICENSING NOTE from your friends on the Cerberus Helpdesk Team
 * 
 * Sure, it would be so easy to just cheat and edit this file to use the 
 * software without paying for it.  But we trust you anyway.  In fact, we're 
 * writing this software for you! 
 * 
 * Quality software backed by a dedicated team takes money to develop.  We 
 * don't want to be out of the office bagging groceries when you call up 
 * needing a helping hand.  We'd rather spend our free time coding your 
 * feature requests than mowing the neighbors' lawns for rent money. 
 * 
 * We've never believed in encoding our source code out of paranoia over not 
 * getting paid.  We want you to have the full source code and be able to 
 * make the tweaks your organization requires to get more done -- despite 
 * having less of everything than you might need (time, people, money, 
 * energy).  We shouldn't be your bottleneck.
 * 
 * We've been building our expertise with this project since January 2002.  We 
 * promise spending a couple bucks [Euro, Yuan, Rupees, Galactic Credits] to 
 * let us take over your shared e-mail headache is a worthwhile investment.  
 * It will give you a sense of control over your in-box that you probably 
 * haven't had since spammers found you in a game of "E-mail Address 
 * Battleship".  Miss. Miss. You sunk my in-box!
 * 
 * A legitimate license entitles you to support, access to the developer 
 * mailing list, the ability to participate in betas and the warm fuzzy 
 * feeling of feeding a couple obsessed developers who want to help you get 
 * more done than 'the other guy'.
 *
 * - Jeff Standen, Mike Fogg, Brenan Cavish, Darren Sugita, Dan Hildebrandt
 * 		and Joe Geck.
 *   WEBGROUP MEDIA LLC. - Developers of Cerberus Helpdesk
 */
$db = DevblocksPlatform::getDatabaseService();
$datadict = NewDataDictionary($db,'mysql'); /* @var $datadict ADODB2_mysql */ // ,'mysql' 

$tables = $datadict->MetaTables();
$tables = array_flip($tables);

// Drop the Service Level fields on address
$columns = $datadict->MetaColumns('address');
$indexes = $datadict->MetaIndexes('address',false);

if(isset($columns['SLA_ID'])) {
	$sql = $datadict->DropColumnSQL('address','sla_id');
	$datadict->ExecuteSQLArray($sql);
}

if(isset($columns['SLA_EXPIRES'])) {
	$sql = $datadict->DropColumnSQL('address','sla_expires');
	$datadict->ExecuteSQLArray($sql);
}

// Drop the Service Level expires field on contact_org (later sla_id is migrated and dropped)
$columns = $datadict->MetaColumns('contact_org');
$indexes = $datadict->MetaIndexes('contact_org',false);

if(isset($columns['SLA_EXPIRES'])) {
	$sql = $datadict->DropColumnSQL('contact_org','sla_expires');
	$datadict->ExecuteSQLArray($sql);
}

// Drop the Service Level fields on tickets
$columns = $datadict->MetaColumns('ticket');
$indexes = $datadict->MetaIndexes('ticket',false);

if(isset($columns['SLA_ID'])) {
	$sql = $datadict->DropColumnSQL('ticket','sla_id');
	$datadict->ExecuteSQLArray($sql);
}

if(isset($columns['SLA_PRIORITY'])) {
	$sql = $datadict->DropColumnSQL('ticket','sla_priority');
	$datadict->ExecuteSQLArray($sql);
}

// Migrate contact_org.sla_id to a custom field dropdown
$columns = $datadict->MetaColumns('contact_org');
$indexes = $datadict->MetaIndexes('contact_org',false);

if(isset($columns['SLA_ID'])) {
	$sql = "SELECT count(id) FROM contact_org WHERE sla_id != ''";
	$count = $db->GetOne($sql);
	
	// Load the SLA hash
	$slas = array();
	if(isset($tables['sla'])) {
		$sql = "SELECT id, name FROM sla ORDER BY name";
		$rs = $db->Execute($sql);
		
		while(!$rs->EOF) {
			$slas[$rs->fields['id']] = $rs->fields['name'];
			$rs->MoveNext();
		}
	}
	
	if(!empty($count) && !empty($slas)) { // Move to a custom field before dropping
		// Create the new custom field
		$field_id = $db->GenID('custom_field_seq');
		$sql = sprintf("INSERT INTO custom_field (id,name,type,group_id,pos,options,source_extension) ".
			"VALUES (%d,'Service Level','D',0,0,%s,%s)",
			$field_id,
			$db->qstr(implode("\n",$slas)),
			$db->qstr('cerberusweb.fields.source.org')
		);
		$db->Execute($sql);
		
		// Populate the custom field from org records
		$sql = sprintf("INSERT INTO custom_field_value (field_id, source_id, field_value, source_extension) ".
			"SELECT %d, o.id, s.name, %s FROM contact_org o INNER JOIN sla s ON (o.sla_id=s.id)",
			$field_id,
			$db->qstr('cerberusweb.fields.source.org')
		);
		$db->Execute($sql);
	}
	
	// Drop the account number hardcoded column
	$sql = $datadict->DropColumnSQL('contact_org','sla_id');
	$datadict->ExecuteSQLArray($sql);
}

// Drop the SLA table
if(isset($tables['sla'])) {
	$sql = $datadict->DropTableSQL('sla');
	$datadict->ExecuteSQLArray($sql);
}

return TRUE;
?>