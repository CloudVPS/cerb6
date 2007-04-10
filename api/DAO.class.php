<?php
/*
 * [TODO] Mike's suggestion of using a self::update($id,$fields) call inside
 * the create function makes it much cleaner to do creates passing arbitrary lists
 * of arguments.  Voila!
 */

class DAO_Setting extends DevblocksORMHelper {
	static function set($key, $value) {
		$db = DevblocksPlatform::getDatabaseService();
		$db->Replace('setting',array('setting'=>$key,'value'=>$value),array('setting'),true);
	}
	
	static function get($key) {
		$db = DevblocksPlatform::getDatabaseService();
		$sql = sprintf("SELECT value FROM setting WHERE setting = %s",
			$db->qstr($key)
		);
		$value = $db->GetOne($sql) or die(__CLASS__ . ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
		
		return $value;
	}
	
	// [TODO] Cache as static/singleton or load up in a page scope object?
	static function getSettings() {
		$db = DevblocksPlatform::getDatabaseService();
		$settings = array();
		
		$sql = sprintf("SELECT setting,value FROM setting");
		$rs = $db->Execute($sql) or die(__CLASS__ . ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
		
		while(!$rs->EOF) {
			$settings[$rs->Fields('setting')] = $rs->Fields('value');
			$rs->MoveNext();
		}
		
		return $settings;
	}
};

class DAO_Bayes {
	private function DAO_Bayes() {}
	
	/**
	 * @return CerberusWord[]
	 */
	static function lookupWordIds($words) {
		$db = DevblocksPlatform::getDatabaseService();
		$tmp = array();
		$outwords = array(); // CerberusWord
		
		// Escaped set
		if(is_array($words))
		foreach($words as $word) {
			$tmp[] = $db->escape($word);
		}
		
		$sql = sprintf("SELECT id,word,spam,nonspam FROM bayes_words WHERE word IN ('%s')",
			implode("','", $tmp)
		);
		$rs = $db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
		
		// [JAS]: Keep a list of words we can check off as we index them with IDs
		$tmp = array_flip($words); // words are now keys
		
		// Existing Words
		while(!$rs->EOF) {
			$w = new CerberusBayesWord();
			$w->id = intval($rs->fields['id']);
			$w->word = $rs->fields['word'];
			$w->spam = intval($rs->fields['spam']);
			$w->nonspam = intval($rs->fields['nonspam']);
			
			$outwords[$w->word] = $w;
			unset($tmp[$w->word]); // check off we've indexed this word
			$rs->MoveNext();
		}
		
		// Insert new words
		if(is_array($tmp))
		foreach($tmp as $new_word => $v) {
			$new_id = $db->GenID('bayes_words_seq');
			$sql = sprintf("INSERT INTO bayes_words (id,word) VALUES (%d,%s)",
				$new_id,
				$db->qstr($new_word)
			);
			$db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
			
			$w = new CerberusBayesWord();
			$w->id = $new_id;
			$w->word = $new_word;
			$outwords[$w->word] = $w;
		}
		
		return $outwords;
	}
	
	/**
	 * @return array Two element array (keys: spam,nonspam)
	 */
	static function getStatistics() {
		$db = DevblocksPlatform::getDatabaseService();
		
		// [JAS]: [TODO] Change this into a 'replace' index?
		$sql = "SELECT spam, nonspam FROM bayes_stats";
		$rs = $db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
		
		if($rs->NumRows()) {
			$spam = intval($rs->Fields('spam'));
			$nonspam = intval($rs->Fields('nonspam'));
		} else {
			$spam = 0;
			$nonspam = 0;
			$sql = "INSERT INTO bayes_stats (spam, nonspam) VALUES (0,0)";
			$db->Execute($sql);
		}
		
		return array('spam' => $spam,'nonspam' => $nonspam);
	}
	
	static function addOneToSpamTotal() {
		$db = DevblocksPlatform::getDatabaseService();
		$sql = "UPDATE bayes_stats SET spam = spam + 1";
		$db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
	}
	
	static function addOneToNonSpamTotal() {
		$db = DevblocksPlatform::getDatabaseService();
		$sql = "UPDATE bayes_stats SET nonspam = nonspam + 1";
		$db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
	}
	
	static function addOneToSpamWord($word_id) {
		$db = DevblocksPlatform::getDatabaseService();
		$sql = sprintf("UPDATE bayes_words SET spam = spam + 1 WHERE id = %d", $word_id);
		$db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
	}
	
	static function addOneToNonSpamWord($word_id) {
		$db = DevblocksPlatform::getDatabaseService();
		$sql = sprintf("UPDATE bayes_words SET nonspam = nonspam + 1 WHERE id = %d", $word_id);
		$db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
	}
	
};

class DAO_Worker {
	private function DAO_Worker() {}
	
	const ID = 'id';
	const FIRST_NAME = 'first_name';
	const LAST_NAME = 'last_name';
	const TITLE = 'title';
	const EMAIL = 'email';
	const PASSWORD = 'pass';
	const IS_SUPERUSER = 'is_superuser';
	
	static function create($email, $password, $first_name, $last_name, $title) {
		if(empty($email) || empty($password))
			return null;
			
		$db = DevblocksPlatform::getDatabaseService();
		$id = $db->GenID('generic_seq');
		
		$sql = sprintf("INSERT INTO worker (id, email, pass, first_name, last_name, title) ".
			"VALUES (%d, %s, %s, %s, %s, %s)",
			$id,
			$db->qstr($email),
			$db->qstr(md5($password)),
			$db->qstr($first_name),
			$db->qstr($last_name),
			$db->qstr($title)
		);
		$rs = $db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
		
		return $id;
	}
	
	static function getList($ids=array()) {
		if(!is_array($ids)) $ids = array($ids);
		
		$db = DevblocksPlatform::getDatabaseService();
		$workers = array();
		
		$sql = "SELECT a.id, a.first_name, a.last_name, a.email, a.title, a.is_superuser ".
			"FROM worker a ".
			((!empty($ids) ? sprintf("WHERE a.id IN (%s)",implode(',',$ids)) : " ").
			"ORDER BY a.last_name, a.first_name "
		);
		$rs = $db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
		
		while(!$rs->EOF) {
			$worker = new CerberusWorker();
			$worker->id = intval($rs->fields['id']);
			$worker->first_name = $rs->fields['first_name'];
			$worker->last_name = $rs->fields['last_name'];
			$worker->email = $rs->fields['email'];
			$worker->title = $rs->fields['title'];
			$worker->is_superuser = $rs->fields['is_superuser'];
			$worker->last_activity_date = intval($rs->fields['last_activity_date']);
			$workers[$worker->id] = $worker;
			$rs->MoveNext();
		}
		
		return $workers;		
	}
	
	static function getAgent($id) {
		if(empty($id)) return null;
		
		$agents = DAO_Worker::getList(array($id));
		
		if(isset($agents[$id]))
			return $agents[$id];
			
		return null;
	}

	/**
	 * Enter description here...
	 *
	 * @param string $email
	 * @return integer $id
	 */
	static function lookupAgentEmail($email) {
		if(empty($email)) return null;
		$db = DevblocksPlatform::getDatabaseService();
		
		$sql = sprintf("SELECT a.id FROM worker a WHERE a.email = %s",
			$db->qstr($email)
		);
		$rs = $db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
		
		if(!$rs->EOF) {
			return intval($rs->fields['id']);
		}
		
		return null;		
	}
	
	static function updateAgent($id, $fields) {
		$db = DevblocksPlatform::getDatabaseService();
		$sets = array();
		
		if(!is_array($fields) || empty($fields) || empty($id))
			return;
		
		foreach($fields as $k => $v) {
			$sets[] = sprintf("%s = %s",
				$k,
				$db->qstr($v)
			);
		}
			
		$sql = sprintf("UPDATE worker SET %s WHERE id = %d",
			implode(', ', $sets),
			$id
		);
		$db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
		
	}
	
	static function deleteAgent($id) {
		if(empty($id)) return;
		
		$db = DevblocksPlatform::getDatabaseService();
		
		$sql = sprintf("DELETE FROM worker WHERE id = %d",
			$id
		);
		$db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
		
		$sql = sprintf("DELETE FROM worker_to_team WHERE agent_id = %d",
			$id
		);
		$db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
		
//		$sql = sprintf("DELETE FROM favorite_tag_to_worker WHERE agent_id = %d",
//			$id
//		);
//		$db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
		
	}
	
	static function login($email, $password) {
		$db = DevblocksPlatform::getDatabaseService();

		$sql = sprintf("SELECT id ".
			"FROM worker ".
			"WHERE email = %s ".
			"AND pass = MD5(%s)",
				$db->qstr($email),
				$db->qstr($password)
		);
		$worker_id = $db->GetOne($sql); // or die(__CLASS__ . ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */

		if(!empty($worker_id)) {
			return self::getAgent($worker_id);
		}
		
		return null;
	}
	
	static function setAgentTeams($agent_id, $team_ids) {
		if(!is_array($team_ids)) $team_ids = array($team_ids);
		if(empty($agent_id)) return;
		$db = DevblocksPlatform::getDatabaseService();

		$sql = sprintf("DELETE FROM worker_to_team WHERE agent_id = %d",
			$agent_id
		);
		$db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
		
		foreach($team_ids as $team_id) {
			$sql = sprintf("INSERT INTO worker_to_team (agent_id, team_id) ".
				"VALUES (%d,%d)",
				$agent_id,
				$team_id
			);
			$db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
		}
	}
	
	static function getAgentTeams($agent_id) {
		if(empty($agent_id)) return;
		$db = DevblocksPlatform::getDatabaseService();
		$ids = array();
		
		$sql = sprintf("SELECT wt.team_id FROM worker_to_team wt WHERE wt.agent_id = %d",
			$agent_id
		);
		$rs = $db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
		
		while(!$rs->EOF) {
			$ids[] = intval($rs->fields['team_id']);
			$rs->MoveNext();
		}
		
		if(empty($ids))
			return array();
		
		return DAO_Workflow::getTeams($ids);
	}
	
//	static function getFavoriteTags($agent_id) {
//		$db = DevblocksPlatform::getDatabaseService();
//		if(empty($agent_id)) return null;
//		
//		$ids = array();
//		
//		$sql = sprintf("SELECT tag_id FROM favorite_tag_to_worker WHERE agent_id = %d",
//			$agent_id
//		);
//		$rs = $db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
//		
//		while(!$rs->EOF) {
//			$ids[] = intval($rs->fields['tag_id']);
//			$rs->MoveNext();
//		}
//
//		if(empty($ids))
//			return array();
//		
//		return DAO_Workflow::getTags($ids);
//	}
	
//	static function setFavoriteTags($agent_id, $tag_string) {
//		$db = DevblocksPlatform::getDatabaseService();
//		if(empty($agent_id)) return null;
//		
//		$sql = sprintf("DELETE FROM favorite_tag_to_worker WHERE agent_id = %d",
//			$agent_id
//		);
//		$db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
//		
//		$tags = CerberusApplication::parseCsvString($tag_string);
//		$ids = array();
//		
//		foreach($tags as $tag_name) {
//			$tag = DAO_Workflow::lookupTag($tag_name, true);
//			$ids[$tag->id] = $tag->id;
//		}
//		
//		foreach($ids as $tag_id) {
//			$sql = sprintf("INSERT INTO favorite_tag_to_worker (tag_id, agent_id) ".
//				"VALUES (%d,%d) ",
//					$tag_id,
//					$agent_id
//			);
//			$db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */	
//		}
//		
//	}
	
	static function getFavoriteWorkers($agent_id) {
		$db = DevblocksPlatform::getDatabaseService();
		if(empty($agent_id)) return null;
		
		$ids = array();
		
		$sql = sprintf("SELECT worker_id FROM favorite_worker_to_worker WHERE agent_id = %d",
			$agent_id
		);
		$rs = $db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
		
		while(!$rs->EOF) {
			$ids[] = intval($rs->fields['worker_id']);
			$rs->MoveNext();
		}

		if(empty($ids))
			return array();
		
		return DAO_Worker::getList($ids);
	}
	
	static function setFavoriteWorkers($agent_id, $worker_string) {
		$db = DevblocksPlatform::getDatabaseService();
		if(empty($agent_id)) return null;
		
		$sql = sprintf("DELETE FROM favorite_worker_to_worker WHERE agent_id = %d",
			$agent_id
		);
		$db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
		
		$workers = CerberusApplication::parseCsvString($worker_string);
		$ids = array();
		
		foreach($workers as $worker_name) {
			$worker_id = DAO_Worker::lookupAgentEmail($worker_name);
			
			if(null == $worker_id)
				continue;

			$ids[$worker_id] = $worker_id;
		}
		
		foreach($ids as $worker_id) {
			$sql = sprintf("INSERT INTO favorite_worker_to_worker (worker_id, agent_id) ".
				"VALUES (%d,%d) ",
					$worker_id,
					$agent_id
			);
			$db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */	
		}
		
	}
	
	// [TODO] Test where this is used
	static function searchAgents($query, $limit=10) {
		$db = DevblocksPlatform::getDatabaseService();
		if(empty($query)) return null;
		
		$sql = sprintf("SELECT w.id FROM worker w WHERE w.email LIKE '%s%%' LIMIT 0,%d",
			$query,
			$limit
		);
		$rs = $db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
		
		$ids = array();
		
		while(!$rs->EOF) {
			$ids[] = intval($rs->fields['id']);
			$rs->MoveNext();
		}
		
		if(empty($ids))
			return array();
			
		return DAO_Worker::getList($ids);
	}
	
}

class DAO_Contact {
	private function DAO_Contact() {}
	
	// [JAS]: [TODO] Move this into MailDAO
	static function lookupAddress($email,$create_if_null=false) {
		$db = DevblocksPlatform::getDatabaseService();
		$id = null;
		
		$sql = sprintf("SELECT id FROM address WHERE email = %s",
			$db->qstr(trim(strtolower($email)))
		);
		$rs = $db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
		
		if(!$rs->EOF) {
			$id = $rs->fields['id'];
		} elseif($create_if_null) {
			$id = DAO_Contact::createAddress($email);
		}
		
		return $id;
	}
	
	// [JAS]: [TODO] Move this into MailDAO
	static function getAddresses($ids=array()) {
		$db = DevblocksPlatform::getDatabaseService();
		if(!is_array($ids)) $ids = array($ids);
		$addresses = array();
		
		$sql = sprintf("SELECT a.id, a.email, a.personal, a.bitflags ".
			"FROM address a ".
			((!empty($ids)) ? "WHERE a.id IN (%s) " : " ").
			"ORDER BY a.email ",
			implode(',', $ids)
		);
		$rs = $db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
		
		while(!$rs->EOF) {
			$address = new CerberusAddress();
			$address->id = intval($rs->fields['id']);
			$address->email = $rs->fields['email'];
			$address->personal = $rs->fields['personal'];
			$address->bitflags = intval($rs->fields['bitflags']);
			$addresses[$address->id] = $address;
			$rs->MoveNext();
		}
		
		return $addresses;
	}
	
	// [JAS]: [TODO] Move this into MailDAO
	static function getAddress($id) {
		if(empty($id)) return null;
		
		$addresses = DAO_Contact::getAddresses(array($id));
		
		if(isset($addresses[$id]))
			return $addresses[$id];
			
		return null;		
	}

//	// [JAS]: [TODO] Move this into MailDAO
//	static function getMailboxIdByAddress($email) {
//		$db = DevblocksPlatform::getDatabaseService();
//		$id = DAO_Contact::lookupAddress($email,false);
//		$mailbox_id = null;
//		
//		if(empty($id))
//			return null;
//		
//		$sql = sprintf("SELECT am.mailbox_id FROM address_to_mailbox am WHERE am.address_id = %d",
//			$id
//		);
//		$rs = $db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
//		
//		if(!$rs->EOF) {
//			$mailbox_id = intval($rs->fields['mailbox_id']);
//		}
//		
//		return $mailbox_id;
//	}
	
	// [JAS]: [TODO] Move this into MailDAO
	/**
	 * creates an address entry in the database if it doesn't exist already
	 *
	 * @param string $email
	 * @param string $personal
	 * @return integer
	 * @throws exception on invalid address
	 */
	static function createAddress($email,$personal='') {
		$db = DevblocksPlatform::getDatabaseService();
		
		if(null != ($id = DAO_Contact::lookupAddress($email,false)))
			return $id;

		$id = $db->GenID('address_seq');
		
		/*
		 * [JAS]: [TODO] If we're going to call platform libs directly we should just have
		 * the platform provide the functionality.
		 */
		// [TODO] This code fails with anything@localhost
//		require_once(DEVBLOCKS_PATH . 'libs/pear/Mail/RFC822.php');
		if (false === Mail_RFC822::isValidInetAddress($email)) {
//			throw new Exception($email . DevblocksTranslationManager::say('ticket.requester.invalid'));
			return null;
		}
		
		$sql = sprintf("INSERT INTO address (id,email,personal,bitflags) VALUES (%d,%s,%s,0)",
			$id,
			$db->qstr(trim(strtolower($email))),
			$db->qstr($personal)
		);
		$db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
		
		return $id;
	}
	
}

/**
 * Enter description here...
 *
 * @addtogroup dao
 */
class DAO_Ticket {
	const ID = 'id';
	const MASK = 'mask';
	const SUBJECT = 'subject';
	const STATUS = 'status';
	const TEAM_ID = 'team_id';
	const CATEGORY_ID = 'category_id';
	const LAST_WROTE_ID = 'last_wrote_address_id';
	const FIRST_WROTE_ID = 'first_wrote_address_id';
	const CREATED_DATE = 'created_date';
	const UPDATED_DATE = 'updated_date';
	const PRIORITY = 'priority';
	const SPAM_TRAINING = 'spam_training';
	const SPAM_SCORE = 'spam_score';
	const NUM_TASKS = 'num_tasks';
	
	private function DAO_Ticket() {}
	
	/**
	 * Enter description here...
	 *
	 * @param string $mask
	 * @return CerberusTicket
	 */
	static function getTicketByMask($mask) {
		$db = DevblocksPlatform::getDatabaseService();
		
		$sql = sprintf("SELECT t.id FROM ticket t WHERE t.mask = %s",
			$db->qstr($mask)
		);
		$rs = $db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
		
		if(!$rs->EOF) {
			$ticket_id = intval($rs->fields['id']);
			return DAO_Ticket::getTicket($ticket_id);
		}
		
		return null;
	}
	
	static function getTicketByMessageId($message_id) {
		$db = DevblocksPlatform::getDatabaseService();
		
		$sql = sprintf("SELECT t.id ".
			"FROM ticket t ".
			"INNER JOIN message m ON (t.id=m.ticket_id) ".
			"WHERE m.message_id = %s",
			$db->qstr($message_id)
		);
		$rs = $db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
		
		if(!$rs->EOF) {
			$ticket_id = intval($rs->fields['id']);
			return $ticket_id;
		}
		
		return null;
	}
	
	/**
	 * Adds an attachment link to the database (this is informational only, it does not contain
	 * the actual attachment)
	 *
	 * @param integer $message_id
	 * @param string $display_name
	 * @param string $filepath
	 * @return integer
	 */
	static function createAttachment($message_id, $display_name, $filepath) {
		$db = DevblocksPlatform::getDatabaseService();
		$newId = $db->GenID('attachment_seq');
		
		$sql = sprintf("INSERT INTO attachment (id, message_id, display_name, filepath)".
			"VALUES (%d,%d,%s,%s)",
			$newId,
			$message_id,
			$db->qstr($display_name),
			$db->qstr($filepath)
		);
		$db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
		
		return $newId;
	}
	
	/**
	 * returns an array of CerberusAttachments that
	 * correspond to the supplied message id.
	 *
	 * @param integer $id
	 * @return CerberusAttachment[]
	 */
	static function getAttachmentsByMessage($id) {
		$db = DevblocksPlatform::getDatabaseService();
		
		$sql = sprintf("SELECT a.id, a.message_id, a.display_name, a.filepath ".
			"FROM attachment a WHERE a.message_id = %d",
			$id
		);
		$rs = $db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
		
		$attachments = array();
		while(!$rs->EOF) {
			$attachment = new CerberusAttachment();
			$attachment->id = intval($rs->fields['id']);
			$attachment->message_id = intval($rs->fields['message_id']);
			$attachment->display_name = $rs->fields['display_name'];
			$attachment->filepath = $rs->fields['filepath'];
			$attachments[] = $attachment;
			$rs->MoveNext();
		}

		return $attachments;
	}
	
	/**
	 * creates a new ticket object in the database
	 *
	 * @param string $mask
	 * @param string $subject
	 * @param string $status
	 * @param string $last_wrote
	 * @param integer $created_date
	 * @return integer
	 * 
	 * [TODO]: Change $last_wrote argument to an ID rather than string?
	 */
	static function createTicket($fields) {
		$db = DevblocksPlatform::getDatabaseService();
		$newId = $db->GenID('ticket_seq');
		
		$sql = sprintf("INSERT INTO ticket (id, mask, subject, status, last_wrote_address_id, first_wrote_address_id, created_date, updated_date, priority) ".
			"VALUES (%d,'','','O',0,0,%d,%d,0)",
			$newId,
			gmmktime(),
			gmmktime()
		);
		$db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
		
		self::updateTicket($newId, $fields);
		
		// send new ticket auto-response
//		DAO_Mail::sendAutoresponse($id, 'new');
		
		return $newId;
	}

	static function createMessage($ticket_id,$type,$created_date,$address_id,$headers,$content) {
		$db = DevblocksPlatform::getDatabaseService();
		$newId = $db->GenID('message_seq');
		
		// [JAS]: Flatten an array of headers into a string.
		$sHeaders = serialize($headers);

		$sql = sprintf("INSERT INTO message (id,ticket_id,message_type,created_date,address_id,message_id,headers,content) ".
			"VALUES (%d,%d,%s,%d,%d,%s,%s,%s)",
				$newId,
				$ticket_id,
				$db->qstr($type),
				$created_date,
				$address_id,
				((isset($headers['message-id'])) ? $db->qstr($headers['message-id']) : "''"),
				$db->qstr($sHeaders),
				$db->qstr($content)
		);
		
		$db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
		
		return $newId;
	}

	/**
	 * Enter description here...
	 *
	 * @param integer $id
	 * @return CerberusTicket
	 */
	static function getTicket($id) {
		if(empty($id)) return NULL;
		
		$tickets = self::getTickets(array($id));
		
		if(isset($tickets[$id]))
			return $tickets[$id];
			
		return NULL;
	}
	
	/**
	 * Enter description here...
	 *
	 * @param array $ids
	 * @return CerberusTicket[]
	 */
	static function getTickets($ids=array()) {
		if(!is_array($ids)) $ids = array($ids);
		$db = DevblocksPlatform::getDatabaseService();
		
		$tickets = array();
		
		$sql = sprintf("SELECT t.id , t.mask, t.subject, t.status, t.priority, t.team_id, t.category_id, ".
			"t.first_wrote_address_id, t.last_wrote_address_id, t.created_date, t.updated_date, t.spam_training, t.spam_score ".
			"FROM ticket t ".
			(!empty($ids) ? sprintf("WHERE t.id IN (%s) ",implode(',',$ids)) : " ").
			"ORDER BY t.updated_date DESC",
			$id
		);
		$rs = $db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
		
		while(!$rs->EOF) {
			$ticket = new CerberusTicket();
			$ticket->id = intval($rs->fields['id']);
			$ticket->mask = $rs->fields['mask'];
			$ticket->subject = $rs->fields['subject'];
			$ticket->team_id = intval($rs->fields['team_id']);
			$ticket->category_id = intval($rs->fields['category_id']);
			$ticket->status = $rs->fields['status'];
			$ticket->priority = intval($rs->fields['priority']);
			$ticket->last_wrote_address_id = intval($rs->fields['last_wrote_address_id']);
			$ticket->first_wrote_address_id = intval($rs->fields['first_wrote_address_id']);
			$ticket->created_date = intval($rs->fields['created_date']);
			$ticket->updated_date = intval($rs->fields['updated_date']);
			$ticket->spam_score = floatval($rs->fields['spam_score']);
			$ticket->spam_training = $rs->fields['spam_training'];
			$tickets[$ticket->id] = $ticket;
			$rs->MoveNext();
		}
		
		return $tickets;
	}
	
	static function updateTicket($id,$fields) {
		$db = DevblocksPlatform::getDatabaseService();
		$sets = array();
		
		if(!is_array($fields) || empty($fields) || empty($id))
			return;
		
		foreach($fields as $k => $v) {
			switch ($k) {
				case 'status':
//					if (0 == strcasecmp($v, 'C')) // if ticket is being closed
//						DAO_Mail::sendAutoresponse($id, 'closed');
					break;
			}
			$sets[] = sprintf("%s = %s",
				$k,
				$db->qstr($v)
			);
		}
			
		$sql = sprintf("UPDATE ticket SET %s WHERE id = %d",
			implode(', ', $sets),
			$id
		);
		$db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
	}
	
	/**
	 * @return CerberusMessage[]
	 */
	static function getMessagesByTicket($ticket_id) {
		$db = DevblocksPlatform::getDatabaseService();
		$messages = array();
		
		$sql = sprintf("SELECT m.id , m.ticket_id, m.message_type, m.created_date, m.address_id, m.message_id, m.headers ".
			"FROM message m ".
			"WHERE m.ticket_id = %d ".
			"ORDER BY m.created_date ASC ",
			$ticket_id
		);
		$rs = $db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
		while(!$rs->EOF) {
			$message = new CerberusMessage();
			$message->id = intval($rs->fields['id']);
			$message->ticket_id = intval($rs->fields['ticket_id']);
			$message->message_type = $rs->fields['message_type'];
			$message->created_date = intval($rs->fields['created_date']);
			$message->address_id = intval($rs->fields['address_id']);
			$message->message_id = $rs->fields['message_id'];
			
			$headers = unserialize($rs->fields['headers']);
			$message->headers = $headers;

			$messages[$message->id] = $message;
			$rs->MoveNext();
		}

		// [JAS]: Count all
		$rs = $db->Execute($sql);
		$total = $rs->RecordCount();
		
		return array($messages,$total);
	}
	
	/**
	 * Enter description here...
	 *
	 * @param integer $id message id
	 * @return CerberusMessage
	 */
	static function getMessage($id) {
		$db = DevblocksPlatform::getDatabaseService();
		$message = null;
		
		$sql = sprintf("SELECT m.id , m.ticket_id, m.message_type, m.created_date, m.address_id, m.message_id, m.headers ".
			"FROM message m ".
			"WHERE m.id = %d ".
			"ORDER BY m.created_date ASC ",
			$id
		);
		$rs = $db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
		if(!$rs->EOF) {
			$message = new CerberusMessage();
			$message->id = intval($rs->fields['id']);
			$message->ticket_id = intval($rs->fields['ticket_id']);
			$message->message_type = $rs->fields['message_type'];
			$message->created_date = intval($rs->fields['created_date']);
			$message->address_id = intval($rs->fields['address_id']);
			$message->message_id = $rs->fields['message_id'];
			
			$headers = unserialize($rs->fields['headers']);
			$message->headers = $headers;
		}

		// [JAS]: Count all
//		$rs = $db->Execute($sql);
//		$total = $rs->RecordCount();
		
		return $message;
//		return array($messages,$total);
	}
	
	static function getRequestersByTicket($ticket_id) {
		$db = DevblocksPlatform::getDatabaseService();
		$addresses = array();
		
		$sql = sprintf("SELECT a.id , a.email, a.personal ".
			"FROM address a ".
			"INNER JOIN requester r ON (r.ticket_id = %d AND a.id=r.address_id) ".
			"ORDER BY a.email ASC ",
			$ticket_id
		);
		$rs = $db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
		while(!$rs->EOF) {
			$address = new CerberusAddress();
			$address->id = intval($rs->fields['id']);
			$address->email = $rs->fields['email'];
			$address->personal = $rs->fields['personal'];
			$addresses[$address->id] = $address;
			$rs->MoveNext();
		}

		// [JAS]: Count all
//		$rs = $db->Execute($sql);
//		$total = $rs->RecordCount();
//		return array($addresses,$total);

		return $addresses;
	}
	
	static function getMessageContent($id) {
		$db = DevblocksPlatform::getDatabaseService();
		$content = '';
		
		$sql = sprintf("SELECT m.id, m.content ".
			"FROM message m ".
			"WHERE m.id = %d ",
			$id
		);
		$rs = $db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
		
		if(!$rs->EOF) {
			$content = $rs->fields['content'];
		}
		
		return $content;
	}
	
	static function createRequester($address_id,$ticket_id) {
		$db = DevblocksPlatform::getDatabaseService();
		$db->Replace("requester",array("address_id"=>$address_id,"ticket_id"=>$ticket_id),array("address_id","ticket_id")); 
		return true;
	}
	
};

/**
 * Enter description here...
 *
 * @addtogroup dao
 */
class DAO_Dashboard {
	private function DAO_Dashboard() {}
	
	static function createDashboard($name, $agent_id) {
		$db = DevblocksPlatform::getDatabaseService();
		$newId = $db->GenID('generic_seq');
		
		$sql = sprintf("INSERT INTO dashboard (id, name, agent_id) ".
			"VALUES (%d, %s, %d)",
			$newId,
			$db->qstr($name),
			$agent_id
		);
		$db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
		
		return $newId;
	}
	
	// [JAS]: Convert this over to pulling by a list of IDs?
	static function getDashboards($agent_id=0) {
		$db = DevblocksPlatform::getDatabaseService();
		
		$sql = sprintf("SELECT id, name ".
			"FROM dashboard "
//			(($agent_id) ? sprintf("WHERE agent_id = %d ",$agent_id) : " ")
		);
		$rs = $db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
		
		$dashboards = array();
		
		while(!$rs->EOF) {
			$dashboard = new CerberusDashboard();
			$dashboard->id = intval($rs->fields['id']);
			$dashboard->name = $rs->fields['name'];
			$dashboard->agent_id = intval($rs->fields['agent_id']);
			$dashboards[$dashboard->id] = $dashboard;
			$rs->MoveNext();
		}
		
		return $dashboards;
	}
	
	static function createView($name,$dashboard_id,$num_rows=10,$sort_by=null,$sort_asc=1,$type='D') {
		$db = DevblocksPlatform::getDatabaseService();
		$newId = $db->GenID('generic_seq');
		
		$sql = sprintf("INSERT INTO dashboard_view (id, name, dashboard_id, type, num_rows, sort_by, sort_asc, page, params) ".
			"VALUES (%d, %s, %d, %s, %d, %s, %s, %d, '')",
			$newId,
			$db->qstr($name),
			$dashboard_id,
			$db->qstr($type),
			$num_rows,
			$db->qstr($sort_by),
			$sort_asc,
			0
		);
		$db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
		
		return $newId;
	}
	
	static private function _updateView($id,$fields) {
		$db = DevblocksPlatform::getDatabaseService();
		$sets = array();
		
		if(!is_array($fields) || empty($fields) || empty($id))
			return;
		
		foreach($fields as $k => $v) {
			$sets[] = sprintf("%s = %s",
				$k,
				$db->qstr($v)
			);
		}
			
		$sql = sprintf("UPDATE dashboard_view SET %s WHERE id = %d",
			implode(', ', $sets),
			$id
		);
		$db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
	}
	
	static function deleteView($id) {
		if(empty($id)) return;
		$db = DevblocksPlatform::getDatabaseService();
		
		$sql = sprintf("DELETE FROM dashboard_view WHERE id = %d",
			$id
		);
		
		$db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
	}
	
	/**
	 * Enter description here...
	 *
	 * @param integer $dashboard_id
	 * @return CerberusDashboardView[]
	 */
	static function getViews($dashboard_id=0) {
		$db = DevblocksPlatform::getDatabaseService();
		
		$sql = sprintf("SELECT v.id, v.name, v.dashboard_id, v.type, v.view_columns, v.num_rows, v.sort_by, v.sort_asc, v.page, v.params ".
			"FROM dashboard_view v ".
			"WHERE v.dashboard_id > 0 "
//			(!empty($dashboard_id) ? sprintf("WHERE v.dashboard_id = %d ", $dashboard_id) : " ")
		);
		$rs = $db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
		
		$views = array();
		
		while(!$rs->EOF) {
			$view = new CerberusDashboardView();
			$view->id = $rs->fields['id'];
			$view->name = $rs->fields['name'];
			$view->dashboard_id = intval($rs->fields['dashboard_id']);
			$view->type = $rs->fields['type'];
			$view->view_columns = unserialize($rs->fields['view_columns']);
			$view->params = unserialize($rs->fields['params']);
			$view->renderLimit = intval($rs->fields['num_rows']);
			$view->renderSortBy = $rs->fields['sort_by'];
			$view->renderSortAsc = intval($rs->fields['sort_asc']);
			$view->renderPage = intval($rs->fields['page']);
			$views[$view->id] = $view; 
			$rs->MoveNext();
		}
		
		return $views;
	}
	
	/**
	 * Loads or creates a view for a given agent
	 *
	 * @param integer $view_id
	 * @return CerberusDashboardView
	 */
	static function getView($view_id) {
		$view = NULL;
		$visit = CerberusApplication::getVisit();
		$viewManager = $visit->get(CerberusVisit::KEY_VIEW_MANAGER);
		
		if(!empty($view_id) && is_numeric($view_id)) { // custom view
			$view = DAO_Dashboard::_getView($view_id);
			
		} elseif($viewManager->exists($view_id)) {
			$view =& $viewManager->getView($view_id);
		}
		
		return $view;
	}
	
	static function updateView($view_id, $fields) {
		$visit = CerberusApplication::getVisit();
		
		if(method_exists($visit,'get')) {
			$viewManager = $visit->get(CerberusVisit::KEY_VIEW_MANAGER);
		}
		
		if(is_numeric($view_id)) { // db-driven view
			DAO_Dashboard::_updateView($view_id, $fields);
			
		} elseif($viewManager->exists($view_id)) { // virtual view
			$view =& $viewManager->getView($view_id); /* @var $view CerberusDashboardView */
			
			foreach($fields as $key => $value) {
				switch($key) {
					case 'name':
						$view->name = $value;
						break;
					case 'view_columns':
						$view->view_columns = unserialize($value);
						break;
					case 'params':
						$view->params = unserialize($value);
						break;
					case 'num_rows':
						$view->renderLimit = intval($value);
						break;
					case 'page':
						$view->renderPage = intval($value);
						break;
					case 'type':
						$view->type = $value;
						break;
					case 'sort_by':
						$view->renderSortBy = $value;
						break;
					case 'sort_asc':
						$view->renderSortAsc = (boolean) $value;
						break;
				}
			}
		}		
	}
	
	/**
	 * Enter description here...
	 *
	 * @param integer $view_id
	 * @return CerberusDashboardView
	 * [TODO] This should wrap getViews()
	 */
	static private function _getView($view_id) {
		$db = DevblocksPlatform::getDatabaseService();
		
		$sql = sprintf("SELECT v.id, v.name, v.dashboard_id, v.type, v.view_columns, v.num_rows, v.sort_by, v.sort_asc, v.page, v.params ".
			"FROM dashboard_view v ".
			"WHERE v.id = %d ",
			$view_id
		);
		$rs = $db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */

		if(!$rs->EOF) {
			$view = new CerberusDashboardView();
			$view->id = $rs->fields['id'];
			$view->name = $rs->fields['name'];
			$view->dashboard_id = intval($rs->fields['dashboard_id']);
			$view->type = $rs->fields['type'];
			$view->view_columns = unserialize($rs->fields['view_columns']);
			$view->params = unserialize($rs->fields['params']);
			$view->renderLimit = intval($rs->fields['num_rows']);
			$view->renderSortBy = $rs->fields['sort_by'];
			$view->renderSortAsc = intval($rs->fields['sort_asc']);
			$view->renderPage = intval($rs->fields['page']);
			$views[$view->id] = $view; 
			return $view;
		}
		
		return null;
	}
	
};

class DAO_DashboardViewAction extends DevblocksORMHelper {
	static $properties = array(
		'table' => 'dashboard_view_action',
		'id_column' => 'id'
	);

	// [TODO] Const? (Fix references)
	static public $FIELD_ID = 'id';
	static public $FIELD_VIEW_ID = 'dashboard_view_id';
	static public $FIELD_NAME = 'name';
	static public $FIELD_WORKER_ID = 'worker_id';
	static public $FIELD_PARAMS = 'params';
	
	/**
	 * Create a DAO entity.
	 *
	 * @return integer
	 */
	static function create($fields) {
		$db = DevblocksPlatform::getDatabaseService();
		
		$id = $db->GenID('generic_seq');
		
		$sql = sprintf("INSERT INTO %s (id,dashboard_view_id,name,worker_id,params) ".
			"VALUES (%d,0,'',0,'')",
			self::$properties['table'],
			$id
		);
		$rs = $db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
		
		self::update($id, $fields);
		
		return $id;
	}

	/**
	 * Update a DAO entity.
	 *
	 * @param integer $id
	 * @param array $fields
	 */
	static function update($id, $fields) {
		parent::_update($id,self::$properties['table'],$fields);
	}
	
	/**
	 * Get multiple DAO entities.
	 *
	 * @param array $ids
	 * @return Model_DashboardViewAction[]
	 */
	static function getList($ids=array()) {
		if(!is_array($ids)) $ids = array($ids);
		$db = DevblocksPlatform::getDatabaseService();
		$objects = array();
		
		$sql = sprintf("SELECT id, dashboard_view_id, name, worker_id, params ".
			"FROM %s ".
			(!empty($ids) ? sprintf("WHERE id IN (%s) ",implode(',',$ids)) : ""),
				self::$properties['table']
		);
		$rs = $db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
		
		if($rs->NumRows())
		while(!$rs->EOF) {
			$object = new Model_DashboardViewAction();
			$object->id = intval($rs->Fields('id'));
			$object->dashboard_view_id = intval($rs->Fields('dashboard_view_id'));
			$object->name = $rs->Fields('name');
			$object->worker_id = intval($rs->Fields('worker_id'));
			
			$params = $rs->Fields('params');
			$object->params = !empty($params) ? unserialize($params) : array();
			
			$objects[$object->id] = $object;
			$rs->MoveNext();
		}
		
		return $objects;
	}
	
	/**
	 * Get a single DAO entity.
	 *
	 * @param integer $id
	 * @return Model_DashboardViewAction
	 */
	static function get($id) {
		if(empty($id)) return NULL;
		
		$results = self::getList(array($id));
		
		if(isset($results[$id])) 
			return $results[$id];
			
		return NULL;
	}
	
	/**
	 * Delete a DAO entity.
	 *
	 * @param integer $id
	 */
	static function delete($id) {
		$db = DevblocksPlatform::getDatabaseService();
		
		$sql = sprintf("DELETE FROM %s WHERE %s = %d",
			self::$properties['table'],
			self::$properties['id_column'],
			$id
		);
		$db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
		
		// [TODO]: Don't forget to also cascade deletes for foreign keys.
	}
	
};

class DAO_MailRule {
	private function DAO_MailRule() {}
	
	/**
	 * creates a new mail rule
	 *
	 * @param CerberusMailRuleCriterion[] $criteria
	 * @param string $sequence
	 * @param string $strictness
	 */
	static function createMailRule ($criteria, $sequence, $strictness) {
		$db = DevblocksPlatform::getDatabaseService();
		$newId = $db->GenID('generic_seq');
		
		$sCriteria = serialize($criteria); // Flatten criterion array into a string
		
		$sql = sprintf("INSERT INTO mail_rule (id, criteria, sequence, strictness) ".
			"VALUES (%d, %s, %s, %s)",
			$newId,
			$db->qstr($sCriteria),
			$db->qstr($sequence),
			$db->qstr($strictness)
		);
		$db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg());
	}
	
	/**
	 * deletes a mail rule from the database
	 *
	 * @param integer $id
	 */
	static function deleteMailRule ($id) {
		if(empty($id)) return;
		$db = DevblocksPlatform::getDatabaseService();
		
		$sql = sprintf("DELETE FROM mail_rule WHERE id = %d",
			$id
		);
		$db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg());
	}
	
	/**
	 * returns the mail rule with the given id
	 *
	 * @param integer $id
	 * @return CerberusMailRule
	 */
	static function getMailRule ($id) {
		$db = DevblocksPlatform::getDatabaseService();
		
		$sql = sprintf("SELECT m.id, m.criteria, m.sequence, m.strictness ".
			"FROM mail_rule m ".
			"WHERE m.id = %d",
			$id
		);
		$rs = $db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg());
		
		$mailRule = new CerberusMailRule();
		while(!$rs->EOF) {
			$mailRule->id = intval($rs->fields['id']);
			$mailRule->sequence = $rs->fields['sequence'];
			$mailRule->strictness = $rs->fields['strictness'];
			
			$criteria = unserialize($rs->fields['criteria']);
			$mailRule->criteria = $criteria;

			$mailRules[$mailRule->id] = $mailRule;
			$rs->MoveNext();
		}
		
		return $mailRule;
	}
	
	/**
	 * returns an array of all mail rules
	 *
	 * @return CerberusMailRule[]
	 */
	static function getMailRules () {
		$db = DevblocksPlatform::getDatabaseService();
		
		$sql = sprintf("SELECT m.id, m.criteria, m.sequence, m.strictness ".
			"FROM mail_rule m"
		);
		$rs = $db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg());
		
		$mailRules = array();
		
		while(!$rs->EOF) {
			$mailRule = new CerberusMailRule();
			$mailRule->id = intval($rs->fields['id']);
			$mailRule->sequence = $rs->fields['sequence'];
			$mailRule->strictness = $rs->fields['strictness'];
			
			$criteria = unserialize($rs->fields['criteria']);
			$mailRule->criteria = $criteria;

			$mailRules[$mailRule->id] = $mailRule;
			$rs->MoveNext();
		}
		
		return $mailRules;
	}
	
	/**
	 * update changed fields on a mail rule
	 *
	 * @param integer $id
	 * @param associative array $fields
	 */
	static function updateMailRule ($id, $fields) {
		$db = DevblocksPlatform::getDatabaseService();
		
		if(!is_array($fields) || empty($fields) || empty($id))
			return;
		
		foreach($fields as $k => $v) {
			$sets[] = sprintf("%s = %s",
				$k,
				$db->qstr($v)
			);
		}
			
		$sql = sprintf("UPDATE mail_rule SET %s WHERE id = %d",
			implode(', ', $sets),
			$id
		);
		$db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg());
	}
};

/**
 * Enter description here...
 * 
 * @addtogroup dao
 */
class DAO_Search {
	// [JAS]: [TODO] Implement Agent ID lookup
	
	/**
	 * Enter description here...
	 *
	 * @param CerberusSearchCriteria[] $params
	 * @param integer $limit
	 * @param integer $page
	 * @param string $sortBy
	 * @param boolean $sortAsc
	 * @return array
	 * [TODO]: Fold back into DAO_Ticket
	 */
	static function searchTickets($params,$limit=10,$page=0,$sortBy=null,$sortAsc=null) {
		$db = DevblocksPlatform::getDatabaseService();

		$fields = CerberusSearchFields::getFields();
		$start = min($page * $limit,1);
		
		$results = array();
		$tables = array();
		$wheres = array();
		
		// [JAS]: Search Builder
		if(is_array($params))
		foreach($params as $param) { /* @var $param CerberusSearchCriteria */
			if(!is_a($param,'CerberusSearchCriteria')) continue;
			$where = "";
			
			// [JAS]: Filter allowed columns (ignore invalid/deprecated)
			if(!isset($fields[$param->field]))
				continue;

			$db_field_name = $fields[$param->field]->db_table . '.' . $fields[$param->field]->db_column; 
			
			// [JAS]: Indexes for optimization
			$tables[$fields[$param->field]->db_table] = $fields[$param->field]->db_table;
				
			// [JAS]: Operators
			switch($param->operator) {
				case "=":
					$where = sprintf("%s = %s",
						$db_field_name,
						$db->qstr($param->value)
					);
					break;
					
				case "!=":
					$where = sprintf("%s != %s",
						$db_field_name,
						$db->qstr($param->value)
					);
					break;
				
				case "in":
					if(!is_array($param->value)) break;
					$where = sprintf("%s IN ('%s')",
						$db_field_name,
						implode("','",$param->value)
					);
					break;
					
				case "like":
//					if(!is_array($param->value)) break;
					$where = sprintf("%s LIKE %s",
						$db_field_name,
						$db->qstr(str_replace('*','%%',$param->value))
					);
					break;
					
				default:
					break;
			}
			
			if(!empty($where)) $wheres[] = $where;
		}
		
		// [JAS]: 1-based [TODO] clean up + document
		$start = ($page * $limit);
		
		$sql = sprintf("SELECT ".
			"t.id as t_id, ".
			"t.mask as t_mask, ".
			"t.subject as t_subject, ".
			"t.status as t_status, ".
			"t.priority as t_priority, ".
			"a1.email as t_first_wrote, ".
			"a2.email as t_last_wrote, ".
			"t.created_date as t_created_date, ".
			"t.updated_date as t_updated_date, ".
			"t.spam_score as t_spam_score, ".
			"t.num_tasks as t_tasks, ".
			"tm.id as tm_id, ".
			"tm.name as tm_name, ".
			"cat.id as cat_id, ".
			"cat.name as cat_name ".
			"FROM ticket t ".
			"INNER JOIN team tm ON (tm.id = t.team_id) ".
			"LEFT JOIN category cat ON (cat.id = t.category_id) ".
			"INNER JOIN address a1 ON (t.first_wrote_address_id=a1.id) ".
			"INNER JOIN address a2 ON (t.last_wrote_address_id=a2.id) ".
			
			// [JAS]: Dynamic table joins
			(isset($tables['ra']) ? "INNER JOIN requester r ON (r.ticket_id=t.id)" : " ").
			(isset($tables['ra']) ? "INNER JOIN address ra ON (ra.id=r.address_id) " : " ").
			(isset($tables['msg']) ? "INNER JOIN message msg ON (msg.ticket_id=t.id) " : " ").
			(isset($tables['ttt']) ? "INNER JOIN task ttk ON (ttk.ticket_id=t.id) INNER JOIN task_owner ttt ON (ttt.owner_type='T' AND ttk.is_completed=0 AND ttt.task_id=ttk.id) " : " ").
			(isset($tables['wtt']) ? "INNER JOIN task wtk ON (wtk.ticket_id=t.id) INNER JOIN task_owner wtt ON (wtt.owner_type='W' AND wtk.is_completed=0 AND wtt.task_id=wtk.id) " : " ").
			
			(!empty($wheres) ? sprintf("WHERE %s ",implode(' AND ',$wheres)) : "").
//			"GROUP BY t_id ".
			(!empty($sortBy) ? sprintf("ORDER BY %s %s",$sortBy,($sortAsc || is_null($sortAsc))?"ASC":"DESC") : "")
		);
		$rs = $db->SelectLimit($sql,$limit,$start) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
		
		while(!$rs->EOF) {
			// [JAS]: [TODO] This needs to change to an intermediary search row object.
			$result = array();
			foreach($rs->fields as $f => $v) {
				$result[$f] = $v;
			}
			$ticket_id = intval($rs->fields[CerberusSearchFields::TICKET_ID]);
			$results[$ticket_id] = $result;
			$rs->MoveNext();
		}

		// [JAS]: Count all
		$rs = $db->Execute($sql);
		$total = $rs->RecordCount();
		
		return array($results,$total);
	}	
	
	/**
	 * Enter description here...
	 *
	 * @param CerberusSearchCriteria[] $params
	 * @param integer $limit
	 * @param integer $page
	 * @param string $sortBy
	 * @param boolean $sortAsc
	 * @return array
	 * 
	 * @todo [TODO] This and the ticket search could really share a lot of the operator/field functionality 
	 */
	static function searchResources($params,$limit=10,$page=0,$sortBy=null,$sortAsc=null) {
		$db = DevblocksPlatform::getDatabaseService();

		$fields = CerberusResourceSearchFields::getFields();
		$start = min($page * $limit,1);
		
		$results = array();
		$tables = array();
		$wheres = array();
		
		// [JAS]: Search Builder
		if(is_array($params))
		foreach($params as $param) { /* @var $param CerberusSearchCriteria */
			if(!is_a($param,'CerberusSearchCriteria')) continue;
			$where = "";
			
			// [JAS]: Filter allowed columns (ignore invalid/deprecated)
			if(!isset($fields[$param->field]))
				continue;

			$db_field_name = $fields[$param->field]->db_table . '.' . $fields[$param->field]->db_column; 
			
			// [JAS]: Indexes for optimization
			$tables[$fields[$param->field]->db_table] = $fields[$param->field]->db_table;
				
			// [JAS]: Operators
			switch($param->operator) {
				case "=":
					$where = sprintf("%s = %s",
						$db_field_name,
						$db->qstr($param->value)
					);
					break;
					
				case "!=":
					$where = sprintf("%s != %s",
						$db_field_name,
						$db->qstr($param->value)
					);
					break;
				
				case "in":
					if(!is_array($param->value)) break;
					$where = sprintf("%s IN ('%s')",
						$db_field_name,
						implode("','",$param->value)
					);
					break;
					
				case "like":
//					if(!is_array($param->value)) break;
					$where = sprintf("%s LIKE %s",
						$db_field_name,
						$db->qstr(str_replace('*','%%',$param->value))
					);
					break;
					
				default:
					break;
			}
			
			if(!empty($where)) $wheres[] = $where;
		}
		
		// [JAS]: 1-based [TODO] clean up + document
		$start = ($page * $limit);
		
		$sql = sprintf("SELECT ".
			"kb.id as kb_id, ".
			"kb.title as kb_title, ".
			"kb.type as kb_type ".
			"FROM kb ".
			
			// [JAS]: Dynamic table joins
			(isset($tables['kbc']) ? "INNER JOIN kb_content kbc ON (kbc.kb_id=kb.id) " : " ").
			(isset($tables['kbcat']) ? "LEFT JOIN kb_to_category kbtc ON (kbtc.kb_id=kb.id) " : " ").
			(isset($tables['kbcat']) ? "LEFT JOIN kb_category kbcat ON (kbcat.id=kbtc.category_id) " : " ").
			
			(!empty($wheres) ? sprintf("WHERE %s ",implode(' AND ',$wheres)) : "").
//			"GROUP BY kb.id ".
			(!empty($sortBy) ? sprintf("ORDER BY %s %s",$sortBy,($sortAsc || is_null($sortAsc))?"ASC":"DESC") : "")
		);
		$rs = $db->SelectLimit($sql,$limit,$start) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
		
		while(!$rs->EOF) {
			$result = array();
			foreach($rs->fields as $f => $v) {
				$result[$f] = $v;
			}
			$id = intval($rs->fields[CerberusResourceSearchFields::KB_ID]);
			$results[$id] = $result;
			$rs->MoveNext();
		}

		// [JAS]: Count all
		$rs = $db->Execute($sql);
		$total = $rs->RecordCount();
		
		return array($results,$total);
	}	
	
	/**
	 * Enter description here...
	 *
	 * @param integer $agent_id
	 * @return CerberusDashboardView[]
	 */
	static function getSavedSearches($agent_id) {
		$db = DevblocksPlatform::getDatabaseService();
		$searches = array();
		
		$sql = sprintf("SELECT v.id, v.name, v.dashboard_id, v.type, v.view_columns, v.num_rows, v.sort_by, v.sort_asc, v.page, v.params ".
			"FROM dashboard_view v ".
			"WHERE v.type = 'S' "
		);
		$rs = $db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */

		while(!$rs->EOF) {
			$view = new CerberusDashboardView();
			$view->id = $rs->fields['id'];
			$view->name = $rs->fields['name'];
			$view->dashboard_id = intval($rs->fields['dashboard_id']);
			$view->type = $rs->fields['type'];
//			$view->agent_id = intval($rs->fields['agent_id']);
			$view->columns = unserialize($rs->fields['view_columns']);
			$view->params = unserialize($rs->fields['params']);
			$view->renderLimit = intval($rs->fields['num_rows']);
			$view->renderSortBy = $rs->fields['sort_by'];
			$view->renderSortAsc = intval($rs->fields['sort_asc']);
			$view->renderPage = intval($rs->fields['page']);
			$searches[$view->id] = $view; 
			$rs->MoveNext();
		}
		
		return $searches;
	}
};

//class DAO_TeamCategory {
//	public static function create($fields) {
//		$db = DevblocksPlatform::getDatabaseService();
//		$id = $db->GenID('generic_seq');
//		
//		$sql = sprintf("INSERT INTO team",
//			
//		);
//		$rs= $db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
//		
//	}
//	public static function update($id,$fields) {
//		
//	}
//	public static function get($id) {
//		
//	}
//	public static function getList($ids=array()) {
//		
//	}
//	public static function delete($id) {
//		// [TODO] cascade foreign key constraints	
//	}
//	public static function search() {
//		
//	}
//};

/**
 * Enter description here...
 *
 * @addtogroup dao
 */
class DAO_Workflow {
	
	/**
	 * Enter description here...
	 *
	 * @param integer $id
	 * @return CerberusTag[]
	 */
//	static function getTagsByTicket($id) {
//		$db = DevblocksPlatform::getDatabaseService();
//		$ids = array();
//		$tags = array();
//		
//		$sql = sprintf("SELECT tt.tag_id ".
//			"FROM tag_to_ticket tt ".
//			"INNER JOIN tag t ON (tt.tag_id=t.id) ".
//			"WHERE tt.ticket_id = %d ".
//			"ORDER BY t.name",
//			$id
//		);
//		$rs = $db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
//		
//		while(!$rs->EOF) {
//			$ids[] = intval($rs->fields['tag_id']);
//			$rs->MoveNext();
//		}
//		
//		if(!empty($ids)) {
//			$tags = DAO_Workflow::getTags($ids); 
//		}
//		
//		return $tags;
//	}
	
//	static function getSuggestedTags($ticket_id,$limit=10) {
//		if(empty($ticket_id)) return array();
//		
//		$db = DevblocksPlatform::getDatabaseService();
//		$tags = array();
//		
//		$msgs = DAO_Ticket::getMessagesByTicket($ticket_id);
//		if(!is_array($msgs[0])) return array();
//		
//		$msg = array_shift($msgs[0]); /* @var $msg CerberusMessage */
//		$content = $msg->getContent();
//		
//		// [JAS]: [TODO] This could get out of control fast
//		$terms = DAO_Workflow::getTagTerms();
//
//		foreach($terms as $term) {
//			if(FALSE === stristr($content,$term->term)) continue;
//			$tags[$term->tag_id] = intval($tags[$term->tag_id]) + 1;
//		}
//		
//		arsort($tags);
//		$tags = array_slice($tags,0,$limit,true);
//		
//		unset($terms);
//		
//		if(empty($tags))
//			return array();
//		
//		return DAO_Workflow::getTags(array_keys($tags));
//	}
	
//	static function setTagTerms($id, $terms) {
//		if(empty($id)) return null;
//		
//		$db = DevblocksPlatform::getDatabaseService();
//
//		// [JAS]: Clear previous terms
//		$db->Execute(sprintf("DELETE FROM tag_term WHERE tag_id = %d", $id));
//		
//		if(is_array($terms))
//		foreach($terms as $v) {
//			$term = trim($v);
//			if(empty($term)) continue;
//			$db->Replace('tag_term', array('tag_id'=>$id,'term'=>$db->qstr($term)),array('tag_id','term'),false);
//		}
//	}
//	
//	static function getTagTerms($id=null) {
//		$db = DevblocksPlatform::getDatabaseService();
//		$terms = array();
//		
//		$sql = "SELECT tag_id, term ".
//			"FROM tag_term ".
//			((!empty($id)) ? sprintf("WHERE tag_id = %d ",$id) : " "). 
//			"ORDER BY term ASC";
//		$rs = $db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
//		
//		while(!$rs->EOF) {
//			$term = new CerberusTagTerm();
//			$term->tag_id = intval($rs->fields['tag_id']);
//			$term->term = $rs->fields['term'];
//			$terms[] = $term;
//			$rs->MoveNext();
//		}
//		
//		return $terms;
//	}
	
	// Teams
	
	/**
	 * Enter description here...
	 *
	 * @param integer $id
	 * @return CerberusTeam
	 */
	static function getTeam($id) {
		$teams = DAO_Workflow::getTeams(array($id));
		
		if(isset($teams[$id]))
			return $teams[$id];
			
		return null;
	}
	
	/**
	 * Enter description here...
	 *
	 * @param array $ids
	 * @return CerberusTeam[]
	 */
	static function getTeams($ids=array()) {
		if(!is_array($ids)) $ids = array($ids);
		$db = DevblocksPlatform::getDatabaseService();

		$teams = array();
		
		$sql = sprintf("SELECT t.id , t.name ".
			"FROM team t ".
			((!empty($ids)) ? sprintf("WHERE t.id IN (%s) ",implode(',',$ids)) : " ").
			"ORDER BY t.name ASC"
		);
		$rs = $db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
		while(!$rs->EOF) {
			$team = new CerberusTeam();
			$team->id = intval($rs->fields['id']);
			$team->name = $rs->fields['name'];
			$teams[$team->id] = $team;
			$rs->MoveNext();
		}
		
		return $teams;
	}

	/**
	 * Enter description here...
	 *
	 * @param string $name
	 * @return integer
	 */
	static function createTeam($name) {
		if(empty($name))
			return;
		
		$db = DevblocksPlatform::getDatabaseService();
		$newId = $db->GenID('generic_seq');
		
		$sql = sprintf("INSERT INTO team (id, name) VALUES (%d,%s)",
			$newId,
			$db->qstr($name)
		);
		$db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
		
		return $newId;
	}

	/**
	 * Enter description here...
	 *
	 * @param integer $id
	 * @param array $fields
	 */
	static function updateTeam($id, $fields) {
		$db = DevblocksPlatform::getDatabaseService();
		$sets = array();
		
		if(!is_array($fields) || empty($fields) || empty($id))
			return;
		
		foreach($fields as $k => $v) {
			$sets[] = sprintf("%s = %s",
				$k,
				$db->qstr($v)
			);
		}
			
		$sql = sprintf("UPDATE team SET %s WHERE id = %d",
			implode(', ', $sets),
			$id
		);
		$db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
		
	}
	
	/**
	 * Enter description here...
	 *
	 * @param integer $id
	 */
	static function deleteTeam($id) {
		if(empty($id)) return;
		$db = DevblocksPlatform::getDatabaseService();
		
		$sql = sprintf("DELETE FROM team WHERE id = %d",
			$id
		);
		$db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
		
		$sql = sprintf("DELETE FROM worker_to_team WHERE team_id = %d",
			$id
		);
		$db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
	}
	
//	static function setTeamMailboxes($team_id, $mailbox_ids) {
//		if(!is_array($mailbox_ids)) $mailbox_ids = array($mailbox_ids);
//		if(empty($team_id)) return;
//		$db = DevblocksPlatform::getDatabaseService();
//		
//		$sql = sprintf("DELETE FROM mailbox_to_team WHERE team_id = %d",
//			$team_id
//		);
//		$db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
//		
//		foreach($mailbox_ids as $mailbox_id) {
//			$sql = sprintf("INSERT INTO mailbox_to_team (mailbox_id, team_id, is_routed) ".
//				"VALUES (%d,%d,%d)",
//				$mailbox_id,
//				$team_id,
//				1
//			);
//			$db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
//		}
//	}
	
//	static function getTeamMailboxes($team_id, $with_counts = false) {
//		if(empty($team_id)) return;
//		$db = DevblocksPlatform::getDatabaseService();
//		$ids = array();
//		
//		$sql = sprintf("SELECT mt.mailbox_id FROM mailbox_to_team mt WHERE mt.team_id = %d",
//			$team_id
//		);
//		$rs = $db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
//		
//		while(!$rs->EOF) {
//			$ids[] = intval($rs->fields['mailbox_id']);
//			$rs->MoveNext();
//		}
//		
//		if(empty($ids))
//			return array();
//		
//		return DAO_Mail::getMailboxes($ids, $with_counts);
//	}
	
	static function setTeamWorkers($team_id, $agent_ids) {
		if(!is_array($agent_ids)) $agent_ids = array($agent_ids);
		if(empty($team_id)) return;
		$db = DevblocksPlatform::getDatabaseService();

		$sql = sprintf("DELETE FROM worker_to_team WHERE team_id = %d",
			$team_id
		);
		$db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
		
		foreach($agent_ids as $agent_id) {
			$sql = sprintf("INSERT INTO worker_to_team (agent_id, team_id) ".
				"VALUES (%d,%d)",
				$agent_id,
				$team_id
			);
			$db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
		}
	}
	
	static function getTeamWorkers($team_id) {
		if(empty($team_id)) return;
		$db = DevblocksPlatform::getDatabaseService();
		$ids = array();
		
		$sql = sprintf("SELECT wt.agent_id FROM worker_to_team wt WHERE wt.team_id = %d",
			$team_id
		);
		$rs = $db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
		
		while(!$rs->EOF) {
			$ids[] = intval($rs->fields['agent_id']);
			$rs->MoveNext();
		}
		
		if(empty($ids))
			return array();
		
		return DAO_Worker::getList($ids);
	}
	
}

class DAO_Task extends DevblocksORMHelper {
	const ID = 'id';
	const TITLE = 'title';
	const TICKET_ID = 'ticket_id';
	const COMPLETED = 'is_completed';
	const DUE_DATE = 'due_date';
	const CONTENT = 'content';
	
	static private function _cacheTicketTasks($ticket_id) {
		$db = DevblocksPlatform::getDatabaseService();
		if(empty($ticket_id)) return;
		
		$sql = sprintf("SELECT count(*) FROM task WHERE ticket_id = %d AND is_completed = 0",
			$ticket_id
		);
		$count = $db->getOne($sql);
		
		DAO_Ticket::updateTicket($ticket_id, array(
			DAO_Ticket::NUM_TASKS => $count
		));
	}
	
	static function create($fields) {
		$db = DevblocksPlatform::getDatabaseService();
		$id = $db->GenID('task_seq');
		
		$sql = sprintf("INSERT INTO task (id,ticket_id,title,due_date,is_completed,content) ".
			"VALUES (%d,0,'',0,0,'') ",
			$id
		);
		$rs = $db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
		
		self::update($id, $fields);
		
		return $id;
	}
	
	static function update($id, $fields) {
		self::_update($id,'task',$fields);
		
		// Recache ticket num_tasks
		$task = DAO_Task::get($id);
		if(!empty($task->ticket_id)) self::_cacheTicketTasks($task->ticket_id);
	}
	
	static function delete($id) {
		$db = DevblocksPlatform::getDatabaseService();
		
		$task = DAO_Task::get($id);
		
		$sql = sprintf("DELETE FROM task WHERE id = %d", $id);
		$rs = $db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
		
		$sql = sprintf("DELETE FROM task_owner WHERE task_id = %d", $id);
		$rs = $db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
		
		// recache ticket num_tasks
		if(!empty($task->ticket_id)) self::_cacheTicketTasks($task->ticket_id);
	}
	
	static function get($id) {
		$tasks = self::getList(array($id));
		if(isset($tasks[$id]))
			return $tasks[$id];
		return NULL;
	}
	
	static function getList($ids=array()) {
		$db = DevblocksPlatform::getDatabaseService();
		if(!is_array($ids)) $ids = array($ids);
		
		$sql = "SELECT tk.id, tk.title, tk.ticket_id, tk.due_date, tk.is_completed ".
			"FROM task tk ".
			(!empty($ids) ? sprintf("WHERE tk.id IN (%s) ", implode(',', $ids)) : " ").
			"ORDER BY tk.due_date ASC "
		;
		$rs = $db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
		
		$tasks = array();
		
		while(!$rs->EOF) {
			$task = new Model_CerberusTask();
			$task->id = intval($rs->Fields('id'));
			$task->ticket_id = intval($rs->Fields('ticket_id'));
			$task->title = $rs->Fields('title');
			$task->due_date = $rs->Fields('due_date');
			$task->is_completed = intval($rs->Fields('is_completed'));
			$tasks[$task->id] = $task;
			$rs->MoveNext();
		}
		
		return $tasks;
	}
	
	// [TODO] Should be a searchDAO thing later
	static function getByTicket($ticket_id) {
		$db = DevblocksPlatform::getDatabaseService();
		
		$sql = sprintf("SELECT tk.id ".
			"FROM task tk ".
			"WHERE tk.ticket_id = %d",
			$ticket_id
		);
		$rs = $db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
		
		$ids = array();
		
		while(!$rs->EOF) {
			$ids[] = intval($rs->Fields('id'));
			$rs->MoveNext();
		}
		
		if(empty($ids))
			return array();
			
		return self::getList($ids);
	}
	
	// [TODO] For multiple IDS at once?
	static function getContent($id) {
		$db = DevblocksPlatform::getDatabaseService();
		
		$sql = sprintf("SELECT tk.content FROM task AS tk WHERE tk.id = %d",
			$id
		);
		return $db->GetOne($sql);// or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
	}
	
	static function getOwners($ids=array()) {
		$db = DevblocksPlatform::getDatabaseService();
		
		if(empty($ids)) return array();
		
		// [JAS]: [TODO] These should come from a cache
		$teams = DAO_Workflow::getTeams();
		$workers = DAO_Worker::getList();
		
		$sql = sprintf("SELECT tko.task_id, tko.owner_type, tko.owner_id ".
			"FROM task_owner AS tko ".
			"WHERE tko.task_id IN (%s)",
			implode(',', $ids)
		);
		$rs = $db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
		
		$owners = array();
		
		while(!$rs->EOF) {
			$id = intval($rs->fields['task_id']);
			$type = $rs->fields['owner_type'];
			$owner_id = intval($rs->fields['owner_id']);
			
			if(!isset($owners[$id]))
				$owners[$id] = new Model_CerberusTaskOwners();
			
			$owner =& $owners[$id]; /* @var $owners Model_CerberusTaskOwners */

			if($type == Enum_CerberusTaskOwnerType::TEAM) {
				$owner->teams[$owner_id] = $teams[$owner_id];
			} else {
				$owner->workers[$owner_id] = $workers[$owner_id];
			}
			
			$rs->MoveNext();
		}
		
		return $owners;
	}
	
	function setOwners($task_id,$team_ids=array(),$worker_ids=array(),$replace=false) {
		$db = DevblocksPlatform::getDatabaseService();
		
		if($replace) {
			$sql = sprintf("DELETE FROM task_owner WHERE task_id = %d",
				$task_id
			);
			$rs = $db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
		}
		
		// Teams
		if(!empty($team_ids)) {
			if(is_array($team_ids))
			foreach($team_ids as $team_id) {
				$db->Replace(
					'task_owner',
					array('task_id' => $task_id,'owner_type' => $db->qstr(Enum_CerberusTaskOwnerType::TEAM),'owner_id' => $team_id),
					array('task_id','owner_type','owner_id'),
					false
				);
			}
		}
		
		// Workers
		if(!empty($worker_ids)) {
			if(is_array($worker_ids))
			foreach($worker_ids as $worker_id) {
				$db->Replace(
					'task_owner',
					array('task_id' => $task_id,'owner_type' => $db->qstr(Enum_CerberusTaskOwnerType::WORKER),'owner_id' => $worker_id),
					array('task_id','owner_type','owner_id'),
					false
				);
			}
		}
	}
	
};

class DAO_Category extends DevblocksORMHelper {
	
	static function getTeams() {
		$categories = self::getList();
		$team_categories = array();
		
		foreach($categories as $cat) {
			$team_categories[$cat->team_id][] = $cat;
		}
		
		return $team_categories;
	}
	
	static function getList($ids=array()) {
		$db = DevblocksPlatform::getDatabaseService();
		
		$sql = "SELECT tc.id, tc.name, tc.team_id ".
			"FROM category tc ".
			"INNER JOIN team t ON (tc.team_id=t.id) ".
			(!empty($ids) ? sprintf("WHERE tc.id IN (%s) ", implode(',', $ids)) : "").
			"ORDER BY t.name ASC, tc.name ASC "
		;
		$rs = $db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
		
		$categories = array();
		
		while(!$rs->EOF) {
			$category = new CerberusCategory();
			$category->id = intval($rs->Fields('id'));
			$category->name = $rs->Fields('name');
			$category->team_id = intval($rs->Fields('team_id'));
			$categories[$category->id] = $category;
			$rs->MoveNext();
		}
		
		return $categories;
	}
	
	static function getByTeam($team_id) {
		$db = DevblocksPlatform::getDatabaseService();
		
		$sql = sprintf("SELECT tc.id ".
			"FROM category tc ".
			"WHERE tc.team_id = %d ".
			"ORDER BY tc.name ASC ",
			$team_id
		);
		$rs = $db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
		
		$ids = array();
		$categories = array();
		
		while(!$rs->EOF) {
			$ids[] = $rs->fields['id'];
			$rs->MoveNext();
		}
		
		if(!empty($ids)) {
			$categories = self::getList($ids);
		}
		
		return $categories;
	}
	
	static function create($name,$team_id) {
		$db = DevblocksPlatform::getDatabaseService();
		
		$id = $db->GenID('generic_seq');
		
		$sql = sprintf("INSERT INTO category (id,name,team_id) ".
			"VALUES (%d,%s,%d)",
			$id,
			$db->qstr($name),
			$team_id
		);
		$rs = $db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
		
		return $id;
	}
	
	static function update($id,$name) {
		$db = DevblocksPlatform::getDatabaseService();
		
		$sql = sprintf("UPDATE category SET name=%s WHERE id = %d",
			$db->qstr($name),
			$id
		);
		$rs = $db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
	}
	
	static function delete($id) {
		$db = DevblocksPlatform::getDatabaseService();
		
		$sql = sprintf("DELETE FROM category WHERE id = %d", $id);
		$db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
		
		$sql = sprintf("DELETE FROM category_to_tag WHERE category_id = %d", $id);
		$db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
	}	
};

class DAO_Mail {
	const ROUTING_ID = 'id';
	const ROUTING_PATTERN = 'pattern';
	const ROUTING_TEAM_ID = 'team_id';
	const ROUTING_POS = 'pos';
	
	/**
	 * Returns a list of all known mailboxes, sorted by name
	 *
	 * @return CerberusMailbox[]
	 */
//	static function getMailboxes($ids=array(), $with_counts = false) {
//		if(!is_array($ids)) $ids = array($ids);
//		$db = DevblocksPlatform::getDatabaseService();
//
//		$mailboxes = array();
//
//		$sql = sprintf("SELECT m.id , m.name, m.reply_address_id, m.display_name, m.close_autoresponse, m.new_autoresponse ".
//			"FROM mailbox m ".
//			((!empty($ids)) ? sprintf("WHERE m.id IN (%s) ",implode(',', $ids)) : " ").
//			"ORDER BY m.name ASC"
//		);
//		$rs = $db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
//		while(!$rs->EOF) {
//			$mailbox = new CerberusMailbox();
//			$mailbox->id = intval($rs->fields['id']);
//			$mailbox->name = $rs->fields['name'];
//			$mailbox->reply_address_id = $rs->fields['reply_address_id'];
//			$mailbox->display_name = $rs->fields['display_name'];
//			$mailbox->close_autoresponse = $rs->fields['close_autoresponse'];
//			$mailbox->new_autoresponse = $rs->fields['new_autoresponse'];
//			$mailboxes[$mailbox->id] = $mailbox;
//			$rs->MoveNext();
//		}
//		
//		// add per-mailbox counts of open tickets if requested
//		if ($with_counts === true) {
//			foreach ($mailboxes as $mailbox) {
//				$sql = sprintf("SELECT COUNT(t.id) as ticket_count ".
//					"FROM ticket t ".
//					"WHERE t.mailbox_id = %d AND t.status = 'O'",
//					$mailbox->id
//				);
//				
//				$rs = $db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
//				
//				while(!$rs->EOF) {
//					$mailbox->count = intval($rs->fields['ticket_count']);
//					$rs->MoveNext();
//				}			
//			}
//		}
//
//		return $mailboxes;
//	}
	
	/**
	 * creates a new mailbox in the database
	 *
	 * @param string $name
	 * @param integer $reply_address_id
	 * @param string $display_name
	 * @return integer
	 */
//	static function createMailbox($name, $reply_address_id, $display_name = '', $close_autoresponse = '', $new_autoresponse = '') {
//		$db = DevblocksPlatform::getDatabaseService();
//		$newId = $db->GenID('generic_seq');
//		
//		$sql = sprintf("INSERT INTO mailbox (id, name, reply_address_id, display_name, close_autoresponse, new_autoresponse) VALUES (%d,%s,%d,%s,%s,%s)",
//			$newId,
//			$db->qstr($name),
//			$reply_address_id,
//			$db->qstr($display_name),
//			$db->qstr($close_autoresponse),
//			$db->qstr($new_autoresponse)
//		);
//		$db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
//		
//		return $newId;
//	}
//	
//	static function updateMailbox($id, $fields) {
//		$db = DevblocksPlatform::getDatabaseService();
//		$sets = array();
//		
//		if(!is_array($fields) || empty($fields) || empty($id))
//			return;
//		
//		foreach($fields as $k => $v) {
//			$sets[] = sprintf("%s = %s",
//				$k,
//				$db->qstr($v)
//			);
//		}
//			
//		$sql = sprintf("UPDATE mailbox SET %s WHERE id = %d",
//			implode(', ', $sets),
//			$id
//		);
//		$db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
//		
//	}
//	
//	static function getMailbox($id) {
//		if(empty($id)) return null;
//		$mailboxes = DAO_Mail::getMailboxes(array($id));
//		
//		if(isset($mailboxes[$id]))
//			return $mailboxes[$id];
//			
//		return null;
//	}
//	
//	static function deleteMailbox($id) {
//		if(empty($id)) return;
//		
//		$db = DevblocksPlatform::getDatabaseService();
//		
//		$sql = sprintf("DELETE FROM mailbox WHERE id = %d",
//			$id
//		);
//		$db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
//		
//		$sql = sprintf("DELETE FROM mailbox_to_team WHERE mailbox_id = %d",
//			$id
//		);
//		$db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
//		
//	}
//	
//	
//	static function setMailboxTeams($mailbox_id, $team_ids) {
//		if(!is_array($team_ids)) $team_ids = array($team_ids);
//		if(empty($mailbox_id)) return;
//		$db = DevblocksPlatform::getDatabaseService();
//
//		$sql = sprintf("DELETE FROM mailbox_to_team WHERE mailbox_id = %d",
//			$mailbox_id
//		);
//		$db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
//		
//		foreach($team_ids as $team_id) {
//			$sql = sprintf("INSERT INTO mailbox_to_team (mailbox_id, team_id, is_routed) ".
//				"VALUES (%d,%d,%d)",
//				$mailbox_id,
//				$team_id,
//				1
//			);
//			$db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
//		}
//	}
//	
//	static function getMailboxTeams($mailbox_id) {
//		if(empty($mailbox_id)) return;
//		$db = DevblocksPlatform::getDatabaseService();
//		$ids = array();
//		
//		$sql = sprintf("SELECT mt.team_id FROM mailbox_to_team mt WHERE mt.mailbox_id = %d",
//			$mailbox_id
//		);
//		$rs = $db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
//		
//		while(!$rs->EOF) {
//			$ids[] = intval($rs->fields['team_id']);
//			$rs->MoveNext();
//		}
//		
//		if(empty($ids))
//			return array();
//			
//		return DAO_Workflow::getTeams($ids);
//	}
	
	static function getMailboxRouting() {
		$db = DevblocksPlatform::getDatabaseService();
		$routing = array();
		
		$sql = "SELECT mr.id, mr.pattern, mr.team_id, mr.pos ".
			"FROM mail_routing mr ".
			"ORDER BY mr.pos ";
		$rs = $db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
		
		while(!$rs->EOF) {
			$route = new Model_MailRoute();
			$route->id = intval($rs->fields['id']);
			$route->pattern = $rs->fields['pattern'];
			$route->team_id = intval($rs->fields['team_id']);
			$route->pos = intval($rs->Fields('pos'));
			$routing[$route->id] = $route;
			$rs->MoveNext();
		}
		
		return $routing;
	}
	
	static function createMailboxRouting($fields) {
		$db = DevblocksPlatform::getDatabaseService();
		
		$id = $db->GenID('generic_seq');
		
		// Move everything down one position in priority
		$sql = "UPDATE mail_routing SET pos=pos+1";
		$db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
		
		// Insert at top
		$sql = sprintf("INSERT INTO mail_routing (id,pattern,team_id,pos) ".
			"VALUES (%d,%s,%d,%d)",
			$id,
			$db->qstr(''),
			0,
			0
		);
		$rs = $db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
		
		self::updateMailboxRouting($id, $fields);
		
		return $id;
	}
	
	static function updateMailboxRouting($id, $fields) {
		$db = DevblocksPlatform::getDatabaseService();
		$sets = array();
		
		if(!is_array($fields) || empty($fields) || empty($id))
			return;
		
		foreach($fields as $k => $v) {
			$sets[] = sprintf("%s = %s",
				$k,
				$db->qstr($v)
			);
		}
			
		$sql = sprintf("UPDATE mail_routing SET %s WHERE id = %d",
			implode(', ', $sets),
			$id
		);
		$db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
	}
	
	static function deleteMailboxRouting($id) {
		$db = DevblocksPlatform::getDatabaseService();
		if(empty($id)) return;
		
		$sql = sprintf("DELETE FROM mail_routing WHERE id = %d",
			$id
		);
		$rs = $db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
	}
	
	static function searchAddresses($query, $limit=10) {
		$db = DevblocksPlatform::getDatabaseService();
		if(empty($query)) return null;
		
		$sql = sprintf("SELECT a.id FROM address a WHERE a.email LIKE '%s%%' LIMIT 0,%d",
			$query,
			$limit
		);
		$rs = $db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
		
		$ids = array();
		
		while(!$rs->EOF) {
			$ids[] = intval($rs->fields['id']);
			$rs->MoveNext();
		}
		
		if(empty($ids))
			return array();
			
		return DAO_Contact::getAddresses($ids);
	}
	
//	static function sendAutoresponse($ticket_id, $type) {
//		$mailMgr = DevblocksPlatform::getMailService();
//		$ticket = DAO_Ticket::getTicket($ticket_id);  /* @var $ticket CerberusTicket */
//		$mailbox = DAO_Mail::getMailbox($ticket->mailbox_id);  /* @var $mailbox CerberusMailbox */
//		
//		$body = '';
//		switch ($type) {
//			case 'new':
//				$body = DAO_Mail::getTokenizedText($ticket_id, $mailbox->new_autoresponse);
//				break;
//			case 'closed':
//				$body = DAO_Mail::getTokenizedText($ticket_id, $mailbox->close_autoresponse);
//				break;
//		}
//		if (0 == strcmp($body, '')) return 0; // if there's no body, we must not need to send an autoresponse.
//		
//		$headers = DAO_Mail::getHeaders(CerberusMessageType::AUTORESPONSE, $ticket_id);
//		
//		$mail_result =& $mailMgr->send('mail.webgroupmedia.com', $headers['x-rcpt'], $headers, $body); // DDH: TODO: this needs to pull the servername from a config, not hardcoded.
//		if ($mail_result !== true) die("Error message was: " . $mail_result->getMessage());
//	}
	
	static function getTokenizedText($ticket_id, $source_text) {
		// TODO: actually implement this function...
		return $source_text;
	}
	
	static function getHeaders($type, $ticket_id = 0) {
		// variable loading
		@$id		= DevblocksPlatform::importGPC($_REQUEST['id']); // message id
		@$to		= DevblocksPlatform::importGPC($_REQUEST['to']);
		@$cc		= DevblocksPlatform::importGPC($_REQUEST['cc']);
		@$bcc		= DevblocksPlatform::importGPC($_REQUEST['bcc']);
		
		// object loading
		if (!empty($id)) {
			$message	= DAO_Ticket::getMessage($id);
			if ($ticket_id == 0)
				$ticket_id	= $message->ticket_id;
		} else {
			$messages = DAO_Ticket::getMessagesByTicket($ticket_id);
			if ($messages[1] > 0) $message = $messages[0][0];
		}
		$ticket		= DAO_Ticket::getTicket($ticket_id);						/* @var $ticket CerberusTicket */
		$mailbox	= DAO_Mail::getMailbox($ticket->mailbox_id);				/* @var $mailbox CerberusMailbox */
		$address	= DAO_Contact::getAddress($mailbox->reply_address_id);	/* @var $address CerberusAddress */
		$requesters	= DAO_Ticket::getRequestersByTicket($ticket_id);			/* @var $requesters CerberusRequester[] */
		
		// requester address parsing - needs to vary based on type
		$sTo = '';
		$sRCPT = '';
		if ($type == CerberusMessageType::EMAIL
		||	$type == CerberusMessageType::AUTORESPONSE ) {
			foreach ($requesters as $requester) {
				if (!empty($sTo)) $sTo .= ', ';
				if (!empty($sRCPT)) $sRCPT .= ', ';
				if (!empty($requester->personal)) $sTo .= $requester->personal . ' ';
				$sTo .= '<' . $requester->email . '>';
				$sRCPT .= $requester->email;
			}
		} else {
			$sTo = $to;
			$sRCPT = $to;
		}

		// header setup: varies based on type of response - BREAK statements intentionally left out!
		$headers = array();
		switch ($type) {
			case CerberusMessageType::FORWARD :
			case CerberusMessageType::EMAIL :
				$headers['cc']			= $cc;
				$headers['bcc']			= $bcc;
			case CerberusMessageType::AUTORESPONSE :
				$headers['to']			= $sTo;
				$headers['x-rcpt']		= $sRCPT;
			case CerberusMessageType::COMMENT :
				// TODO: pull info from mailbox instead of hard-coding it.  (display name cannot be just a personal on a mailbox address...)
				// TODO: differentiate between mailbox from as part of email/forward and agent from as part of comment (may not be necessary, depends on ticket display)
				$headers['from']		= (!empty($mailbox->display_name)) ? '"' . $mailbox->display_name . '" <' . $address->email . '>' : $address->email ;
				$headers['date']		= gmdate(r);
				$headers['message-id']	= CerberusApplication::generateMessageId();
				$headers['subject']		= $ticket->subject;
				$headers['references']	= (!empty($message)) ? $message->headers['message-id'] : '' ;
				$headers['in-reply-to']	= (!empty($message)) ? $message->headers['message-id'] : '' ;
		}
		
		return $headers;
	}
		
	// Pop3 Accounts
	
	// [TODO] Allow custom ports
	static function createPop3Account($nickname,$host,$username,$password) {
		if(empty($nickname) || empty($host) || empty($username) || empty($password)) 
			return null;
			
		$db = DevblocksPlatform::getDatabaseService();
		$newId = $db->GenID('generic_seq');
		
		$sql = sprintf("INSERT INTO pop3_account (id, nickname, host, username, password) ".
			"VALUES (%d,%s,%s,%s,%s)",
			$newId,
			$db->qstr($nickname),
			$db->qstr($host),
			$db->qstr($username),
			$db->qstr($password)
		);
		$db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
		
		return $newId;
	}
	
	static function getPop3Accounts($ids=array()) {
		if(!is_array($ids)) $ids = array($ids);
		$db = DevblocksPlatform::getDatabaseService();
		$pop3accounts = array();
		
		$sql = "SELECT id, nickname, host, username, password ".
			"FROM pop3_account ".
			((!empty($ids) ? sprintf("WHERE id IN (%s)", implode(',', $ids)) : " ").
			"ORDER BY nickname "
		);
		$rs = $db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
		
		while(!$rs->EOF) {
			$pop3 = new CerberusPop3Account();
			$pop3->id = intval($rs->fields['id']);
			$pop3->nickname = $rs->fields['nickname'];
			$pop3->host = $rs->fields['host'];
			$pop3->username = $rs->fields['username'];
			$pop3->password = $rs->fields['password'];
			$pop3accounts[$pop3->id] = $pop3;
			$rs->MoveNext();
		}
		
		return $pop3accounts;		
	}
	
	/**
	 * Enter description here...
	 *
	 * @param integer $id
	 * @return CerberusPop3Account
	 */
	static function getPop3Account($id) {
		$accounts = DAO_Mail::getPop3Accounts(array($id));
		
		if(isset($accounts[$id]))
			return $accounts[$id];
			
		return null;
	}
	
	static function updatePop3Account($id, $fields) {
		$db = DevblocksPlatform::getDatabaseService();
		$sets = array();
		
		if(!is_array($fields) || empty($fields) || empty($id))
			return;
		
		foreach($fields as $k => $v) {
			$sets[] = sprintf("%s = %s",
				$k,
				$db->qstr($v)
			);
		}
			
		$sql = sprintf("UPDATE pop3_account SET %s WHERE id = %d",
			implode(', ', $sets),
			$id
		);
		$db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
	}
	
	static function deletePop3Account($id) {
		if(empty($id))
			return;
			
		$db = DevblocksPlatform::getDatabaseService();
		
		$sql = sprintf("DELETE FROM pop3_account WHERE id = %d",
			$id			
		);
		
		$db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
	}
};

class DAO_Kb {
	
	/**
	 * @return integer
	 */
	static function createCategory($name, $parent_id=0) {
		if(empty($name)) return null;
		
		$db = DevblocksPlatform::getDatabaseService();
		$id = $db->GenID('generic_seq');
		
		$sql = sprintf("INSERT INTO kb_category (id,name,parent_id) ".
			"VALUES (%d,%s,%d)",
			$id,
			$db->qstr($name),
			$parent_id
		);
		
		$db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
		
		return $id;
	}
	
	static function getCategory($id) {
		$categories = DAO_Kb::getCategories(array($id));
		
		if(isset($categories[$id]))
			return $categories[$id];
			
		return null;
	}
	
	static function getBreadcrumbTrail(&$tree,$id) {
		$trail = array();
		$p = $id;
		do {
			$trail[] =& $tree[$p];
			$p = $tree[$p]->parent_id; 		
		} while($p >= 0);
		$trail = array_reverse($trail,true);
		return $trail;
	}
	
	/*
	 * @return array
	 */
	static private function _getCategoryResourceTotals() {
		$db = DevblocksPlatform::getDatabaseService();
		$totals = array();
		
		$sql = sprintf("SELECT kbc.category_id, count(kb.id) as hits ".
			"FROM kb ".
			"INNER JOIN kb_to_category kbc ON (kb.id=kbc.kb_id) ".
			"GROUP BY kbc.category_id"
		);
		$rs = $db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
		
		while(!$rs->EOF) {
			$id = intval($rs->fields['category_id']);
			$hits = intval($rs->fields['hits']);
			$totals[$id] = $hits;
			$rs->MoveNext();
		}
		
		return $totals;
	}
	
	static function getCategoryTree() {

		// [JAS]: Root node
		$rootNode = new CerberusKbCategory();
		$rootNode->id = 0;
		$rootNode->name = 'Top';
		$rootNode->parent_id = -1;
		
		$tree = DAO_Kb::getCategories();
		$tree[0] = $rootNode;
		
		// [JAS]: Pointer hash
		foreach($tree as $catid => $cat) { /* @var $cat CerberusKbCategory */
			if(isset($tree[$cat->parent_id]) && $cat->parent_id != $catid) {
				$children =& $tree[$cat->parent_id]->children;
				$children[$catid] =& $tree[$catid];
			}
		}

		// [JAS]: Alphabetize children
		foreach($tree as $catid => $cat) { /* @var $cat CerberusKbCategory */
			$func = create_function('$a,$b', 'return strcasecmp($a->name,$b->name);');
			uasort($cat->children, $func);
		}
		
		// [JAS]: Recursively total resources
		$totals = DAO_Kb::_getCategoryResourceTotals();
		foreach($totals as $catid => $hits) {
			$ptrid = $catid;
			do {
				$ptr =& $tree[$ptrid];
				$ptr->hits += $hits;
				$ptrid = $ptr->parent_id;
			} while($ptrid >= 0);
		}
		
		return $tree;
	}
	
	static function buildTreeMap($tree,&$map,$position=0) {
		static $level = 0;
		$node =& $tree[$position];
		
		$level++;
		
		if(is_array($node->children))
		foreach($node->children as $ck => $cv) {
			$map[$ck] = $level;
			DAO_Kb::buildTreeMap($tree,$map,$ck);
		}
		
		$level--;
	}
	
	static function getCategories($ids=array()) {
		if(!is_array($ids)) $ids = array($ids);
		
		$db = DevblocksPlatform::getDatabaseService();
		$categories = array();
		
		$sql = "SELECT kc.id, kc.name, kc.parent_id ".
			"FROM kb_category kc ".
			(!empty($ids) ? sprintf("WHERE kc.id IN (%s) ",implode(',', $ids)) : " ").
			"ORDER BY kc.id";
		$rs = $db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
		
		while(!$rs->EOF) {
			$category = new CerberusKbCategory();
			$category->id = intval($rs->fields['id']);
			$category->name = $rs->fields['name'];
			$category->parent_id = intval($rs->fields['parent_id']);
			$categories[$category->id] = $category;
			$rs->MoveNext();
		}
		
		return $categories;
	}
	
	static function updateCategory($id, $fields) {
		
	}
	
	static function deleteCategory($id) {
		if(empty($id)) return null;
		$db = DevblocksPlatform::getDatabaseService();
		$db->Execute(sprintf("DELETE FROM kb_category WHERE id = %d",$id)) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
	}
	
	static function createResource($title,$type=CerberusKbResourceTypes::ARTICLE) {
		if(empty($title)) return null;
		
		$db = DevblocksPlatform::getDatabaseService();
		$id = $db->GenID('kb_seq');
		
		$sql = sprintf("INSERT INTO (id,title,type) ".
			"VALUES (%d,%s,%s)",
			$id,
			$db->qstr($title),
			$db->qstr($type)
		);
		$db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
		
		return $id;
	}
	
	static function getResource($id) {
		if(empty($id)) return null;
		
		$resources = DAO_Kb::getResources(array($id));
		
		if(isset($resources[$id]))
			return $resources[$id];
			
		return null;
	}
	
	static function getResources($ids=array()) {
		if(!is_array($ids)) $ids = array($ids);
		
		$db = DevblocksPlatform::getDatabaseService();
		$resources = array();
		
		$sql = "SELECT kb.id, kb.title, kb.type ".
			"FROM kb ".
			((!empty($ids)) ? sprintf("WHERE kb.id IN (%s) ",implode(',',$ids)) : " ").
			"ORDER BY kb.title";
		$rs = $db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
		
		while(!$rs->EOF) {
			$resource = new CerberusKbResource();
			$resource->id = intval($rs->fields['id']);
			$resource->title = $rs->fields['title'];
			$resource->type = $rs->fields['type'];
			$resources[$resource->id] = $resource;
			$rs->MoveNext();
		}
			
		return $resources;		
	}
	
	static function updateResource($id, $fields) {
		
	}
	
	static function deleteResource($id) {
		if(empty($id)) return null;
		$db = DevblocksPlatform::getDatabaseService();
		
		$sql = sprintf("DELETE FROM kb WHERE id = %d",$id);
		$db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
		
		$sql = sprintf("DELETE FROM kb_content WHERE kb_id = %d",$id);
		$db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
		
		$sql = sprintf("DELETE FROM kb_to_category WHERE kb_id = %d",$id);
		$db->Execute($sql) or die(__CLASS__ . '('.__LINE__.')'. ':' . $db->ErrorMsg()); /* @var $rs ADORecordSet */
	}
	
	/**
	 * Enter description here...
	 *
	 * @param integer $id
	 * @param string $content
	 */
	static function setResourceContent($id, $content) {
		$db = DevblocksPlatform::getDatabaseService();
		$db->Replace('kb_content',array('kb_id'=>$id,'content'=>$db->qstr($content)),array('kb_id'),false);
	}
	
	/**
	 * Enter description here...
	 *
	 * @param integer $id
	 * @return string
	 */
	static function getResourceContent($id) {
		$content = "Content";
		return $content;
	}
	
};

?>
