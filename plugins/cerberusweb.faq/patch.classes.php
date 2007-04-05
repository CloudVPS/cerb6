<?php
class ChFaqPatchContainer extends DevblocksPatchContainerExtension {
	const REV_0 = 0;
	
	function __construct($manifest) {
		parent::__construct($manifest);
		
		/*
		 * [JAS]: Just add a sequential build number here (and update plugin.xml) and
		 * write a case in runVersion().  You should comment the milestone next to your build 
		 * number.
		 */
		$revisions = array(
			REV_0, // Initial
		);
		
		if(is_array($revisions))
		foreach($revisions as $rev) {
			$this->registerPatch(new CerberusPatch('cerberusweb.faq',$rev,$this));
		}
	}

	function runRevision($rev) {
		switch($rev) {
			
			// 4.0 Beta Clean
			case REV_0:
				self::_initDatabase();
				break;
				
		}
		
		return TRUE;
	}
	
	private static function _initDatabase() {
		$db = DevblocksPlatform::getDatabaseService();
		$datadict = NewDataDictionary($db); /* @var $datadict ADODB_DataDict */ 

		$tables = array();
		
		// ***** Application

		$tables['faq'] = "
			id I4 DEFAULT 0 NOTNULL PRIMARY,
			question C(255) DEFAULT '' NOTNULL
		";
		
		$currentTables = $db->MetaTables('TABLE', false);

		if(is_array($tables))
		foreach($tables as $table => $flds) {
			if(false === array_search($table,$currentTables)) {
				$sql = $datadict->CreateTableSQL($table,$flds);
			//			print_r($sql);
				// [TODO] Buffer up success and fail messages?  Patcher!
				if(!$datadict->ExecuteSQLArray($sql,false)) {
					return FALSE;
				}
			}
//			echo "<HR>";
		}				
	}
	
};

?>