<?php
class Model_TeamRoutingRule {
    public $id = 0;
    public $team_id = 0;
    public $header = '';
    public $pattern = '';
    public $pos = 0;
//    public $params = array();
    public $do_move = '';
    public $do_status = '';
    public $do_spam = '';
    
    function getPatternAsRegexp() {
		$pattern = str_replace(array('*'),'__any__', $this->pattern);
		$pattern = sprintf("/%s/i",
		    str_replace(array('__any__'),'(.*?)', preg_quote($pattern))
		);
		
//		 if(false !== @preg_match($pattern, '')) {
	    // [TODO] Test the pattern we created?

		return $pattern;
    }
}

class Model_WorkerPreference {
    public $setting = '';
    public $value = '';
};

class Model_DashboardViewAction {
	public $id = 0;
	public $dashboard_view_id = 0;
	public $name = '';
	public $worker_id = 0;
	public $params = array();
	
	/*
	 * [TODO] [JAS] This could be way more efficient by doing a single DAO_Ticket::update() 
	 * call where the DAO accepts multiple IDs for a single update, vs. a loop with 'n'.
	 */
	
	/**
	 * @param integer[] $ticket_ids
	 */
	function run($ticket_ids) {
	    $agent_id = CerberusApplication::getActiveWorker()->id;
		
//		if(is_array($ticket_ids))
//		foreach($ticket_ids as $ticket_id) {
		$fields = array();
		
		// actions
		if(is_array($this->params))
		foreach($this->params as $k => $v) {
			if(empty($v)) continue;
			
			switch($k) {
				case 'closed':
				    switch(intval($v)) {
				        case CerberusTicketStatus::OPEN:
				            $fields[DAO_Ticket::IS_CLOSED] = 0;
				            break;
				        case CerberusTicketStatus::CLOSED:
				            $fields[DAO_Ticket::IS_CLOSED] = 1;
				            break;
				        case 2:
				            $fields[DAO_Ticket::IS_CLOSED] = 1;
				            $fields[DAO_Ticket::IS_DELETED] = 1;
				            break;
				    }
					break;
				
//				case 'priority':
//					$fields[DAO_Ticket::PRIORITY] = $v;
//					break;
				
				case 'spam':
					if($v == CerberusTicketSpamTraining::NOT_SPAM) {
					    foreach($ticket_ids as $ticket_id) {
						    CerberusBayes::markTicketAsNotSpam($ticket_id);
					    }
						$fields[DAO_Ticket::SPAM_TRAINING] = $v;
						
					} elseif($v == CerberusTicketSpamTraining::SPAM) {
					    foreach($ticket_ids as $ticket_id) {
					        CerberusBayes::markTicketAsSpam($ticket_id);
                        }
						$fields[DAO_Ticket::SPAM_TRAINING] = $v;
			            $fields[DAO_Ticket::IS_CLOSED] = 1;
			            $fields[DAO_Ticket::IS_DELETED] = 1;
					}
					
					break;
				
				case 'team':
				    // [TODO] Make sure the team/bucket still exists
					list($team_id,$category_id) = CerberusApplication::translateTeamCategoryCode($v);
					$fields[DAO_Ticket::TEAM_ID] = $team_id;
					$fields[DAO_Ticket::CATEGORY_ID] = $category_id;
					break;
				
				default:
					// [TODO] Log?
					break;
			}
		}
//		}

		DAO_Ticket::updateTicket($ticket_ids, $fields);
		
		if(!empty($this->params['team'])) {
		    list($team_id,$bucket_id) = CerberusApplication::translateTeamCategoryCode($this->params['team']);
		    
		    $eventMgr = DevblocksPlatform::getEventService();
		    $eventMgr->trigger(
		        new Model_DevblocksEvent(
		            'ticket.moved', // [TODO] Const
	                array(
	                    'ticket_ids' => $ticket_ids,
	                    'team_id' => $team_id,
	                    'bucket_id' => $bucket_id,
	                )
	            )
		    );
		}
	}
};

class Model_Activity {
    public $translation_code;
    public $params;
    
    public function __construct($translation_code='activity.default',$params=array()) {
        $this->translation_code = $translation_code;
        $this->params = $params;
    }
    
    public function toString() {
        $translate = DevblocksPlatform::getTranslationService();
        return vsprintf($translate->_($this->translation_code), $this->params);
    }
}

class Model_MailRoute {
	public $id = 0;
	public $pattern = '';
	public $team_id = 0;
	public $pos = 0;
};

class CerberusVisit extends DevblocksVisit {
	private $worker;
	
	const KEY_VIEW_MANAGER = 'view_manager';
	const KEY_DASHBOARD_ID = 'cur_dashboard_id';
	const KEY_VIEW_LAST_ACTION = 'view_last_action';
	const KEY_VIEW_ACTION_LEARNER = 'view_action_learner';
	const KEY_VIEW_TIPS = 'view_tips';

	public function __construct() {
		$this->worker = null;
		$this->set(self::KEY_VIEW_MANAGER, new CerberusStaticViewManager());
	}
	
	/**
	 * @return CerberusWorker
	 */
	public function getWorker() {
		return $this->worker;
	}
	
	public function setWorker(CerberusWorker $worker=null) {
		$this->worker = $worker;
	}
	
}

class CerberusBayesWord {
	public $id = -1;
	public $word = '';
	public $spam = 0;
	public $nonspam = 0;
	public $probability = CerberusBayes::PROBABILITY_UNKNOWN;
	public $interest_rating = 0.0;
}

class CerberusWorker {
	public $id;
	public $first_name;
	public $last_name;
	public $email;
	public $title;
	public $last_activity;
	public $last_activity_date;
	
	function getTeams() {
		return DAO_Worker::getAgentTeams($this->id);
	}
	
	function getName() {
		return sprintf("%s%s%s",
			$this->first_name,
			(!empty($this->first_name) && !empty($this->last_name)) ? " " : "",
			$this->last_name
		);
	}
	
}

class CerberusDashboardViewColumn {
	public $column;
	public $name;
	
	public function CerberusDashboardViewColumn($column, $name) {
		$this->column = $column;
		$this->name = $name;
	}
}

class CerberusDashboard {
	public $id = 0;
	public $name = "";
	public $agent_id = 0;
}

class Model_TicketViewLastAction {
    // [TODO] Recycle the bulk update constants for these actions?
    const ACTION_SPAM = 'spam';
    const ACTION_CLOSE = 'close';
    const ACTION_DELETE = 'delete';
    const ACTION_MOVE = 'move';
    
    public $ticket_ids = array(); // key = ticket id, value=old value
    public $action = ''; // spam/closed/move, etc.
	public $action_params = array(); // DAO Actions Taken
};

class Model_TicketViewActionLearner {
    public $flat = array();
    public $instance_id = '';
    
    public function __construct($instance_id) {
        $this->instance_id = $instance_id;
    }
    
    // [TODO] Hash this up by view_id, since move_to is less relevant now.
    
    public function analyze($tickets, Model_TicketViewLastAction $last_action) {
        if(!is_array($tickets) || empty($last_action))
            return;
            
        // Temporary
        if($last_action->action == Model_TicketViewLastAction::ACTION_MOVE) {
	
	        $move_to = !empty($last_action->action_params['category_id']) 
	            ? 'c'.$last_action->action_params['category_id']
	            : 't'.$last_action->action_params['team_id']
	            ; 
	            
	        $address_ids = array();
	        foreach($tickets as $ticket_id => $ticket) { /* @var $ticket CerberusTicket */
	            $address_ids[] = $ticket->first_wrote_address_id;
	        }
	        
	        $addresses = DAO_Contact::getAddresses($address_ids);
	                
	        foreach($tickets as $ticket_id => $ticket) { /* @var $ticket CerberusTicket */
	            @$address = $addresses[$ticket->first_wrote_address_id];
	            $sender = ($address instanceof CerberusAddress) ? $address->email : "";
	            $domain = ($address instanceof CerberusAddress) ? substr($address->email,strrpos($address->email,'@')) : "";
	            $subject = $ticket->subject;
	
	            $sender_hash = md5('sender'.$sender);
	            $domain_hash = md5('domain'.$domain);
	            $subject_hash = md5('subject'.$subject);
	            
	            /*
	             * [JAS]: Only store a hit when it's unique.  If we used the same header to do 
	             * a different action then ignore both hits.
	             */
	            
	            if(!isset($this->flat[$sender_hash])) {
	                $this->flat[$sender_hash] = array('sender',$sender,$move_to,1);
	            } else { 
	                if($this->flat[$sender_hash][2]==$move_to) {
	                    $this->flat[$sender_hash][3]++;
	                } else { // Different action
	                    unset($this->flat[$sender_hash]);
	                }
	            }
	            
	            if(!isset($this->flat[$domain_hash])) {
	                $this->flat[$domain_hash] = array('domain',$domain,$move_to,1);
	            } else { 
	                if($this->flat[$domain_hash][2]==$move_to) {
	                    $this->flat[$domain_hash][3]++;
	                } else { // Different action
	                    unset($this->flat[$domain_hash]);
	                }
	            }
	            
	            if(!isset($this->flat[$subject_hash])) {
	                $this->flat[$subject_hash] = array('subject',$subject,$move_to,1);
	            } else { 
	                if($this->flat[$subject_hash][2]==$move_to) {
	                    $this->flat[$subject_hash][3]++;
	                } else { // Different action
	                    unset($this->flat[$subject_hash]);
	                }
	            }
	        }
	        
	        $this->purge();
        } // end if ACTION_MOVE

        $top = array();

        $max = 10;
        foreach($this->flat as $prop_hash => $props) {
            $threshold = ($props[0]=="domain") ? 2 : 2;
            if($props[3] >= $threshold) { // count above threshold
                $top[$prop_hash] = $props;
                if(--$max <= 0) 
                    break;
            }
        }
        
        $visit = CerberusApplication::getVisit(); /* @var $visit CerberusVisit */
        $view_key = CerberusVisit::KEY_VIEW_TIPS . $this->instance_id;
		$visit->set($view_key,$top);
    }
    
    public function clear($hash) {
        unset($this->flat[$hash]);
    }
    
    private function sortByCount($a,$b) {
	    if ($a[3] == $b[3]) {
	        return 0;
	    }
        return ($a[3] > $b[3]) ? -1 : 1;        
    }
    
    private function purge() {
        uasort($this->flat,array($this,'sortByCount'));
        
        // Only remember top 200 facts at a time
        if(count($this->flat) > 300) // a little buffer space so we're not always running
            $this->flat = array_slice($this->flat,0,200,true);
    }
    
};

class CerberusDashboardView {
	public $id = 0;
	public $name = "";
	public $dashboard_id = 0;
	public $type = '';
	public $view_columns = array();
	public $params = array();
	
	public $renderPage = 0;
	public $renderLimit = 10;
	public $renderSortBy = 't_subject';
	public $renderSortAsc = 1;
	
	function getTickets() {
		$tickets = DAO_Ticket::search(
			$this->params,
			$this->renderLimit,
			$this->renderPage,
			$this->renderSortBy,
			$this->renderSortAsc
		);
		return $tickets;	
	}
	
	static public function setLastAction($view_id, Model_TicketViewLastAction $last_action=null) {
	    $visit = CerberusApplication::getVisit(); /* @var $visit CerberusVisit */
	    $view_last_actions = $visit->get(CerberusVisit::KEY_VIEW_LAST_ACTION,array());
	    
	    if(!is_null($last_action) && !empty($last_action->ticket_ids)) {
	        $view_last_actions[$view_id] = $last_action;
	    } else {
	        if(isset($view_last_actions[$view_id])) {
	            unset($view_last_actions[$view_id]);
	        }
	    }
	    
        $visit->set(CerberusVisit::KEY_VIEW_LAST_ACTION,$view_last_actions);
	}
	
	/**
	 * @param string $view_id
	 * @return Model_TicketViewLastAction
	 */
	static public function getLastAction($view_id) {
	    $visit = CerberusApplication::getVisit(); /* @var $visit CerberusVisit */
        $view_last_actions = $visit->get(CerberusVisit::KEY_VIEW_LAST_ACTION,array());
        return (isset($view_last_actions[$view_id]) ? $view_last_actions[$view_id] : null);
	}
	
	static public function clearLastActions() {
	    $visit = CerberusApplication::getVisit(); /* @var $visit CerberusVisit */
	    $visit->set(CerberusVisit::KEY_VIEW_LAST_ACTION,array());
	}
	
	/**
	 * @param array
	 * @param array
	 * @return boolean
	 */
	function doBulkUpdate($filter, $data, $do, $ticket_ids=array(), $always_do_for_team_id=0) {
		$action = new Model_DashboardViewAction();
		$action->params = $do;
		$action->dashboard_view_id = $this->id;
	    
		$params = $this->params;

		// Sanitize params
	    if(!empty($filter)) {
	        $find = ($filter=='subject') ? SearchFields_Ticket::TICKET_SUBJECT : SearchFields_Ticket::SENDER_ADDRESS;
	    
	        foreach($params as $k => $v) { /* @var $v DevblocksSearchCriteria */
	            if(0 == strcasecmp($v->field, $find)) {
	                unset($params[$k]);
	                break;
	            }
	        }
	    }
		
		switch($filter) {
		    case 'subject':
		        foreach($data as $v) {
		            $new_params = $params;
		            $new_params[] = new DevblocksSearchCriteria(SearchFields_Ticket::TICKET_SUBJECT,DevblocksSearchCriteria::OPER_LIKE,$v);
//		            $count = 0;
		            
//			        do {
				        list($tickets,$count) = DAO_Ticket::search(
				            $new_params,
				            -1,
				            0
				        );
				        
	                    $ticket_ids = array_keys($tickets);
	                    
				        // Did we want to save this and repeat it in the future?
					    if($always_do_for_team_id) {
						  $fields = array(
						      DAO_TeamRoutingRule::HEADER => 'subject',
						      DAO_TeamRoutingRule::PATTERN => $v,
						      DAO_TeamRoutingRule::TEAM_ID => $always_do_for_team_id,
						      DAO_TeamRoutingRule::POS => count($ticket_ids),
						      DAO_TeamRoutingRule::DO_MOVE => $do['team'],
						      DAO_TeamRoutingRule::DO_SPAM => $do['spam'],
						      DAO_TeamRoutingRule::DO_STATUS => $do['closed'],
						  );
						  DAO_TeamRoutingRule::create($fields);
						}
	                    
                        $action->run($ticket_ids);
				        
//			        } while($count);
		        }
		        break;
		        
		    case 'sender':
		        foreach($data as $v) {
		            $new_params = $params;
		            $new_params[] = new DevblocksSearchCriteria(SearchFields_Ticket::SENDER_ADDRESS,DevblocksSearchCriteria::OPER_LIKE,$v);
//		            $count = 0;
		            
//			        do {
				        list($tickets,$count) = DAO_Ticket::search(
				            $new_params,
				            -1,
				            0
				        );
				        
	                    $ticket_ids = array_keys($tickets);
	                    
				        // Did we want to save this and repeat it in the future?
					    if($always_do_for_team_id) {
						  $fields = array(
						      DAO_TeamRoutingRule::HEADER => 'from',
						      DAO_TeamRoutingRule::PATTERN => $v,
						      DAO_TeamRoutingRule::TEAM_ID => $always_do_for_team_id,
						      DAO_TeamRoutingRule::POS => count($ticket_ids),
						      DAO_TeamRoutingRule::DO_MOVE => $do['team'],
						      DAO_TeamRoutingRule::DO_SPAM => $do['spam'],
						      DAO_TeamRoutingRule::DO_STATUS => $do['closed'],
						      );
						  DAO_TeamRoutingRule::create($fields);
						}
	                    
                        $action->run($ticket_ids);
	                    
//			        } while($count);
		        }
		        break;
		        
		    default: // none/selected
		
			    // Save shortcut?
//				if(!empty($shortcut_name)) {
//					$fields = array(
//						DAO_DashboardViewAction::$FIELD_NAME => $shortcut_name,
//						DAO_DashboardViewAction::$FIELD_VIEW_ID => 0,
//						DAO_DashboardViewAction::$FIELD_WORKER_ID => 1, // [TODO] Should be real
//						DAO_DashboardViewAction::$FIELD_PARAMS => serialize($params)
//					);
//					$action_id = DAO_DashboardViewAction::create($fields);
//				}
//				$tickets = DAO_Ticket::getTickets($ticket_ids);
				
				$action->run($ticket_ids);
		        
		        break;
		}

		if(!empty($do['team'])) {
		    list($team_id, $bucket_id) = CerberusApplication::translateTeamCategoryCode($do['team']);
		    
		    if(!empty($team_id)) {
			    $eventMgr = DevblocksPlatform::getEventService();
			    $eventMgr->trigger(
			        new Model_DevblocksEvent(
			            'ticket.moved', // [TODO] Const
		                array(
		                    'ticket_ids' => $ticket_ids,
		                    'team_id' => $team_id,
		                    'bucket_id' => $bucket_id,
		                )
		            )
			    );
		    }
		}
		
	}
};

// [JAS] This is no longer needed
class CerberusResourceSearchFields implements IDevblocksSearchFields {
	// Resource
	const KB_ID = 'kb_id';
	const KB_TITLE = 'kb_title';
	const KB_TYPE = 'kb_type';
	
	// Content
	const KB_CONTENT = 'kb_content';
	
	// Category
	const KB_CATEGORY_ID = 'kbc_id';
	
	/**
	 * @return DevblocksSearchField[]
	 */
	static function getFields() {
		return array(
			CerberusResourceSearchFields::KB_ID => new DevblocksSearchField(CerberusResourceSearchFields::KB_ID, 'kb', 'id'),
			CerberusResourceSearchFields::KB_TITLE => new DevblocksSearchField(CerberusResourceSearchFields::KB_TITLE, 'kb', 'title'),
			CerberusResourceSearchFields::KB_TYPE => new DevblocksSearchField(CerberusResourceSearchFields::KB_TYPE, 'kb', 'type'),
			
			CerberusResourceSearchFields::KB_CONTENT => new DevblocksSearchField(CerberusResourceSearchFields::KB_CONTENT, 'kbc', 'content'),
			
			CerberusResourceSearchFields::KB_CATEGORY_ID => new DevblocksSearchField(CerberusResourceSearchFields::KB_CATEGORY_ID, 'kbcat', 'id'),
		);
	}
};

class CerberusMessageType { // [TODO] Append 'Enum' to class name?
	const EMAIL = 'E';
	const FORWARD = 'F';
	const COMMENT = 'C';
	const AUTORESPONSE = 'A';
};

class CerberusTicketBits {
	const CREATED_FROM_WEB = 1;
};

class CerberusTicketStatus {
	const OPEN = 0;
	const CLOSED = 1;
	
	/**
	 * @return array 
	 */
	public static function getOptions() {
		$translate = DevblocksPlatform::getTranslationService();
		
		return array(
			self::OPEN => $translate->_('status.open'),
			self::CLOSED => $translate->_('status.closed'),
		);
	}
};

class CerberusTicketSpamTraining { // [TODO] Append 'Enum' to class name?
	const BLANK = '';
	const NOT_SPAM = 'N';
	const SPAM = 'S';
	
	public static function getOptions() {
		$translate = DevblocksPlatform::getTranslationService();
		
		return array(
			self::NOT_SPAM => $translate->_('training.not_spam'),
			self::SPAM => $translate->_('training.report_spam'),
		);
	}
};

class CerberusTicketPriority { // [TODO] Append 'Enum' to class name?
	const NONE = 0;
	const LOW = 25;
	const MODERATE = 50;
	const HIGH = 75;
	
	public static function getOptions() {
		$translate = DevblocksPlatform::getTranslationService();
		
		return array(
			self::NONE => $translate->_('priority.none'),
			self::LOW => $translate->_('priority.low'),
			self::MODERATE => $translate->_('priority.moderate'),
			self::HIGH => $translate->_('priority.high'),
		);
	}
};

// [TODO] Is this used?
class CerberusAddressBits {
	const AGENT = 1;
	const BANNED = 2;
	const QUEUE = 4;
};

class CerberusTicket {
	public $id;
	public $mask;
	public $subject;
	public $is_closed = 0;
	public $is_deleted = 0;
	public $team_id;
	public $category_id;
	public $owner_id = 0;
	public $priority;
	public $first_wrote_address_id;
	public $last_wrote_address_id;
	public $created_date;
	public $updated_date;
	public $due_date;
	public $spam_score;
	public $spam_training;
	public $interesting_words;
	public $next_action;
	
	function CerberusTicket() {}
	
	function getMessages() {
		$messages = DAO_Ticket::getMessagesByTicket($this->id);
		return $messages;
	}
	
	function getRequesters() {
		$requesters = DAO_Ticket::getRequestersByTicket($this->id);
		return $requesters;
	}
	
	/**
	 * @return CloudGlueTag[]
	 */
	function getTags() {
		$result = DAO_CloudGlue::getTagsOnContents(array($this->id), CerberusApplication::INDEX_TICKETS);
		$tags = array_shift($result);
		return $tags;
	}
	
};

class CerberusMessage {
	public $id;
	public $ticket_id;
	public $message_type;
	public $created_date;
	public $address_id;
	public $message_id;
	public $headers;
	private $content; // use getter
	
	function CerberusMessage() {}
	
	function getContent() {
		return DAO_Ticket::getMessageContent($this->id);
	}

	/**
	 * returns an array of the message's attachments
	 *
	 * @return Model_Attachment[]
	 */
	function getAttachments() {
		$attachments = DAO_Ticket::getAttachmentsByMessage($this->id);
		return $attachments;
	}

};

class CerberusAddress {
	public $id;
	public $email;
	public $personal;
	public $bitflags;
	
	function CerberusAddress() {}
};

class Model_Attachment {
	public $id;
	public $message_id;
	public $display_name;
	public $filepath;
	public $file_size = 0;
	public $mime_type = '';
	
	public function getFileContents() {
	    $file_path = APP_PATH . '/storage/attachments/';
		if (!empty($this->filepath))
	        return file_get_contents($file_path.$this->filepath,false);
	}
	
//	public function getMimeType() {
////	    [TODO] mime_content_type() or php_fileinfo.dll?
//	    
//        if(false === ($lpos = strrpos($this->filepath,'.'))) {
//            return("application/octet-stream");
//        }
//
//		$ext = strtolower(substr($this->filepath,$lpos));
//		 
//		$mimetype = array( 
//		    'bmp'=>'image/bmp',
//		    'doc'=>'application/msword', 
//		    'gif'=>'image/gif',
//		    'gz'=>'application/x-gzip-compressed',
//		    'htm'=>'text/html', 
//		    'html'=>'text/html', 
//		    'jpeg'=>'image/jpeg', 
//		    'jpg'=>'image/jpeg', 
//		    'mp3'=>'audio/x-mp3',
//		    'pdf'=>'application/pdf', 
//		    'php'=>'text/plain', 
//		    'swf'=>'application/x-shockwave-flash',
//		    'tar'=>'application/x-tar',
//		    'tgz'=>'application/x-gzip-compressed',
//		    'tif'=>'image/tiff',
//		    'tiff'=>'image/tiff',
//		    'txt'=>'text/plain', 
//		    'vsd'=>'application/vnd.visio',
//		    'vss'=>'application/vnd.visio',
//		    'vst'=>'application/vnd.visio',
//		    'vsw'=>'application/vnd.visio',
//		    'wav'=>'audio/x-wav',
//		    'xls'=>'application/vnd.ms-excel',
//		    'xml'=>'text/xml',
//		    'zip'=>'application/x-zip-compressed' 
//		    ); 
//		        
//		if(isset($mimetype[$ext])) {
//			return($mimetype[$ext]);
//		} else {
//			return("application/octet-stream");
//		}
//	}
	
};

class CerberusTeam {
	public $id;
	public $name;
	public $count;
	
	function getWorkers() {
		return DAO_Workflow::getTeamWorkers($this->id);
	}
}

class CerberusCategory {
	public $id;
	public $name;
	public $team_id;
	public $tags = array();
}

class Enum_CerberusTaskOwnerType {
	const WORKER = 'W';
	const TEAM = 'T';
};

class Model_CerberusTask {
	public $id;
	public $ticket_id;
	public $title;
	public $due_date;
	public $is_completed;
	
	/**
	 * @return string
	 */
	function getContent() {
		return DAO_Task::getContent($this->id);
	}
	
	/**
	 * @return Model_CerberusTaskOwners[]
	 */
	function getOwners() {
		$owners = DAO_Task::getOwners(array($this->id));
		return $owners[$this->id];
	}
}

class Model_CerberusTaskOwners {
	public $workers = array();
	public $teams = array();
}

class CerberusPop3Account {
	public $id;
	public $enabled=1;
	public $nickname;
	public $protocol='pop3';
	public $host;
	public $username;
	public $password;
	public $port=110;
};

//class CerberusTag {
//	public $id;
//	public $name;
//	
//	function getTerms() {
//		return DAO_Workflow::getTagTerms($this->id);
//	}
//};
//
//class CerberusTagTerm {
//	public $tag_id;
//	public $term;
//};

class CerberusKbCategory {
	public $id;
	public $name;
	public $parent_id;
	
	public $hits=0;
	public $level=0;
	public $children = array(); // ptr array
};

class CerberusKbResource {
	public $id;
	public $title;
	public $type; // CerberusKbResourceTypes
	public $categories = array();
	
	function getContent() { 
		
		return '';
	}
};

class CerberusKbResourceTypes {
	const ARTICLE = 'A';
	const URL = 'U';
};

class CerberusPatch extends DevblocksPatch {
	private $plugin_id = null;
	private $revision = null;
	private $container = null;
	
	function __construct($plugin_id, $revision, DevblocksPatchContainerExtension $container) {
		parent::__construct($plugin_id, $revision);
		$this->revision = intval($revision);
		$this->container = $container;
	}
	
	public function run() {
		if(empty($this->container) || !is_object($this->container)) {
			return FALSE;
		}
		
		// Callback
		$result = $this->container->runRevision($this->revision);
		
		if($result) {
			$this->_ran();
			return TRUE;
		} else {
			return FALSE;
		}
	}
};

class Model_Community {
    public $id = 0;
    public $name = '';
    public $url = '';
}

interface ICerberusCriterion {
	public function getValue($rfcMessage);
};

?>