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

// Classes
$path = realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR;

DevblocksPlatform::registerClasses($path. 'api/Extension.php', array(
    'Extension_UsermeetTool'
));
DevblocksPlatform::registerClasses($path. 'api/Model.php', array(
    'Model_CommunityTool'
));
DevblocksPlatform::registerClasses($path. 'api/DAO.php', array(
    'DAO_CommunityTool'
));

class UmCorePlugin extends DevblocksPlugin {
	function load(DevblocksPluginManifest $manifest) {
	}
};

class UmPortalController extends DevblocksControllerExtension {
    const ID = 'usermeet.controller.portal';
	private $tools = array();
	private $hash = array();
    
	function __construct($manifest) {
		parent::__construct($manifest);

	    // Routing
	    $router = DevblocksPlatform::getRoutingService();
	    $router->addRoute('portal', self::ID);
	    
	    // Internal Routing
	    // [TODO] Cache the code to extension lookup -- silly to go to DB every time for this
	    $this->tools = DAO_CommunityTool::getList();
	    foreach($this->tools as $idx => $tool) {
	        $this->hash[$tool->code] =& $this->tools[$idx];
	    }
	}
		
	/**
	 * @param DevblocksHttpRequest $request 
	 * @return DevblocksHttpResponse $response 
	 */
	function handleRequest(DevblocksHttpRequest $request) {
		$stack = $request->path;

		array_shift($stack); // portal
		$code = array_shift($stack); // xxxxxxxx

		// [TODO] Convert to Model_CommunityTool::getByCode()
        if(null != (@$tool = $this->hash[$code])) {
	        // [TODO] Don't double instance any apps (add instance registry to ::getExtension?)
	        $manifest = DevblocksPlatform::getExtension($tool->extension_id);
            if(null != ($tool = $manifest->createInstance())) { /* @var $app Extension_UsermeetTool */
				$tool->setPortal($code); // [TODO] Kinda hacky
	        	return $tool->handleRequest(new DevblocksHttpRequest($stack));
            }
        } else {
            die("Tool not found.");
        }
	}
	
	/**
	 * @param DevblocksHttpResponse $response
	 */
	function writeResponse(DevblocksHttpResponse $response) {
		$stack = $response->path;

		$tpl = DevblocksPlatform::getTemplateService();

		// Globals for Community Tool template scope
		$translate = DevblocksPlatform::getTranslationService();
		$tpl->assign('translate', $translate);
		
		array_shift($stack); // portal
		$code = array_shift($stack); // xxxxxxxx

		// [TODO] Convert to Model_CommunityTool::getByCode()
        if(null != ($tool = $this->hash[$code])) {
	        // [TODO] Don't double instance any apps (add instance registry to ::getExtension?)
	        $manifest = DevblocksPlatform::getExtension($tool->extension_id);
            if(null != ($tool = $manifest->createInstance())) { /* @var $app Extension_UsermeetTool */
				$tool->setPortal($code); // [TODO] Kinda hacky
		        $tool->writeResponse(new DevblocksHttpResponse($stack));
            }
        } else {
            die("Tool not found.");
        }
	}
	
//	function test() {
//	    $proxyhost = $_SERVER['HTTP_DEVBLOCKSPROXYHOST'];
//	    $proxybase = $_SERVER['HTTP_DEVBLOCKSPROXYBASE'];
//
//	    echo "<html><head></head><body>";
//	    
//	    echo 'Proxy Host: ', $proxyhost, '<BR>';
//	    echo 'Proxy Base: ', $proxybase, '<BR>';
//	    echo 'Response: ', var_dump($response), '<BR>';
//	    
//	    echo "<h2>Get Test</h2>";
//	    echo "<a href='$proxybase/latest'>latest</a><br>";
//
//	    echo "<h2>Post Test</h2>";
//	    echo "<form action=\"${proxybase}/\" method=\"post\">";
//	    echo "<input type='text' name='name' value=''><br>";
//	    echo "<input type='checkbox' name='checky' value='1'><br>";
//	    echo "<input type='submit' value='Submit'>";
//	    echo "</form>";
//	    
//	    if(!empty($_POST)) {
//	        echo "<HR>"; print_r($_POST); echo "<HR>";
//	    }
//	    
//        echo "Cookies: ";
//	    print_r($_COOKIE);
//	    echo "<HR>";
//	    
////	    echo "PORTAL RESPONSE";
//	    echo "</body></html>";
//	    exit;
//	}
	
};

class UmCommunityPage extends CerberusPageExtension {
	function __construct($manifest) {
		parent::__construct($manifest);

		$path = realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR;
	}
		
	function isVisible() {
		// check login
		$session = DevblocksPlatform::getSessionService();
		$visit = $session->getVisit();
		
		if(empty($visit)) {
			return false;
		} else {
			return true;
		}
	}
	
	// Ajax
	function actionAction() {
    	if(null == ($worker = CerberusApplication::getActiveWorker()))
    		die();
		
    	@$sCode = DevblocksPlatform::importGPC($_REQUEST['code'],'string','');
    	@$sAction = DevblocksPlatform::importGPC($_REQUEST['action'],'string','');

		if(null != ($instance = DAO_CommunityTool::getByCode($sCode))) {
			$manifest = DevblocksPlatform::getExtension($instance->extension_id);
            $tool = $manifest->createInstance(); /* @var $app Extension_UsermeetTool */
			$tool->setPortal($sCode); // [TODO] Kinda hacky
			
			// Passthrough [TODO] need to secure to agent logins
			if(method_exists($tool, $sAction)) {
				call_user_func(array($tool,$sAction));
			}
		}
	}
	
	function render() {
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl->cache_lifetime = "0";
		$tpl->assign('path', dirname(__FILE__) . '/templates/');
		
		$response = DevblocksPlatform::getHttpResponse();
		$stack = $response->path;

		$host = $_SERVER['HTTP_HOST'];
		$tpl->assign('host', $host);
		
		array_shift($stack); // community
		
		switch(array_shift($stack)) {
		        
		    case 'add_widget':
		        $communities = DAO_Community::getList();
			    $tpl->assign('communities', $communities);
			    
			    // Widget Manifests
			    $widgets = DevblocksPlatform::getExtensions('usermeet.widget', false);
			    $tpl->assign('widget_manifests', $widgets);
			    
		        $tpl->display('file:' . dirname(__FILE__) . '/templates/community/add_widget.tpl.php');
		        break;
		    
		    case 'tool':
		        $code = array_shift($stack);
		        $action = array_shift($stack);
		        
		        $tpl->assign('portal', $code);
				
		        $tpl->display('file:' . dirname(__FILE__) . '/templates/community/tool_display.tpl.php');						
		        
//		        switch($action) {
//		        	case 'configure': // configure
//		        		break;
//		        		
//		        	default:
//	        			break;
//		        }
		        
		        break;
		    
		    case 'widget':
		        echo "CONFIGURE";
		        break;
		    
		    default:
				// Community sites
			    $communities = DAO_Community::getList();
			    $tpl->assign('communities', $communities);
			    
			    // Community Tools + Widgets (Indexed)
			    $community_addons = array();
		        foreach(array_keys($communities) as $idx) {
		            $community_addons[$idx] = array('tools'=>array(),'widgets'=>array());
		        }
			    
			    $community_tools = DAO_CommunityTool::getList();
			    foreach($community_tools as $tool) {
			        if(!isset($community_addons[$tool->community_id])) continue;
			        $community_addons[$tool->community_id]['tools'][$tool->code] = $tool->extension_id;
			    }
			    
			    // Tool Manifests
			    $tools = DevblocksPlatform::getExtensions('usermeet.tool', false);
			    $tpl->assign('tool_manifests', $tools);
			    
			    // [TODO] Widget Manifests
			    $widgets = DevblocksPlatform::getExtensions('usermeet.widget', false);
			    $tpl->assign('widget_manifests', $widgets);
			    			    
			    $tpl->assign('community_addons', $community_addons);
				
				$tpl->display('file:' . dirname(__FILE__) . '/templates/community/index.tpl.php');
		        break;
		}
		
	}
	
	function showToolConfigAction() {
		@$portal = DevblocksPlatform::importGPC($_REQUEST['portal'],'string','');
		
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl->cache_lifetime = "0";
		$tpl->assign('path', dirname(__FILE__) . '/templates/');
		
		$tpl->assign('portal', $portal);
		
		if(null != ($instance = DAO_CommunityTool::getByCode($portal))) {
			$tpl->assign('instance', $instance);
			$manifest = DevblocksPlatform::getExtension($instance->extension_id);
            if(null != ($tool = $manifest->createInstance())) { /* @var $app Extension_UsermeetTool */
				$tool->setPortal($portal); // [TODO] Kinda hacky
        		$tpl->assign('tool', $tool);
            }
		}
        
        // Community Record
        $community_id = $instance->community_id;
        $community = DAO_Community::get($community_id);
        $tpl->assign('community', $community);
		
		$tpl->display('file:' . dirname(__FILE__) . '/templates/community/tool_config.tpl.php');
	}
	
	function showToolInstallAction() {
		@$portal = DevblocksPlatform::importGPC($_REQUEST['portal'],'string','');
		
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl->cache_lifetime = "0";
		$tpl->assign('path', dirname(__FILE__) . '/templates/');
		
		$tpl->assign('portal', $portal);
		
        $url_writer = DevblocksPlatform::getUrlService();
        $url = $url_writer->write('c=portal&a='.$portal,true);
        $url_parts = parse_url($url);
        
        $host = $url_parts['host'];
		$base = substr(DEVBLOCKS_WEBPATH,0,-1); // consume trailing
        $path = substr($url_parts['path'],strlen(DEVBLOCKS_WEBPATH)-1); // consume trailing slash

        @$parts = explode('/', $path);
        if($parts[1]=='index.php') // 0 is null from /part1/part2 paths.
        	unset($parts[1]);
        $path = implode('/', $parts);
        
		$tpl->assign('host', $host);
		$tpl->assign('base', $base);
		$tpl->assign('path', $path);
		
		$tpl->display('file:' . dirname(__FILE__) . '/templates/community/tool_install.tpl.php');
	}
	
	// Facade
	function saveConfigurationAction() {
		@$code = DevblocksPlatform::importGPC($_POST['code'],'string');
        @$iFinished = DevblocksPlatform::importGPC($_POST['finished'],'integer',0);
        @$iDelete = DevblocksPlatform::importGPC($_POST['do_delete'],'integer',0);
		
		if(DEMO_MODE) {
			DevblocksPlatform::redirect(new DevblocksHttpResponse(array('community')));
			return;
		}

        if(null != ($instance = DAO_CommunityTool::getByCode($code))) {
			// Deleting?
			if(!empty($iDelete)) {
				$tool = DAO_CommunityTool::getByCode($code); /* @var $tool Model_CommunityTool */
				DAO_CommunityTool::delete($tool->id);
				DevblocksPlatform::setHttpResponse(new DevblocksHttpResponse(array('community')));
				return;
				
			} else {
				$manifest = DevblocksPlatform::getExtension($instance->extension_id);
	            $tool = $manifest->createInstance(); /* @var $tool Extension_UsermeetTool */
				$tool->setPortal($code); // [TODO] Kinda hacky
				$tool->saveConfiguration();
			}
		}
		
		if($iFinished) {
			DevblocksPlatform::redirect(new DevblocksHttpResponse(array('community')));
		} else {
			DevblocksPlatform::redirect(new DevblocksHttpResponse(array('community','tool',$code,'configure')));
		}
	}
	
	function showCommunityPanelAction() {
		@$id = DevblocksPlatform::importGPC($_REQUEST['id'],'integer',0);
		
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl->cache_lifetime = "0";
		$tpl->assign('path', dirname(__FILE__) . '/templates/');
		
		if(!empty($id)) {
			$community = DAO_Community::get($id);
			$tpl->assign('community', $community);
		}
		
		$tpl->display('file:' . dirname(__FILE__) . '/templates/community/community_panel.tpl.php');
	}
	
	function editCommunityAction() {
		// [TODO] Privs
		@$id = DevblocksPlatform::importGPC($_POST['id'],'integer',0);
		@$name = DevblocksPlatform::importGPC($_POST['name'],'string','New Community');	
		@$delete = DevblocksPlatform::importGPC($_POST['do_delete'],'integer',0);	

		if(DEMO_MODE) {
			DevblocksPlatform::redirect(new DevblocksHttpResponse(array('community')));
			return;
		}

		if(!empty($delete)) {
			DAO_Community::delete($id);
			
		} else {
		    $fields = array(
		        DAO_Community::NAME => $name,
		    );
			
			if(empty($id)) { // Create
			    $id = DAO_Community::create($fields);
				
			} else { // Edit || Delete
			    $id = DAO_Community::update($id,$fields);
			}
		}
		
		DevblocksPlatform::redirect(new DevblocksHttpResponse(array('community')));
	}
	
	function addCommunityToolAction() {
	    @$community_id = DevblocksPlatform::importGPC($_POST['community_id'],'integer');	    
	    @$extension_id = DevblocksPlatform::importGPC($_POST['extension_id'],'string');

	    if(empty($community_id) || empty($extension_id)) return;
	    
	    $fields = array(
	        DAO_CommunityTool::COMMUNITY_ID => $community_id,
	        DAO_CommunityTool::EXTENSION_ID => $extension_id
	    );
	    $id = DAO_CommunityTool::create($fields);
	    
	    $tool = DAO_CommunityTool::get($id);
	    
	    DevblocksPlatform::setHttpResponse(new DevblocksHttpResponse(array('community','tool',$tool->code,'configure')));
	}
	
};

class UmContactApp extends Extension_UsermeetTool {
	const PARAM_DISPATCH = 'dispatch';
	const PARAM_LOGO_URL = 'logo_url';
	const PARAM_THEME_URL = 'theme_url';
	const PARAM_PAGE_TITLE = 'page_title';
	const PARAM_CAPTCHA_ENABLED = 'captcha_enabled';
	const SESSION_CAPTCHA = 'write_captcha';
	
    function __construct($manifest) {
        parent::__construct($manifest);
        
        $filepath = realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR;
        
//        DevblocksPlatform::registerClasses('Text/CAPTCHA.php',array(
//        	'Text_CAPTCHA',
//        ));
    }
    
	public function writeResponse(DevblocksHttpResponse $response) {
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl->cache_lifetime = "0";
		$tpl->assign('path', dirname(__FILE__) . '/templates/');
		
        $umsession = $this->getSession();
		$stack = $response->path;
		
		$logo_url = DAO_CommunityToolProperty::get($this->getPortal(), self::PARAM_LOGO_URL, '');
		$tpl->assign('logo_url', $logo_url);
        
		$page_title = DAO_CommunityToolProperty::get($this->getPortal(), self::PARAM_PAGE_TITLE, 'Contact Us');
		$tpl->assign('page_title', $page_title);
        
        $captcha_enabled = DAO_CommunityToolProperty::get($this->getPortal(), self::PARAM_CAPTCHA_ENABLED, 1);
		$tpl->assign('captcha_enabled', $captcha_enabled);
		
		// Usermeet Session
		if(null == ($fingerprint = parent::getFingerprint())) {
			die("A problem occurred.");
		}
        $tpl->assign('fingerprint', $fingerprint);

		switch(array_shift($stack)) {
			case 'captcha':
                header('Cache-control: max-age=0', true); // 1 wk // , must-revalidate
                header('Expires: ' . gmdate('D, d M Y H:i:s',time()-604800) . ' GMT'); // 1 wk
				header('Content-type: image/jpeg');
                //header('Content-length: '. count($jpg));

//		        // Get CAPTCHA secret passphrase
				$phrase = CerberusApplication::generatePassword(4);
		        $umsession->setProperty(self::SESSION_CAPTCHA, $phrase);
                
				$im = @imagecreate(150, 80) or die("Cannot Initialize new GD image stream");
				$background_color = imagecolorallocate($im, 0, 0, 0);
				$text_color = imagecolorallocate($im, 255, 255, 255); //233, 14, 91
				$font = DEVBLOCKS_PATH . 'resources/font/ryanlerch_-_Tuffy_Bold(2).ttf';
				imagettftext($im, 24, rand(0,20), 5, 60+6, $text_color, $font, $phrase);
//				$im = imagerotate($im, rand(-20,20), $background_color);
				imagejpeg($im,null,85);
				imagedestroy($im);
				exit;
				break;
			
		    	default:
				case 'write':
		    	$response = array_shift($stack);
		    	switch($response) {
		    		case 'confirm':
		    			$tpl->assign('last_opened',$umsession->getProperty('support.write.last_opened',''));
		    			$tpl->display('file:' . dirname(__FILE__) . '/templates/portal/contact/write/confirm.tpl.php');
		    			break;
		    		
		    		default:
		    		case 'step1':
		    		case 'step2':
		    		case 'step3':
		    			$sFrom = $umsession->getProperty('support.write.last_from','');
		    			$sNature = $umsession->getProperty('support.write.last_nature','');
		    			$sContent = $umsession->getProperty('support.write.last_content','');
		    			$sError = $umsession->getProperty('support.write.last_error','');
		    			
						$tpl->assign('last_from', $sFrom);
						$tpl->assign('last_nature', $sNature);
						$tpl->assign('last_content', $sContent);
						$tpl->assign('last_error', $sError);
						
        				$sDispatch = DAO_CommunityToolProperty::get($this->getPortal(),self::PARAM_DISPATCH, '');
		    			$dispatch = !empty($sDispatch) ? unserialize($sDispatch) : array();
				        $tpl->assign('dispatch', $dispatch);
				        
				        switch($response) {
				        	default:
				        		$tpl->display('file:' . dirname(__FILE__) . '/templates/portal/contact/write/step1.tpl.php');
				        		break;
				        		
				        	case 'step2':
				        		// Cache along with answers?
								if(is_array($dispatch))
						        foreach($dispatch as $k => $v) {
						        	if(md5($k)==$sNature) {
						        		$umsession->setProperty('support.write.last_nature_string', $k);
						        		$tpl->assign('situation', $k);
						        		$tpl->assign('situation_params', $v);
						        		break;
						        	}
						        }
				        		$tpl->display('file:' . dirname(__FILE__) . '/templates/portal/contact/write/step2.tpl.php');
				        		break;
				        		
				        	case 'step3':
				        		$tpl->display('file:' . dirname(__FILE__) . '/templates/portal/contact/write/step3.tpl.php');
				        		break;
				        }
				        break;
		    	}
		    	break;
		}
	}
	
	function doStep2Action() {
		$umsession = $this->getSession();
		$fingerprint = parent::getFingerprint();
		
		@$sNature = DevblocksPlatform::importGPC($_POST['nature'],'string','');

		$umsession->setProperty('support.write.last_nature', $sNature);
		$umsession->setProperty('support.write.last_content', null);
		$umsession->setProperty('support.write.last_error', null);
		
		$sDispatch = DAO_CommunityToolProperty::get($this->getPortal(),self::PARAM_DISPATCH, '');
		$dispatch = !empty($sDispatch) ? unserialize($sDispatch) : array();
		
		// Check if this nature has followups, if not skip to step3
		$followups = array();
		if(is_array($dispatch))
        foreach($dispatch as $k => $v) {
        	if(md5($k)==$sNature) {
        		$umsession->setProperty('support.write.last_nature_string', $k);
        		@$followups = $v['followups'];
        		break;
        	}
        }

        if(empty($followups)) {		
			DevblocksPlatform::setHttpResponse(new DevblocksHttpResponse(array('portal',$this->getPortal(),'write','step3')));
        } else {
			DevblocksPlatform::setHttpResponse(new DevblocksHttpResponse(array('portal',$this->getPortal(),'write','step2')));
        }
	}
	
	function doStep3Action() {
		$umsession = $this->getSession();
		$fingerprint = parent::getFingerprint();

		@$aFollowUpQ = DevblocksPlatform::importGPC($_POST['followup_q'],'array',array());
		@$aFollowUpA = DevblocksPlatform::importGPC($_POST['followup_a'],'array',array());
		$nature = $umsession->getProperty('support.write.last_nature_string','');
		$content = '';
		
		if(!empty($aFollowUpQ)) {
			$content = "Comments:\r\n\r\n\r\n";
			$content .= "--------------------------------------------\r\n";
			if(!empty($nature)) {
				$content .= $nature . "\r\n";
				$content .= "--------------------------------------------\r\n";
			}
			foreach($aFollowUpQ as $idx => $q) {
				$content .= "Q) " . $q . "\r\n" . "A) " . $aFollowUpA[$idx] . "\r\n";
				if($idx+1 < count($aFollowUpQ)) $content .= "\r\n";
			}
			$content .= "--------------------------------------------\r\n";
			"\r\n";
		}
		
		$umsession->setProperty('support.write.last_content', $content);
		$umsession->setProperty('support.write.last_error', null);
		
		DevblocksPlatform::setHttpResponse(new DevblocksHttpResponse(array('portal',$this->getPortal(),'write','step3')));
	}
	
	function doSendMessageAction() {
		@$sFrom = DevblocksPlatform::importGPC($_POST['from'],'string','');
		@$sContent = DevblocksPlatform::importGPC($_POST['content'],'string','');
		@$sCaptcha = DevblocksPlatform::importGPC($_POST['captcha'],'string','');
		
		$umsession = $this->getSession();
		$fingerprint = parent::getFingerprint();

        $settings = CerberusSettings::getInstance();
        $default_from = $settings->get(CerberusSettings::DEFAULT_REPLY_FROM);

		$umsession->setProperty('support.write.last_from',$sFrom);
		$umsession->setProperty('support.write.last_content',$sContent);
        
		$sNature = $umsession->getProperty('support.write.last_nature', '');
		
        $captcha_enabled = DAO_CommunityToolProperty::get($this->getPortal(), self::PARAM_CAPTCHA_ENABLED, 1);
		
		if(empty($sFrom) || ($captcha_enabled && 0 != strcasecmp($sCaptcha,@$umsession->getProperty(self::SESSION_CAPTCHA,'***')))) {
			
			if(empty($sFrom)) {
				$umsession->setProperty('support.write.last_error','Invalid e-mail address.');
			} else {
				$umsession->setProperty('support.write.last_error','What you typed did not match the image.');
			}
			
			// [TODO] Need to report the captcha didn't match and redraw the form
			DevblocksPlatform::setHttpResponse(new DevblocksHttpResponse(array('portal',$this->getPortal(),'write','step3')));
			return;
		}

		// Dispatch
		$to = $default_from;
		$subject = 'Contact me: Other';
		
        $sDispatch = DAO_CommunityToolProperty::get($this->getPortal(),self::PARAM_DISPATCH, '');
        $dispatch = !empty($sDispatch) ? unserialize($sDispatch) : array();

        foreach($dispatch as $k => $v) {
        	if(md5($k)==$sNature) {
        		$to = $v['to'];
        		$subject = 'Contact me: ' . strip_tags($k);
        		break;
        	}
        }
		
		$message = new CerberusParserMessage();
		$message->headers['date'] = date('r'); 
		$message->headers['to'] = $to;
		$message->headers['subject'] = $subject;
		$message->headers['message-id'] = CerberusApplication::generateMessageId();
		$message->headers['x-cerberus-portal'] = 1; 
		
		// Sender
		$fromList = imap_rfc822_parse_adrlist($sFrom,'');
		if(empty($fromList) || !is_array($fromList)) {
			return; // abort with message
		}
		$from = array_shift($fromList);
		$message->headers['from'] = $from->mailbox . '@' . $from->host; 

		$message->body = 'IP: ' . $fingerprint['ip'] . "\r\n\r\n" . $sContent;

		$ticket_id = CerberusParser::parseMessage($message);
		$ticket = DAO_Ticket::getTicket($ticket_id);
		
//		echo "Created Ticket ID: $ticket_id<br>";
		// [TODO] Could set this ID/mask into the UMsession

		// Clear any errors
		$umsession->setProperty('support.write.last_nature',null);
		$umsession->setProperty('support.write.last_nature_string',null);
		$umsession->setProperty('support.write.last_content',null);
		$umsession->setProperty('support.write.last_error',null);
		$umsession->setProperty('support.write.last_opened',$ticket->mask);
		
		DevblocksPlatform::setHttpResponse(new DevblocksHttpResponse(array('portal',$this->getPortal(),'write','confirm')));
	}
	
	/**
	 * @param $instance Model_CommunityTool 
	 */
    public function configure(Model_CommunityTool $instance) {
        $tpl = DevblocksPlatform::getTemplateService();
        $tpl_path = realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR;
        $tpl->assign('config_path', $tpl_path);
        
        $settings = CerberusSettings::getInstance();
        
        $default_from = $settings->get(CerberusSettings::DEFAULT_REPLY_FROM);
        $tpl->assign('default_from', $default_from);
        
        $sDispatch = DAO_CommunityToolProperty::get($this->getPortal(),self::PARAM_DISPATCH, '');
        $dispatch = !empty($sDispatch) ? unserialize($sDispatch) : array();
        $tpl->assign('dispatch', $dispatch);
        
        $logo_url = DAO_CommunityToolProperty::get($this->getPortal(), self::PARAM_LOGO_URL, '');
		$tpl->assign('logo_url', $logo_url);
        
        $page_title = DAO_CommunityToolProperty::get($this->getPortal(), self::PARAM_PAGE_TITLE, 'Contact Us');
		$tpl->assign('page_title', $page_title);
        
        $captcha_enabled = DAO_CommunityToolProperty::get($this->getPortal(), self::PARAM_CAPTCHA_ENABLED, 1);
		$tpl->assign('captcha_enabled', $captcha_enabled);
        
        $tpl->display("file:${tpl_path}portal/contact/config/index.tpl.php");
    }
    
    // Ajax
    public function getSituation() {
		@$sCode = DevblocksPlatform::importGPC($_REQUEST['code'],'string','');
		@$sReason = DevblocksPlatform::importGPC($_REQUEST['reason'],'string','');
    	 
    	$tool = DAO_CommunityTool::getByCode($sCode);
    	 
        $tpl = DevblocksPlatform::getTemplateService();
        $tpl_path = realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR;

        $settings = CerberusSettings::getInstance();
        
        $default_from = $settings->get(CerberusSettings::DEFAULT_REPLY_FROM);
        $tpl->assign('default_from', $default_from);
        
        $sDispatch = DAO_CommunityToolProperty::get($this->getPortal(),self::PARAM_DISPATCH, '');
        $dispatch = !empty($sDispatch) ? unserialize($sDispatch) : array();
        
        if(is_array($dispatch))
        foreach($dispatch as $reason => $params) {
        	if(md5($reason)==$sReason) {
        		$tpl->assign('situation_reason', $reason);
        		$tpl->assign('situation_params', $params);
        		break;
        	}
        }
        
        $tpl->display("file:${tpl_path}portal/contact/config/add_situation.tpl.php");
		exit;
    }
    
    public function saveConfiguration() {
        @$sLogoUrl = DevblocksPlatform::importGPC($_POST['logo_url'],'string','');
        @$sPageTitle = DevblocksPlatform::importGPC($_POST['page_title'],'string','Contact Us');
        @$iCaptcha = DevblocksPlatform::importGPC($_POST['captcha_enabled'],'integer',1);
//        @$sThemeUrl = DevblocksPlatform::importGPC($_POST['theme_url'],'string','');

        DAO_CommunityToolProperty::set($this->getPortal(), self::PARAM_LOGO_URL, $sLogoUrl);
        DAO_CommunityToolProperty::set($this->getPortal(), self::PARAM_PAGE_TITLE, $sPageTitle);
        DAO_CommunityToolProperty::set($this->getPortal(), self::PARAM_CAPTCHA_ENABLED, $iCaptcha);
//        DAO_CommunityToolProperty::set($this->getPortal(), self::PARAM_THEME_URL, $sThemeUrl);
        
        $settings = CerberusSettings::getInstance();
        $default_from = $settings->get(CerberusSettings::DEFAULT_REPLY_FROM);
        
    	@$sEditReason = DevblocksPlatform::importGPC($_POST['edit_reason'],'string','');
    	@$sReason = DevblocksPlatform::importGPC($_POST['reason'],'string','');
        @$sTo = DevblocksPlatform::importGPC($_POST['to'],'string','');
        @$aFollowup = DevblocksPlatform::importGPC($_POST['followup'],'array',array());
        @$aFollowupLong = DevblocksPlatform::importGPC($_POST['followup_long'],'array',array());
        
        if(empty($sTo))
        	$sTo = $default_from;
        
        $sDispatch = DAO_CommunityToolProperty::get($this->getPortal(),self::PARAM_DISPATCH, '');
        $dispatch = !empty($sDispatch) ? unserialize($sDispatch) : array();

        // [JAS]: [TODO] Only needed temporarily to clean up imports
		// [TODO] Move to patch
        if(is_array($dispatch))
        foreach($dispatch as $d_reason => $d_params) {
        	if(!is_array($d_params)) {
        		$dispatch[$d_reason] = array('to'=>$d_params,'followups'=>array());
        	} else {
        		unset($d_params['']);
        	}
        }

        // Nuke a record we're replacing
       	if(!empty($sEditReason)) {
			// will be MD5
	        if(is_array($dispatch))
	        foreach($dispatch as $d_reason => $d_params) {
	        	if(md5($d_reason)==$sEditReason) {
	        		unset($dispatch[$d_reason]);
	        	}
	        }
       	}
        
       	// If we have new data, add it
        if(!empty($sReason) && !empty($sTo)) {
			$dispatch[$sReason] = array(
				'to' => $sTo,
				'followups' => array()
			);
			
			$followups =& $dispatch[$sReason]['followups'];
			
			if(!empty($aFollowup))
			foreach($aFollowup as $idx => $followup) {
				if(empty($followup)) continue;
				$followups[$followup] = (false !== array_search($idx,$aFollowupLong)) ? 1 : 0;
			}
        }
        
        ksort($dispatch);
        
		DAO_CommunityToolProperty::set($this->getPortal(), self::PARAM_DISPATCH, serialize($dispatch));
    }
	
};

class UmKbApp extends Extension_UsermeetTool {
	const PARAM_LOGO_URL = 'logo_url';
	const PARAM_THEME_URL = 'theme_url';
	const PARAM_PAGE_TITLE = 'page_title';
	const PARAM_CAPTCHA_ENABLED = 'captcha_enabled';
	const PARAM_EDITORS = 'editors';
	
	const SESSION_CAPTCHA = 'write_captcha';
	const SESSION_EDITOR = 'kb_editor';
	const SESSION_ARTICLE_LIST = 'kb_article_list';
	
	const TAG_INDEX_PREFIX = 'ch_kb';
	
    function __construct($manifest) {
        parent::__construct($manifest);
        $filepath = realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR;
    }
    
    private function _getTagIndex() {
    	return self::TAG_INDEX_PREFIX . $this->getPortal();
    }
    
    private function _writeArticlesAsRss($articles, $title) {
		$url_writer = DevblocksPlatform::getUrlService();
	
		$aFeed = array(
			'title' => $title,
	    	'link' => $url_writer->write('', true),
			'charset' => 'utf-8',
			'entries' => array(),
		);
		
		foreach($articles as $article_id => $article) {
			$summary = substr(strip_tags($article->content),0,255) . (strlen($article->content)>255?'...':'');
			
			$aEntry = array(
				'title' => utf8_encode($article->title),
				'link' => $url_writer->write('c=article&id='.$article_id, true),
				'lastUpdate' => $article->updated,
				'published' => $article->updated,
				'guid' => md5($article->title.$article->updated),
				'description' => utf8_encode($summary),
				'content' => utf8_encode($article->content),
			);
			$aFeed['entries'][] = $aEntry;
		}
		unset($articles);
	
		$rssFeed = Zend_Feed::importArray($aFeed, 'rss');
		
		echo $rssFeed->saveXML();				
    }
    
	public function writeResponse(DevblocksHttpResponse $response) {
		$tpl = DevblocksPlatform::getTemplateService();
		$tpl->cache_lifetime = "0";
		$tpl->assign('tpl_path', dirname(__FILE__) . '/templates/');
		
        $umsession = $this->getSession();
		$stack = $response->path;
		
		$logo_url = DAO_CommunityToolProperty::get($this->getPortal(), self::PARAM_LOGO_URL, '');
		$tpl->assign('logo_url', $logo_url);
        
		$page_title = DAO_CommunityToolProperty::get($this->getPortal(), self::PARAM_PAGE_TITLE, 'Knowledgebase');
		$tpl->assign('page_title', $page_title);
        
        $captcha_enabled = DAO_CommunityToolProperty::get($this->getPortal(), self::PARAM_CAPTCHA_ENABLED, 1);
		$tpl->assign('captcha_enabled', $captcha_enabled);
		
        $editor = $umsession->getProperty(self::SESSION_EDITOR, null);
		$tpl->assign('editor', $editor);
		
		// Usermeet Session
		if(null == ($fingerprint = parent::getFingerprint())) {
			die("A problem occurred.");
		}
        $tpl->assign('fingerprint', $fingerprint);

		switch(array_shift($stack)) {
			case 'rss':
				switch(array_shift($stack)) {
					case 'recent_changes':
						list($results, $null) = DAO_KbArticle::search(
							array(
								new DevblocksSearchCriteria(SearchFields_KbArticle::CODE,'=',$this->getPortal())
							),
							25,
							0,
							SearchFields_KbArticle::UPDATED,
							false,
							false
						);
						
						if(is_array($results) && !empty($results))
						$full_articles = DAO_KbArticle::getWhere(sprintf("%s IN (%s)",
							DAO_KbArticle::ID,
							implode(',', array_keys($results))
						));
						
						$order= array_keys($results);
						$articles = array();
						foreach($order as $id) {
							$articles[$id] =& $full_articles[$id];
						}
						
						$this->_writeArticlesAsRss($articles, $page_title);
						
						break;
						
					case 'most_popular':
						list($results, $null) = DAO_KbArticle::search(
							array(
								new DevblocksSearchCriteria(SearchFields_KbArticle::CODE,'=',$this->getPortal())
							),
							25,
							0,
							SearchFields_KbArticle::VIEWS,
							false,
							false
						);
						
						if(is_array($results) && !empty($results))
						$full_articles = DAO_KbArticle::getWhere(sprintf("%s IN (%s)",
							DAO_KbArticle::ID,
							implode(',', array_keys($results))
						));

						$order= array_keys($results);
						$articles = array();
						foreach($order as $id) {
							$articles[$id] =& $full_articles[$id];
						}
						
						$this->_writeArticlesAsRss($articles, $page_title);
													
						break;
					
					case 'search':
						$query = rawurldecode(array_shift($stack));
						
						$articles = $this->_searchIndex($query);

						$this->_writeArticlesAsRss($articles, $page_title);
						
						break;
					
					case 'article':
						$id = intval(array_shift($stack));
		
						$articles = DAO_KbArticle::getWhere(sprintf("%s = %d AND %s = '%s'",
							DAO_KbArticle::ID,
							$id,
							DAO_KbArticle::CODE,
							$this->getPortal()
						));

						$this->_writeArticlesAsRss($articles, $page_title);
						
						break;
						
					// [TODO] [JAS]: This is pretty redundant with the browse code below
					case 'tags':
			    		// ----
			    		$cg = DevblocksPlatform::getCloudGlueService();
			    		$cfg = new CloudGlueConfiguration();
			    		$cfg->indexName = $this->_getTagIndex();
			    		$cfg->divName = 'kbTagCloud'; // [TODO] Make optional (move to render)
			    		$cfg->extension = 'kb'; // [TODO] Make optional (move to render)
			    		$cfg->php_click = '_'; // [TODO] Make optional (move to render)
						$cfg->maxWeight = 36;
						$cfg->minWeight = 12;
			    		$cloud = $cg->getCloud($cfg);
			    		// ----
		
			    		if(!empty($stack)) {
			    			$articles = array();
			    			$tag_str = rawurldecode(array_shift($stack));
			    			$tags = explode('+', $tag_str);
			    			
			    			foreach($tags as $tag_name) {
				    			if(null != ($tag = DAO_CloudGlue::lookupTag($tag_name,false)))
					    			$cloud->addToPath($tag);
			    			}
			    			
							if(null != ($ids = DAO_CloudGlue::getTagContentIds($this->_getTagIndex(), $cloud->getPath()))) {
								$articles = DAO_KbArticle::getWhere(sprintf("%s IN (%s) AND %s = '%s'",
									DAO_KbArticle::ID,
									implode(',', $ids),
									DAO_KbArticle::CODE,
									$this->getPortal()
								));
							}
							
							$this->_writeArticlesAsRss($articles, $page_title);
			    		}	    		
			    		
						break;
				}
				
				break;
				
			case 'search':
				@$query = urldecode(array_shift($stack));
				
				$session = $this->getSession();
				
				if(!empty($query)) {
					$session->setProperty('last_query', $query);
				} else {
					$query = $session->getProperty('last_query', '');
				}

				$articles = $this->_searchIndex($query);
				
				$tpl->assign('query', $query);
				$tpl->assign('articles', $articles);

				$tpl->display('file:' . dirname(__FILE__) . '/templates/portal/kb/search.tpl.php');
				break;
			
			case 'edit':
				if(empty($editor)) break;
				$id = intval(array_shift($stack));

				$articles = DAO_KbArticle::getWhere(sprintf("%s = %d AND %s = '%s'",
					DAO_KbArticle::ID,
					$id,
					DAO_KbArticle::CODE,
					$this->getPortal()
				));
				@$article = $articles[$id];
				$tpl->assign('article', $article);
				
				$tags = DAO_CloudGlue::getTagsOnContents($id, $this->_getTagIndex());
				$tpl->assign('tags', @$tags[$id]);
				
				$tpl->display('file:' . dirname(__FILE__) . '/templates/portal/kb/article_edit.tpl.php');
				break;
			
			case 'config':
				if(empty($editor))
					break;
				
				$tpl->display('file:' . dirname(__FILE__) . '/templates/portal/kb/public_config/index.tpl.php');
				break;
				
			case 'import':
				if(empty($editor))
					break;
				
				$tpl->display('file:' . dirname(__FILE__) . '/templates/portal/kb/public_config/import.tpl.php');
				break;
				
			case 'reindex':
				if(empty($editor)) break;

				$index = $this->_getSearchIndex();
				
				// Nuke all the existing documents
				if(0 != ($len = $index->count())) {
					for($x=0;$x<$len;$x++) {
						if(!$index->isDeleted($x))
							$index->delete($x);
					}
				}
				
				// Purge
				$index->optimize();
				
				list($articles,$null) = DAO_KbArticle::search(
					array(
					),
					-1,
					0,
					null,
					null,
					false
				);
				
				// Add back fresh content
				foreach($articles as $article_id => $article) {
					$doc = new Zend_Search_Lucene_Document();
					$doc->addField(Zend_Search_Lucene_Field::Text('article_id', $article_id));
					$doc->addField(Zend_Search_Lucene_Field::Text('title', $article[SearchFields_KbArticle::TITLE]));
					$doc->addField(Zend_Search_Lucene_Field::UnStored('contents', $article[SearchFields_KbArticle::CONTENT]));		
					$index->addDocument($doc);
				}
				
				$tpl->display('file:' . dirname(__FILE__) . '/templates/portal/kb/public_config/reindex.tpl.php');
				break;
				
			case 'article':
				$id = intval(array_shift($stack));

				$articles = DAO_KbArticle::getWhere(sprintf("%s = %d AND %s = '%s'",
					DAO_KbArticle::ID,
					$id,
					DAO_KbArticle::CODE,
					$this->getPortal()
				));
				@$article = $articles[$id];
				$tpl->assign('article', $article);

				@$article_list = $umsession->getProperty(self::SESSION_ARTICLE_LIST, array());
				if(!empty($article) && !isset($article_list[$id])) {
					DAO_KbArticle::update($article->id, array(
						DAO_KbArticle::VIEWS => ++$article->views
					));
					$article_list[$id] = $id;
					$umsession->setProperty(self::SESSION_ARTICLE_LIST, $article_list);
				}
				
				@$tags = array_shift(DAO_CloudGlue::getTagsOnContents($id, $this->_getTagIndex()));
				if(!empty($tags)) {
					$parts = array();
					foreach($tags as $tag) {
						$parts[] = $tag->name;
					}
					$tpl->assign('location', rawurlencode(implode('+', $parts)));
					$tpl->assign('tags', $tags);
				}
				
		    	$tpl->display('file:' . dirname(__FILE__) . '/templates/portal/kb/article.tpl.php');
				break;
			
			case 'login':
		    	$tpl->display('file:' . dirname(__FILE__) . '/templates/portal/kb/login.tpl.php');
				break;

			case 'browse':
	    	default:
	    		// ----
	    		$cg = DevblocksPlatform::getCloudGlueService();
	    		$cfg = new CloudGlueConfiguration();
	    		$cfg->indexName = $this->_getTagIndex();
	    		$cfg->divName = 'kbTagCloud'; // [TODO] Make optional (move to render)
	    		$cfg->extension = 'kb'; // [TODO] Make optional (move to render)
	    		$cfg->php_click = '_'; // [TODO] Make optional (move to render)
				$cfg->maxWeight = 36;
				$cfg->minWeight = 12;
	    		$cloud = $cg->getCloud($cfg);
	    		// ----

	    		if(!empty($stack)) {
	    			$tag_str = rawurldecode(array_shift($stack));
	    			$tags = explode('+', $tag_str);
	    			foreach($tags as $tag_name) {
		    			if(null != ($tag = DAO_CloudGlue::lookupTag($tag_name,false)))
			    			$cloud->addToPath($tag);
	    			}
	    			$tpl->assign('tags_prefix', $tag_str);
	    			
					if(null != ($ids = DAO_CloudGlue::getTagContentIds($this->_getTagIndex(), $cloud->getPath()))) {
						$articles = DAO_KbArticle::getWhere(sprintf("%s IN (%s) AND %s = '%s'",
							DAO_KbArticle::ID,
							implode(',', $ids),
							DAO_KbArticle::CODE,
							$this->getPortal()
						));
						$tpl->assign('articles', $articles);
					}
	    		}	    		
	    		
	    		$tpl->assign('cloud', $cloud);
	    		
   				$tpl->display('file:' . dirname(__FILE__) . '/templates/portal/kb/index.tpl.php');
		    	break;
		}
	}
	
	/**
	 * @param $instance Model_CommunityTool 
	 */
    public function configure(Model_CommunityTool $instance) {
        $tpl = DevblocksPlatform::getTemplateService();
        $tpl_path = realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR;
        $tpl->assign('config_path', $tpl_path);
        
        $logo_url = DAO_CommunityToolProperty::get($this->getPortal(), self::PARAM_LOGO_URL, '');
		$tpl->assign('logo_url', $logo_url);
        
        $page_title = DAO_CommunityToolProperty::get($this->getPortal(), self::PARAM_PAGE_TITLE, 'Knowledgebase');
		$tpl->assign('page_title', $page_title);
        
        $captcha_enabled = DAO_CommunityToolProperty::get($this->getPortal(), self::PARAM_CAPTCHA_ENABLED, 1);
		$tpl->assign('captcha_enabled', $captcha_enabled);
        
		$sEditors = DAO_CommunityToolProperty::get($this->getPortal(),self::PARAM_EDITORS, '');
        $editors = !empty($sEditors) ? unserialize($sEditors) : array();
		
        $tpl->assign('editors', $editors);
		
        $tpl->display("file:${tpl_path}portal/kb/config/index.tpl.php");
    }
    
    public function saveConfiguration() {
        @$sLogoUrl = DevblocksPlatform::importGPC($_POST['logo_url'],'string','');
        @$sPageTitle = DevblocksPlatform::importGPC($_POST['page_title'],'string','Contact Us');
        @$iCaptcha = DevblocksPlatform::importGPC($_POST['captcha_enabled'],'integer',1);
//        @$sThemeUrl = DevblocksPlatform::importGPC($_POST['theme_url'],'string','');

        DAO_CommunityToolProperty::set($this->getPortal(), self::PARAM_LOGO_URL, $sLogoUrl);
        DAO_CommunityToolProperty::set($this->getPortal(), self::PARAM_PAGE_TITLE, $sPageTitle);
        DAO_CommunityToolProperty::set($this->getPortal(), self::PARAM_CAPTCHA_ENABLED, $iCaptcha);
//        DAO_CommunityToolProperty::set($this->getPortal(), self::PARAM_THEME_URL, $sThemeUrl);
        
        @$sEditorEmail = DevblocksPlatform::importGPC($_POST['editor_email'],'string','');
        @$sEditorPass = DevblocksPlatform::importGPC($_POST['editor_pass'],'string','');
        @$aEditorsEmail = DevblocksPlatform::importGPC($_POST['editors_email'],'array',array());
        @$aEditorsPass = DevblocksPlatform::importGPC($_POST['editors_pass'],'array',array());
        @$aEditorsDelete = DevblocksPlatform::importGPC($_POST['editors_delete'],'array',array());

        @$aEditorsDelete = array_flip($aEditorsDelete); // values to keys
        
        $sEditors = DAO_CommunityToolProperty::get($this->getPortal(),self::PARAM_EDITORS, '');
        $editors = !empty($sEditors) ? unserialize($sEditors) : array();
        
        // Adding
        if(!empty($sEditorEmail) && !empty($sEditorPass)) {
        	$editors[$sEditorEmail] = array(
        		'email' => $sEditorEmail,
        		'password' => md5($sEditorPass)
        	);
        }
        
        // Modifying/Deleting
		foreach($aEditorsEmail as $idx => $email) {
			if(isset($aEditorsDelete[$email])) { // Deleting
				unset($editors[$email]);
				
			} elseif (!empty($aEditorsPass[$idx])) { // Modifying
				$editors[$email] = array(
					'email' => $email,
					'password' => md5($aEditorsPass[$idx])
				);
				
			}
		}

        DAO_CommunityToolProperty::set($this->getPortal(), self::PARAM_EDITORS, serialize($editors));
    }
    
    public function doSearchAction() {
		@$query = DevblocksPlatform::importGPC($_REQUEST['query'],'string','');
		@$match = DevblocksPlatform::importGPC($_REQUEST['match'],'string','');
    	
		$session = $this->getSession();
		$session->setProperty('last_query', $query);
		$session->setProperty('last_query_type', $match);
		
		DevblocksPlatform::setHttpResponse(new DevblocksHttpResponse(array('portal',$this->getPortal(),'search')));
    }
    
    public function doLoginAction() {
		@$editor_email = DevblocksPlatform::importGPC($_REQUEST['editor_email'],'string','');
		@$editor_pass = DevblocksPlatform::importGPC($_REQUEST['editor_pass'],'string','');
    	
        $sEditors = DAO_CommunityToolProperty::get($this->getPortal(),self::PARAM_EDITORS, '');
        $editors = !empty($sEditors) ? unserialize($sEditors) : array();
		
        @$editor =& $editors[$editor_email]; 
		if(!empty($editor) && $editor['password']==md5($editor_pass)) {
			$session = $this->getSession(); /* @var $session Model_CommunitySession */
			$session->setProperty(self::SESSION_EDITOR, $editor_email);
			
			DevblocksPlatform::setHttpResponse(new DevblocksHttpResponse(array('portal',$this->getPortal())));
		} else {
			DevblocksPlatform::setHttpResponse(new DevblocksHttpResponse(array('portal',$this->getPortal(),'login')));
		}		
    }
    
    public function doLogoutAction() {
    	$umsession = $this->getSession();
    	$umsession->setProperty(self::SESSION_EDITOR, null);
    	
    	DevblocksPlatform::setHttpResponse(new DevblocksHttpResponse(array('portal',$this->getPortal())));
    }
    
    public function doImportAction() {
    	@$import_file = $_FILES['import_file'];
    	
    	if(!empty($import_file)) {
    		$xmlstr = file_get_contents($import_file['tmp_name']);
    		
    		// [TODO] Parse XML
			$xml = new SimpleXMLElement($xmlstr);
			
			foreach($xml->articles->article AS $article) {
				$title = (string) $article->title;
				$content = (string) $article->content;

				$fields = array(
						DAO_KbArticle::CODE => $this->getPortal(),
						DAO_KbArticle::TITLE => $title,
						DAO_KbArticle::CONTENT => $content,
				);
				$id = DAO_KbArticle::create($fields);
				
				$tags = array();
				if(!empty($article->categories->category))
				foreach($article->categories->category AS $category) {
					$tags[] = (string) $category;
				}

				if(!empty($tags)) {
					DAO_CloudGlue::applyTags($tags, $id, $this->_getTagIndex(), false); // 4th argument = replace
				}
			}
    	}
    }
    
    public function doArticleEditAction() {
    	// Permissions
		$session = $this->getSession(); /* @var $session Model_CommunitySession */
		$editor = $session->getProperty(self::SESSION_EDITOR, null);
		if(empty($editor)) {
			die("Access denied.");
		}
		
		$index = $this->_getSearchIndex();
    	
    	@$id = DevblocksPlatform::importGPC($_POST['id'],'integer',0);
    	@$title = DevblocksPlatform::importGPC($_POST['title'],'string','No article title');
    	@$content = DevblocksPlatform::importGPC($_POST['content'],'string','');
    	@$delete = DevblocksPlatform::importGPC($_POST['do_delete'],'integer',0);
    	@$tags_csv = DevblocksPlatform::importGPC($_POST['tags'],'string','');
    	
		$fields = array(
			DAO_KbArticle::TITLE => $title,
			DAO_KbArticle::CODE => $this->getPortal(),
			DAO_KbArticle::UPDATED => time(),
			DAO_KbArticle::CONTENT => $content,
		);
    	
    	if(empty($id)) { // insert
			$id = DAO_KbArticle::create($fields);
    		
    	} else { // edit
			// Clear any copies of this document out before editing
			if(0 != ($len = $index->count())) {
				for($x=0;$x<$len;$x++) {
					$hit = $index->getDocument($x);
					if(!$index->isDeleted($x) && $hit->article_id==$id)
						$index->delete($x);
				}
			}
			 		
			 if(!empty($delete)) {
			 	$tags = array();
			 	
			 	@$set_tags = array_shift(DAO_CloudGlue::getTagsOnContents(array($id),$this->_getTagIndex()));
			 	if(is_array($set_tags) && !empty($set_tags)) {
				 	foreach($set_tags as $tag) {
				 		$tags[] = $tag->name;
				 	}
				 	$tag_list = rawurlencode(implode('+', $tags));
			 	}
			 	
			 	DAO_KbArticle::delete($id);
			 	
			 	// [JAS]: Send the user back somewhere useful if we can
			 	if(!empty($tag_list)) {
			 		DevblocksPlatform::setHttpResponse(new DevblocksHttpResponse(array('portal',$this->getPortal(),'browse',$tag_list)));
			 	} else {
			 		DevblocksPlatform::setHttpResponse(new DevblocksHttpResponse(array('portal',$this->getPortal())));
			 	}
			 	return;
			 	
			 } else {
			 	DAO_KbArticle::update($id, $fields);
			 }
			
    	}
    	
    	// Tagging
    	$tags = CerberusApplication::parseCsvString($tags_csv);
		DAO_CloudGlue::applyTags($tags, $id, $this->_getTagIndex(), true);
    	
		// Search Indexing
		$doc = new Zend_Search_Lucene_Document();
		$doc->addField(Zend_Search_Lucene_Field::Text('article_id', $id));
		$doc->addField(Zend_Search_Lucene_Field::Text('title', $title));
		$doc->addField(Zend_Search_Lucene_Field::UnStored('contents', $content));		
		
		$index->addDocument($doc);
		
    	DevblocksPlatform::setHttpResponse(new DevblocksHttpResponse(array('portal',$this->getPortal(),'article',$id)));
    }

    /**
     * @return Zend_Search_Lucene_Interface
     */
    private function _getSearchIndex() {
    	$path = APP_PATH . '/storage/indexes/kb-'.$this->getPortal();
    	
    	// Allow Numeric Text
    	Zend_Search_Lucene_Analysis_Analyzer::setDefault(
    		new Zend_Search_Lucene_Analysis_Analyzer_Common_TextNum_CaseInsensitive()
    	);
    	
    	if(!is_dir($path)) {
    		$index = Zend_Search_Lucene::create($path);
    	} else {
    		$index = Zend_Search_Lucene::open($path);
    	}
    	
    	return $index;
    }
    
    private function _searchIndex($query) {
    	try {
			$index = $this->_getSearchIndex();
			$hits = $index->find($query);
    	} catch(Exception $e) {
    		return array();
    	}
		
		$articles = array();
		
		foreach($hits as $hit) { /* @var $hit Zend_Search_Lucene_Search_QueryHit */
			@$article = DAO_KbArticle::get($hit->article_id);
			if(!empty($article) && 0 == strcasecmp($article->code,$this->getPortal())) {
				$article->score = $hit->score;
				$articles[$hit->article_id] = $article;
			}
		}
		
		return $articles;
    }
    
    public function getTagAutoCompletionsAction() {
    	@$starts_with = DevblocksPlatform::importGPC($_REQUEST['query'],'string','');
    	$tags = DAO_CloudGlue::getTagsWhere(sprintf("name LIKE '%s%%'", $starts_with));
		
		foreach($tags AS $val){
			echo $val->name . "\t";
			echo $val->id . "\n";
		}
		exit();
    }
    
};

?>