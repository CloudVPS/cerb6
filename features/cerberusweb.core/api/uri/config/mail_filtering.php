<?php
/***********************************************************************
| Cerb(tm) developed by Webgroup Media, LLC.
|-----------------------------------------------------------------------
| All source code & content (c) Copyright 2013, Webgroup Media LLC
|   unless specifically noted otherwise.
|
| This source code is released under the Devblocks Public License.
| The latest version of this license can be found here:
| http://cerberusweb.com/license
|
| By using this software, you acknowledge having read this license
| and agree to be bound thereby.
| ______________________________________________________________________
|	http://www.cerberusweb.com	  http://www.webgroupmedia.com/
***********************************************************************/

class PageSection_SetupMailFiltering extends Extension_PageSection {
	function render() {
		$tpl = DevblocksPlatform::getTemplateService();
		$visit = CerberusApplication::getVisit();

		$visit->set(ChConfigurationPage::ID, 'mail_filtering');
		
		$context = 'cerberusweb.contexts.app';
		$context_id = 0;
		
		// Events
		
		$events = Extension_DevblocksEvent::getByContext($context, false);
		$tpl->assign('events', $events);
		
		// Virtual Attendanta
		
		$vas = DAO_VirtualAttendant::getByOwner($context, $context_id);
		
		foreach($vas as $va_id => $va) {
			$behaviors = $va->getBehaviors('event.mail.received.app', true);
			
			$vas[$va_id]->behaviors = array(
				'event.mail.received.app' => $behaviors,
			);
		}
		
		$tpl->assign('vas', $vas);
		
		$tpl->display('devblocks:cerberusweb.core::configuration/section/mail_filtering/index.tpl');
	}
}
