<?php
/**
 * @author Jeff Standen <jeff@webgroupmedia.com> [JAS]
 */

@set_time_limit(3600);
require('../framework.config.php');
require_once(DEVBLOCKS_PATH . 'Devblocks.class.php');
require_once(APP_PATH . '/api/Application.class.php');
require_once(APP_PATH . '/install/classes.php');

require_once(DEVBLOCKS_PATH . 'api/Patch.php'); // [TODO] Temporary

// DevblocksPlatform::init() workaround 
if(!defined('DEVBLOCKS_WEBPATH')) {
	$php_self = $_SERVER["PHP_SELF"];
	$php_self = str_replace('/install','',$php_self);
	$pos = strrpos($php_self,'/');
	$php_self = substr($php_self,0,$pos) . '/';
	@define('DEVBLOCKS_WEBPATH',$php_self);
}

define('STEP_ENVIRONMENT', 1);
define('STEP_DATABASE', 2);
define('STEP_SAVE_CONFIG_FILE', 3);
define('STEP_INIT_DB', 4);
define('STEP_CONTACT', 5);
define('STEP_OUTGOING_MAIL', 6);
define('STEP_INCOMING_MAIL', 7);
define('STEP_WORKFLOW', 8);
define('STEP_CATCHALL', 9);
define('STEP_ANTISPAM', 10);
define('STEP_REGISTER', 11);
define('STEP_UPGRADE', 12);
define('STEP_FINISHED', 13);

define('TOTAL_STEPS', 13);

// Import GPC variables to determine our scope/step.
@$step = DevblocksPlatform::importGPC($_REQUEST['step'],'integer');

/*
 * [TODO] We can run some quick tests to bypass steps we've already passed
 * even when returning to the page with a NULL step.
 */
if(empty($step)) $step = STEP_ENVIRONMENT;

// Make sure the temporary directories of Devblocks are writeable.
if(!is_writeable(DEVBLOCKS_PATH . "tmp/")) {
	die(realpath(DEVBLOCKS_PATH . "tmp/") ." is not writeable by the webserver.  Please adjust permissions and reload this page.");
}

if(!is_writeable(DEVBLOCKS_PATH . "tmp/templates_c/")) {
	die(realpath(DEVBLOCKS_PATH . "tmp/templates_c/") . " is not writeable by the webserver.  Please adjust permissions and reload this page.");
}

if(!is_writeable(DEVBLOCKS_PATH . "tmp/cache/")) {
	die(realpath(DEVBLOCKS_PATH . "tmp/cache/") . " is not writeable by the webserver.  Please adjust permissions and reload this page.");
}

if(!is_writeable(APP_PATH . "/attachments/")) {
	die(realpath(APP_PATH . "/attachments/") ." is not writeable by the webserver.  Please adjust permissions and reload this page.");
}

require_once(DEVBLOCKS_PATH . 'libs/Zend.php');
//require_once(DEVBLOCKS_PATH . 'libs/Zend/Config.php');

// [JAS]: Email Validator
//require_once 'Zend/Validate/EmailAddress.php';

// [TODO] Move this to the framework init (installer blocks this at the moment)
$locale = DevblocksPlatform::getLocaleService();
$locale->setLocale('en_US');

// [JAS]: Translations
// [TODO] Should probably cache this
$translate = DevblocksPlatform::getTranslationService();
$translate->addTranslation(APP_PATH . '/install/strings.xml',$locale);
//$date = DevblocksPlatform::getDateService();
//echo sprintf($translate->_('installer.today'),$date->get(Zend_Date::WEEKDAY));

// Get a reference to the template system and configure it
$tpl = DevblocksPlatform::getTemplateService();
$tpl->template_dir = APP_PATH . '/install/templates';
$tpl->caching = 0;

$tpl->assign('translate', $translate);

$tpl->assign('step', $step);

switch($step) {
	// [TODO] Check server + php environment (extensions + php.ini)
	default:
	case STEP_ENVIRONMENT:
		$results = array();
		$fails = 0;
		
		// PHP Version
		if(version_compare(PHP_VERSION,"5.0.0") >=0) {
			$results['php_version'] = PHP_VERSION;
		} else {
			$results['php_version'] = false;
			$fails++;
		}
		
		// File Uploads
		$ini_file_uploads = ini_get("file_uploads");
		if($ini_file_uploads == 1 || strcasecmp($ini_file_uploads,"on")==0) {
			$results['file_uploads'] = true;
		} else {
			$results['file_uploads'] = false;
			$fails++;
		}
		
		// File Upload Temporary Directory
		$ini_upload_tmp_dir = ini_get("upload_tmp_dir");
		if(!empty($ini_upload_tmp_dir)) {
			$results['upload_tmp_dir'] = true;
		} else {
			$results['upload_tmp_dir'] = false;
			//$fails++; // Not fatal
		}

		// Extension: Sessions
		if(extension_loaded("session")) {
			$results['ext_session'] = true;
		} else {
			$results['ext_session'] = false;
			$fails++;
		}
		
		// Extension: PCRE
		if(extension_loaded("pcre")) {
			$results['ext_pcre'] = true;
		} else {
			$results['ext_pcre'] = false;
			$fails++;
		}

		// Extension: IMAP
		if(extension_loaded("imap")) {
			$results['ext_imap'] = true;
		} else {
			$results['ext_imap'] = false;
			$fails++;
		}
		
		// Extension: SimpleXML
		if(extension_loaded("simplexml")) {
			$results['ext_simplexml'] = true;
		} else {
			$results['ext_simplexml'] = false;
			$fails++;
		}
		
		$tpl->assign('fails', $fails);
		$tpl->assign('results', $results);
		$tpl->assign('template', 'steps/step_environment.tpl.php');
		
		break;
		
	// Configure and test the database connection
	// [TODO] This should also patch in app_id + revision order
	// [TODO] This should remind the user to make a backup (and refer to a wiki article how)
	case STEP_DATABASE:
		// Import scope (if post)
		@$db_driver = DevblocksPlatform::importGPC($_POST['db_driver'],'string');
		@$db_server = DevblocksPlatform::importGPC($_POST['db_server'],'string');
		@$db_name = DevblocksPlatform::importGPC($_POST['db_name'],'string');
		@$db_user = DevblocksPlatform::importGPC($_POST['db_user'],'string');
		@$db_pass = DevblocksPlatform::importGPC($_POST['db_pass'],'string');

		@$db = DevblocksPlatform::getDatabaseService();
		if(@$db->IsConnected()) {
			// If we've been to this step, skip past framework.config.php
			$tpl->assign('step', STEP_INIT_DB);
			$tpl->display('steps/redirect.tpl.php');
			exit;
		}
		unset($db);
		
		// [JAS]: Detect available database drivers
		
		$drivers = array();
		
		if(extension_loaded('mysql')) {
			$drivers['mysql'] = 'MySQL 3.23/4.x/5.x';
		}
		
		if(extension_loaded('mysqli')) {
			$drivers['mysqli'] = 'MySQLi 4.x/5.x';
		}
		
		if(extension_loaded('pgsql')) {
			$drivers['postgres8'] = 'PostgreSQL 8.x';
			$drivers['postgres7'] = 'PostgreSQL 7.x';
			$drivers['postgres64'] = 'PostgreSQL 6.4';
		}

		if(extension_loaded('mssql')) {
			$drivers['mssql'] = 'Microsoft SQL Server 7.x/2000/2005';
		}
		
		if(extension_loaded('oci8')) {
			$drivers['oci8'] = 'Oracle 8/9';
		}
		
		$tpl->assign('drivers', $drivers);
		
		if(!empty($db_driver) && !empty($db_server) && !empty($db_name) && !empty($db_user)) {
			// Test the given settings, bypass platform initially
			include_once(DEVBLOCKS_PATH . "libs/adodb/adodb.inc.php");
			$ADODB_CACHE_DIR = APP_PATH . "/tmp/cache";
			@$db =& ADONewConnection($db_driver);
			@$db->Connect($db_server, $db_user, $db_pass, $db_name);

			$tpl->assign('db_driver', $db_driver);
			$tpl->assign('db_server', $db_server);
			$tpl->assign('db_name', $db_name);
			$tpl->assign('db_user', $db_user);
			$tpl->assign('db_pass', $db_pass);
			
			// If passed, write config file and continue
			if($db->IsConnected()) {
				// [TODO] Write database settings to framework.config.php
				$result = CerberusInstaller::saveFrameworkConfig($db_driver, $db_server, $db_name, $db_user, $db_pass);
				
				// [JAS]: If we didn't save directly to the config file, user action required
				if(0 != strcasecmp($result,'config')) {
					$tpl->assign('result', $result);
					$tpl->assign('config_path', realpath(APP_PATH . "/framework.config.php"));
					$tpl->assign('template', 'steps/step_config_file.tpl.php');
					
				} else { // skip the config writing step
					$tpl->assign('step', STEP_INIT_DB);
					$tpl->display('steps/redirect.tpl.php');
					exit;
				}
				
			} else { // If failed, re-enter
				$tpl->assign('failed', true);
				$tpl->assign('template', 'steps/step_database.tpl.php');
			}
			
		} else {
			$tpl->assign('db_server', 'localhost');
			$tpl->assign('template', 'steps/step_database.tpl.php');
		}
		break;
		
	// [JAS]: If we didn't save directly to the config file, user action required		
	case STEP_SAVE_CONFIG_FILE:
		@$db_driver = DevblocksPlatform::importGPC($_POST['db_driver'],'string');
		@$db_server = DevblocksPlatform::importGPC($_POST['db_server'],'string');
		@$db_name = DevblocksPlatform::importGPC($_POST['db_name'],'string');
		@$db_user = DevblocksPlatform::importGPC($_POST['db_user'],'string');
		@$db_pass = DevblocksPlatform::importGPC($_POST['db_pass'],'string');
		@$result = DevblocksPlatform::importGPC($_POST['result'],'string');
		
		// Check to make sure our constants match our input
		if(
			0 == strcasecmp($db_driver,APP_DB_DRIVER) &&
			0 == strcasecmp($db_server,APP_DB_HOST) &&
			0 == strcasecmp($db_name,APP_DB_DATABASE) &&
			0 == strcasecmp($db_user,APP_DB_USER) &&
			0 == strcasecmp($db_pass,APP_DB_PASS)
		) { // we did it!
			$tpl->assign('step', STEP_INIT_DB);
			$tpl->display('steps/redirect.tpl.php');
			exit;
			
		} else { // oops!
			$tpl->assign('db_driver', $db_driver);
			$tpl->assign('db_server', $db_server);
			$tpl->assign('db_name', $db_name);
			$tpl->assign('db_user', $db_user);
			$tpl->assign('db_pass', $db_pass);
			$tpl->assign('failed', true);
			$tpl->assign('result', $result);
			$tpl->assign('config_path', realpath(APP_PATH . "/framework.config.php"));
			
			$tpl->assign('template', 'steps/step_config_file.tpl.php');
		}
		
		break;

	// Initialize the database
	case STEP_INIT_DB:
		// [TODO] Add current user to patcher/upgrade authorized IPs
		
		if(CerberusInstaller::isDatabaseEmpty()) { // install
			$patchMgr = DevblocksPlatform::getPatchService();
			
			// [JAS]: Run our overloaded container for the platform
			$patchMgr->registerPatchContainer(new PlatformPatchContainer());
			
			// Clean script
			if(!$patchMgr->run()) {
				// [TODO] Show more info on the error
				$tpl->assign('template', 'steps/step_init_db.tpl.php');
				
			} else { // success
				// Read in plugin information from the filesystem to the database
				DevblocksPlatform::readPlugins();
				
				$plugins = DevblocksPlatform::getPluginRegistry();
				
				// Tailor which plugins are enabled by default
				if(is_array($plugins))
				foreach($plugins as $plugin_manifest) { /* @var $plugin_manifest DevblocksPluginManifest */
					switch ($plugin_manifest->id) {
						case "cerberusweb.core":
						case "cerberusweb.simulator":
							$plugin_manifest->setEnabled(true);
							break;
						
						default:
							$plugin_manifest->setEnabled(false);
							break;
					}
				}
				
				DevblocksPlatform::clearCache();
				
				// Run enabled plugin patches
				$patches = DevblocksPlatform::getExtensions("devblocks.patch.container");
				
				if(is_array($patches))
				foreach($patches as $patch_manifest) { /* @var $patch_manifest DevblocksExtensionManifest */ 
					 $container = $patch_manifest->createInstance(); /* @var $container DevblocksPatchContainerExtension */
					 $patchMgr->registerPatchContainer($container);
				}
				
				if(!$patchMgr->run()) { // fail
					$tpl->assign('template', 'steps/step_init_db.tpl.php');
					
				} else {
					// success
					$tpl->assign('step', STEP_CONTACT);
					$tpl->display('steps/redirect.tpl.php');
					exit;
				}
			
				// [TODO] Verify the database
			}
			
			
		} else { // upgrade / patch
			/*
			 * [TODO] We should probably only forward to upgrade when we know 
			 * the proper tables were installed.  We may be repeating an install 
			 * request where the clean DB failed.
			 */
			$tpl->assign('step', STEP_UPGRADE);
			$tpl->display('steps/redirect.tpl.php');
			exit;
		}
			
		break;
		

	// Personalize system information (title, timezone, language)
	case STEP_CONTACT:
		$settings = CerberusSettings::getInstance();
		
		@$default_reply_from = DevblocksPlatform::importGPC($_POST['default_reply_from'],'string',$settings->get(CerberusSettings::DEFAULT_REPLY_FROM));
		@$default_reply_personal = DevblocksPlatform::importGPC($_POST['default_reply_personal'],'string',$settings->get(CerberusSettings::DEFAULT_REPLY_PERSONAL));
		@$helpdesk_title = DevblocksPlatform::importGPC($_POST['helpdesk_title'],'string',$settings->get(CerberusSettings::HELPDESK_TITLE));
		@$form_submit = DevblocksPlatform::importGPC($_POST['form_submit'],'integer');
		
		if(!empty($form_submit) && !empty($default_reply_from)) {
			
			if(!empty($default_reply_from)) {
				$settings->set(CerberusSettings::DEFAULT_REPLY_FROM, $default_reply_from);
			}
			
			if(!empty($default_reply_personal)) {
				$settings->set(CerberusSettings::DEFAULT_REPLY_PERSONAL, $default_reply_personal);
			}
			
			if(!empty($helpdesk_title)) {
				$settings->set(CerberusSettings::HELPDESK_TITLE, $helpdesk_title);
			}
			
			$tpl->assign('step', STEP_OUTGOING_MAIL);
			$tpl->display('steps/redirect.tpl.php');
			exit;
		}
		
		if(!empty($form_submit) && empty($default_reply_from)) {
			$tpl->assign('failed', true);
		}
		
		$tpl->assign('default_reply_from', $default_reply_from);
		$tpl->assign('default_reply_personal', $default_reply_personal);
		$tpl->assign('helpdesk_title', $helpdesk_title);
		
		$tpl->assign('template', 'steps/step_contact.tpl.php');
		
		break;
	
	// Set up and test the outgoing SMTP
	case STEP_OUTGOING_MAIL:
		$settings = CerberusSettings::getInstance();
		
		@$smtp_host = DevblocksPlatform::importGPC($_POST['smtp_host'],'string',$settings->get(CerberusSettings::SMTP_HOST));
		@$smtp_to = DevblocksPlatform::importGPC($_POST['smtp_to'],'string');
		@$smtp_auth_user = DevblocksPlatform::importGPC($_POST['smtp_auth_user'],'string');
		@$smtp_auth_pass = DevblocksPlatform::importGPC($_POST['smtp_auth_pass'],'string');
		@$form_submit = DevblocksPlatform::importGPC($_POST['form_submit'],'integer');
		@$passed = DevblocksPlatform::importGPC($_POST['passed'],'integer');
		
		if(!empty($form_submit)) {
			$mail = DevblocksPlatform::getMailService();
			$from = $_SERVER['HTTP_HOST'];
			
			// Did the user receive the test message?
			if($passed) { // passed
				if(!empty($smtp_host))
					$settings->set(CerberusSettings::SMTP_HOST, $smtp_host);
				if(!empty($smtp_auth_user))
					$settings->set(CerberusSettings::SMTP_AUTH_USER, $smtp_auth_user);
				if(!empty($smtp_auth_pass))
					$settings->set(CerberusSettings::SMTP_AUTH_PASS, $smtp_auth_pass);
				
				$tpl->assign('step', STEP_INCOMING_MAIL);
				$tpl->display('steps/redirect.tpl.php');
				exit;
				
			} else { // fail
				$from = $settings->get(CerberusSettings::DEFAULT_REPLY_FROM);
				$mail->testSmtp($smtp_host,$smtp_to,$from,$smtp_auth_user,$smtp_auth_pass);
				
				$tpl->assign('smtp_host', $smtp_host);
				$tpl->assign('smtp_to', $smtp_to);
				$tpl->assign('smtp_auth_user', $smtp_auth_user);
				$tpl->assign('smtp_auth_pass', $smtp_auth_pass);
				$tpl->assign('form_submit', $form_submit);
			}
		}
		
		// First time, or retry
		$tpl->assign('template', 'steps/step_outgoing_mail.tpl.php');
		
		break;

	// Set up a POP3/IMAP mailbox
	case STEP_INCOMING_MAIL:
		@$imap_service = DevblocksPlatform::importGPC($_POST['imap_service'],'string');
		@$imap_host = DevblocksPlatform::importGPC($_POST['imap_host'],'string');
		@$imap_user = DevblocksPlatform::importGPC($_POST['imap_user'],'string');
		@$imap_pass = DevblocksPlatform::importGPC($_POST['imap_pass'],'string');
		@$imap_port = DevblocksPlatform::importGPC($_POST['imap_port'],'integer');
		@$form_submit = DevblocksPlatform::importGPC($_POST['form_submit'],'integer');

		// Allow skip by submitting a blank form
		// Skip if we already have a pop3 box defined.
		$accounts = DAO_Mail::getPop3Accounts();
		$skip = (!empty($form_submit) && empty($imap_host) && empty($imap_user)) ? true : false; 
		if($skip OR !empty($accounts)) {
			$tpl->assign('step', STEP_WORKFLOW);
			$tpl->display('steps/redirect.tpl.php');
			exit;
		}
		
		if(!empty($form_submit)) {
			$mail = DevblocksPlatform::getMailService();

			// Test mailbox
			if($mail->testImap($imap_host,$imap_port,$imap_service,$imap_user,$imap_pass)) { // Success!
				// [TODO] Check to make sure the details aren't duplicate
				$id = DAO_Mail::createPop3Account($imap_user.'@'.$imap_host,$imap_host,$imap_user,$imap_pass);
				
				$tpl->assign('step', STEP_WORKFLOW);
				$tpl->display('steps/redirect.tpl.php');
				exit;
				
			} else { // Failed
				$tpl->assign('imap_host', $imap_host);
				$tpl->assign('imap_user', $imap_user);
				$tpl->assign('imap_pass', $imap_pass);
				$tpl->assign('imap_port', $imap_port);
				
				$tpl->assign('failed', true);
				$tpl->assign('error_msgs', $mail->getErrors());
				$tpl->assign('template', 'steps/step_incoming_mail.tpl.php');
			}
			
		} else { // defaults
			$tpl->assign('imap_port', 110);
		}
		
		$tpl->assign('template', 'steps/step_incoming_mail.tpl.php');
		
		break;
		
	// Create initial workers, mailboxes, teams
	case STEP_WORKFLOW:
		@$form_submit = DevblocksPlatform::importGPC($_POST['form_submit'],'integer');
		$settings = CerberusSettings::getInstance();

		// Catch the submit
		switch($form_submit) {
			case 1: // names form submit
				@$workers_str = DevblocksPlatform::importGPC($_POST['workers'],'string');
				@$teams_str = DevblocksPlatform::importGPC($_POST['teams'],'string');
				
				$worker_ids = array();
				$team_ids = array();

				$workers = CerberusApplication::parseCrlfString($workers_str);
				$teams = CerberusApplication::parseCrlfString($teams_str);

				if(empty($workers)) {
					$tpl->assign('failed', true);
					$tpl->assign('workers_str', $workers_str);
//					$tpl->assign('mailboxes_str', $mailboxes_str);
					$tpl->assign('teams_str', $teams_str);
					$tpl->assign('template', 'steps/step_workflow.tpl.php');
					break;
				}
				
				// Create worker records
				if(is_array($workers))
				foreach($workers as $worker_email) {
					$id = DAO_Worker::create($worker_email,'new','Joe','User','');
					$worker_ids[$id] = $worker_email; 
				}
				
				// Create team records
				if(is_array($teams))
				foreach($teams as $team_name) {
					$id = DAO_Workflow::createTeam($team_name);
					$team_ids[$id] = $team_name;
				}
				
				$tpl->assign('worker_ids', $worker_ids);
				$tpl->assign('team_ids', $team_ids);
				$tpl->assign('default_reply_from', $settings->get(CerberusSettings::DEFAULT_REPLY_FROM));
				$tpl->assign('template', 'steps/step_workflow2.tpl.php');
				break;
				
			case 2: // detailed form submit
				@$worker_ids = DevblocksPlatform::importGPC($_POST['worker_ids'],'array');
				@$worker_first = DevblocksPlatform::importGPC($_POST['worker_first'],'array');
				@$worker_last = DevblocksPlatform::importGPC($_POST['worker_last'],'array');
				@$worker_title = DevblocksPlatform::importGPC($_POST['worker_title'],'array');
				@$worker_superuser = DevblocksPlatform::importGPC($_POST['worker_superuser'],'array');
				@$worker_pw = DevblocksPlatform::importGPC($_POST['worker_pw'],'array');
				@$team_ids = DevblocksPlatform::importGPC($_POST['team_ids'],'array');

				/*
				 * [TODO] Make sure we're setting up at least one superuser.
				 * This is probably best done through a javascript form validation, 
				 * since it won't require re-typing everything into the form (or injecting)
				 * on failure.
				 */
				// 
//				if(empty($workers)) {
//					$tpl->assign('failed', true);
//					$tpl->assign('template', 'steps/step_workflow.tpl.php');
//					break;
//				}
				
				// Worker Details
				if(is_array($worker_ids))
				foreach($worker_ids as $idx => $worker_id) {
				    $passGen = new Text_Password();
				    $password = $passGen->create(8);
				    
				    $worker = DAO_Worker::getAgent($worker_id);
				    
				    if(in_array($worker_id,$worker_pw)) {
					    $mail = CerberusMail::createInstance();
					    
					    $mail->addTo($worker->email,'');
					    $mail->setSubject("Your new helpdesk login information.");
					    $mail->setBodyText(sprintf("Your new helpdesk login information is below:\n\n".
					        "URL: %s\n".
					        "Login: %s\n".
					        "Password: %s\n\n".
					        "You should change your password after logging in for the first time.\n",
						        "http://", // [TODO]
						        $worker->email,
						        $password
					    ));
					    
					    $mail->send();
				    }
				    
					$fields = array(
						DAO_Worker::FIRST_NAME => $worker_first[$idx],
						DAO_Worker::LAST_NAME => $worker_last[$idx],
						DAO_Worker::TITLE => $worker_title[$idx],
						DAO_Worker::PASSWORD => md5($password),
						DAO_Worker::IS_SUPERUSER => (in_array($worker_id,$worker_superuser) ? 1 : 0)
					);
					DAO_Worker::updateAgent($worker_id, $fields);
					
					// Create a default dashboard for each worker
					$dashboard_id = DAO_Dashboard::createDashboard("Dashboard", $worker_id);
					
					// Trash Action
					$fields = array(
						DAO_DashboardViewAction::$FIELD_NAME => 'Trash',
						DAO_DashboardViewAction::$FIELD_WORKER_ID => $worker_id,
						DAO_DashboardViewAction::$FIELD_PARAMS => serialize(array(
							'status' => CerberusTicketStatus::DELETED
						))
					);
					$trash_action_id = DAO_DashboardViewAction::create($fields);

					// Spam Action
					// [TODO] Look up the spam mailbox id
					$fields = array(
						DAO_DashboardViewAction::$FIELD_NAME => 'Report Spam',
						DAO_DashboardViewAction::$FIELD_WORKER_ID => $worker_id,
						DAO_DashboardViewAction::$FIELD_PARAMS => serialize(array(
							'status' => CerberusTicketStatus::DELETED,
							'spam' => CerberusTicketSpamTraining::SPAM
						))
					);
					$spam_action_id = DAO_DashboardViewAction::create($fields);
				}
				
				// Team Details
				// [TODO] Permissions
				if(is_array($team_ids))
				foreach($team_ids as $idx => $team_id) {
					@$team_members = DevblocksPlatform::importGPC($_POST['team_members_'.$team_id],'array');
					
					// Team Members
					if(is_array($team_members))
						DAO_Workflow::setTeamWorkers($team_id,$team_members);
				}
				
				$tpl->assign('step', STEP_CATCHALL);
				$tpl->display('steps/redirect.tpl.php');
				exit;
				
				break;
				
			default: // first time
				$tpl->assign('teams_str', "General\n");
				$tpl->assign('template', 'steps/step_workflow.tpl.php');
				break;
		}
		
		break;

	case STEP_CATCHALL:
		@$form_submit = DevblocksPlatform::importGPC($_POST['form_submit'],'integer');
		
		if(!empty($form_submit)) {
			@$default_team_id = DevblocksPlatform::importGPC($_POST['default_team_id'],'integer');
			
			$settings = CerberusSettings::getInstance();
			$settings->set(CerberusSettings::DEFAULT_TEAM_ID,$default_team_id);
			
			$tpl->assign('step', STEP_ANTISPAM);
			$tpl->display('steps/redirect.tpl.php');
			exit;
		}
		
		$teams = DAO_Workflow::getTeams();
		$tpl->assign('teams', $teams);
		
		$tpl->assign('template', 'steps/step_catchall.tpl.php');
		
		break;
		
	// [TODO] Create an anti-spam rule and mailbox automatically
	case STEP_ANTISPAM:
		@$setup_antispam = DevblocksPlatform::importGPC($_POST['setup_antispam'],'integer');
		@$form_submit = DevblocksPlatform::importGPC($_POST['form_submit'],'integer');
		
		if($form_submit) {
			// [TODO] Implement (to check for dupes)
			$id = 0;
//			$id = DAO_Mail::lookupMailbox('Spam');
			
			if($setup_antispam && empty($id)) {
//				$id = DAO_Mail::createMailbox('Spam',0);

				// [TODO] Need to fit antispam into the new team-oriented concepts (no more mailbox)
				
				// [TODO] Need to create a mail rule to route spam > 90%
				
				// Assign the new mailbox to all existing teams
//				$teams = DAO_Workflow::getTeams();
//				if(is_array($teams))
//				foreach($teams as $team_id => $team) { /* @var $team CerberusTeam */
//					$mailbox_keys = array_keys($team->getMailboxes());
//					$mailbox_keys[] = $id;
//					// [TODO] This could be simplified with the addition of addTeamMailbox(id,id)
//					DAO_Workflow::setTeamMailboxes($team_id, $mailbox_keys);
//				}
			}
			
			$tpl->assign('step', STEP_REGISTER);
			$tpl->display('steps/redirect.tpl.php');
			exit;
		}
		
		$tpl->assign('template', 'steps/step_antispam.tpl.php');
		break;

	// [TODO] Automatically collect 'About Me' information? (Register, with benefit)
	case STEP_REGISTER:
		@$register = DevblocksPlatform::importGPC($_POST['register'],'integer');
		@$form_submit = DevblocksPlatform::importGPC($_POST['form_submit'],'integer');
		
		if(!empty($form_submit)) {
			$tpl->assign('step', STEP_FINISHED);
			$tpl->display('steps/redirect.tpl.php');
			exit;
		}
		
		$tpl->assign('template', 'steps/step_register.tpl.php');
		break;
		
	case STEP_UPGRADE:
		$patchMgr = DevblocksPlatform::getPatchService();
		$patchMgr->clear();
		
		// [JAS]: Run our overloaded container for the platform
		$patchMgr->registerPatchContainer(new PlatformPatchContainer());
		
		// Clean script
		if(!$patchMgr->run()) {
			// [TODO] Show more info on the error
			$tpl->assign('template', 'steps/step_upgrade.tpl.php');
			
		} else { // success
			// Read in plugin information from the filesystem to the database
			DevblocksPlatform::readPlugins();
			DevblocksPlatform::clearCache();
			
			// Run enabled plugin patches
			$patches = DevblocksPlatform::getExtensions("devblocks.patch.container");
			
			if(is_array($patches))
			foreach($patches as $patch_manifest) { /* @var $patch_manifest DevblocksExtensionManifest */ 
				 $container = $patch_manifest->createInstance(); /* @var $container DevblocksPatchContainerExtension */
				 $patchMgr->registerPatchContainer($container);
			}
			
			if(!$patchMgr->run()) { // fail
				$tpl->assign('template', 'steps/step_upgrade.tpl.php');
				
			} else { // pass
				$tpl->assign('step', STEP_FINISHED);
				$tpl->display('steps/redirect.tpl.php');
				exit;
			}
		}
		
		break;
		
	// [TODO] Delete the /install/ directory (security)
	case STEP_FINISHED:
		$tpl->assign('template', 'steps/step_finished.tpl.php');
		break;
}

// [TODO] Configure attachment path (/attachments?)  -- Can remove the is_writeable check in the top if !/attachments

// [TODO] Check PEAR path

// [TODO] Automatically do the spam step?  (no prompt/step)

// [TODO] License Agreement (first step)

// [TODO] Support Center (move to SC installer)

// [TODO] Set up the cron to run using internal timer by default

// [TODO] Check apache rewrite (somehow)

// [TODO] Check if safe_mode is disabled, and if so set our php.ini overrides in the framework.config.php rewrite

/*
Jeremy: yup... that's it... :)
stupid adodb hiding that error
I switched framework.config.php to have mysqli as the db driver (the extension which I have loaded) and it works

Jeff: k, sweet. I just need to add that to the dropdown, and then have the platform or installer check for any of the possible ones being there and complain if none
I'll add to install/index.php [TODO]
 */

$tpl->display('base.tpl.php');
