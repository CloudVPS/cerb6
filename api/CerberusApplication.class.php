<?php
include_once(UM_PATH . "/api/CerberusDAO.class.php");
include_once(UM_PATH . "/api/CerberusModel.class.php");
include_once(UM_PATH . "/api/CerberusExtension.class.php");

class CerberusApplication {
	
	private function CerberusApplication() {}
	
	static function getModules() {
		$modules = array();
		$extModules = UserMeetPlatform::getExtensions("com.cerberusweb.module");
		foreach($extModules as $mod) { /* @var $mod UserMeetExtensionManifest */
			$instance = $mod->createInstance(); /* @var $instance CerberusModuleExtension */
			if(is_a($instance,'usermeetextension') && $instance->isVisible())
				$modules[] = $instance;
		}
		return $modules;
	}
	
	static function setActiveModule($module=null) {
		static $activeModule;
		if(!is_null($module)) $activeModule = $module;
		return $activeModule;
	}
	
	static function getActiveModule() {
		return CerberusApplication::setActiveModule(); // returns
	}
	
	/**
	 * Enter description here...
	 *
	 * @return a unique ticket mask as a string
	 */
	static function generateTicketMask() {
		$letters = "ABCDEFGHIJKLMNPQRSTUVWXYZ";
		$numbers = "1234567890";
		$pattern = "LLL-NNNNN-NNN";
//		$pattern = "Y-M-D-LLLL";

		do {		
			// [JAS]: Seed randomness
			list($usec, $sec) = explode(' ', microtime());
			srand((float) $sec + ((float) $usec * 100000));
			
			$mask = "";
			$bytes = preg_split('//', $pattern, -1, PREG_SPLIT_NO_EMPTY);
			
			if(is_array($bytes))
			foreach($bytes as $byte) {
				switch(strtoupper($byte)) {
					case 'L':
						$mask .= substr($letters,rand(0,strlen($letters)-1),1);
						break;
					case 'N':
						$mask .= substr($numbers,rand(0,strlen($numbers)-1),1);
						break;
					case 'Y':
						$mask .= date('Y');
						break;
					case 'M':
						$mask .= date('n');
						break;
					case 'D':
						$mask .= date('j');
						break;
					default:
						$mask .= $byte;
						break;
				}
			}
		} while(null != CerberusTicketDAO::getTicketByMask($mask));
		
//		echo "Generated unique mask: ",$mask,"<BR>";
		
		return $mask;
	}
	
	static function generateMessageId() {
		$message_id = sprintf('<%s.%s@%s>', base_convert(time(), 10, 36), base_convert(rand(), 10, 36), !empty($_SERVER['HTTP_HOST']) ?  $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME']);
		return $message_id;
	}
	
	// ***************** DUMMY
	static function getDashboardViewColumns() {
		return array(
			new CerberusDashboardViewColumn('t.mask','ID'),
			new CerberusDashboardViewColumn('t.status','Status'),
			new CerberusDashboardViewColumn('t.priority','Priority'),
			new CerberusDashboardViewColumn('t.last_wrote','Last Wrote'),
			new CerberusDashboardViewColumn('t.first_wrote','First Wrote'),
			new CerberusDashboardViewColumn('t.created_date','Created Date'),
			new CerberusDashboardViewColumn('t.updated_date','Updated Date'),
		);
	}
	
	static function getTeamList() {
		$um_db = UserMeetDatabase::getInstance();

		$teams = array();
		
		$sql = sprintf("SELECT t.id , t.name ".
			"FROM team t ".
			"ORDER BY t.name ASC"
		);
		$rs = $um_db->Execute($sql) or die(__CLASS__ . ':' . $um_db->ErrorMsg()); /* @var $rs ADORecordSet */
		while(!$rs->EOF) {
			$team = new stdClass();
			$team->id = intval($rs->fields['id']);
			$team->name = $rs->fields['name'];
			$teams[$team->id] = $team;
			$rs->MoveNext();
		}
		
		return $teams;
	}

	/**
	 * Returns a list of all known mailboxes, sorted by name
	 *
	 * @return CerberusMailbox[]
	 */
	static function getMailboxList() {
		$um_db = UserMeetDatabase::getInstance();

		$mailboxes = array();
		
		$sql = sprintf("SELECT m.id , m.name, m.reply_address_id, m.display_name ".
			"FROM mailbox m ".
			"ORDER BY m.name ASC"
		);
		$rs = $um_db->Execute($sql) or die(__CLASS__ . ':' . $um_db->ErrorMsg()); /* @var $rs ADORecordSet */
		while(!$rs->EOF) {
			$mailbox = new CerberusMailbox();
			$mailbox->id = intval($rs->fields['id']);
			$mailbox->name = $rs->fields['name'];
			$mailbox->reply_address_id = $rs->fields['reply_address_id'];
			$mailbox->display_name = $$rs->fields['display_name'];
			$mailboxes[$mailbox->id] = $mailbox;
			$rs->MoveNext();
		}
		
		return $mailboxes;
	}
	
	static function createTeam($name) {
		$um_db = UserMeetDatabase::getInstance();
		$newId = $um_db->GenID('generic_seq');
		
		$sql = sprintf("INSERT INTO team (id, name) VALUES (%d,%s)",
			$newId,
			$um_db->qstr($name)
		);
		$um_db->Execute($sql) or die(__CLASS__ . ':' . $um_db->ErrorMsg()); /* @var $rs ADORecordSet */
		
		return $newId;
	}
	
	/**
	 * creates a new mailbox in the database
	 *
	 * @param string $name
	 * @param integer $reply_address_id
	 * @param string $display_name
	 * @return integer
	 */
	static function createMailbox($name, $reply_address_id, $display_name = '') {
		$um_db = UserMeetDatabase::getInstance();
		$newId = $um_db->GenID('generic_seq');
		
		$sql = sprintf("INSERT INTO mailbox (id, name, reply_address_id, display_name) VALUES (%d,%s,%d,%s)",
			$newId,
			$um_db->qstr($name),
			$reply_address_id,
			$um_db->qstr($display_name)
		);
		$um_db->Execute($sql) or die(__CLASS__ . ':' . $um_db->ErrorMsg()); /* @var $rs ADORecordSet */
		
		return $newId;
	}
	
	// ***************** DUMMY
	
};

?>