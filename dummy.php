<?php
require(getcwd() . '/framework.config.php');
require(UM_PATH . '/libs/ump/UserMeetPlatform.class.php');
require(UM_PATH . '/api/CerberusApplication.class.php');

// Teams
CerberusApplication::createTeam('Support');
CerberusApplication::createTeam('Sales');
CerberusApplication::createTeam('Development');
CerberusApplication::createTeam('Marketing');

// Mailboxes
CerberusApplication::createMailbox('Trial Keys');
CerberusApplication::createMailbox('Leads');
CerberusApplication::createMailbox('Hosting Support');
CerberusApplication::createMailbox('Bugs');
CerberusApplication::createMailbox('Wishlist');

// Dashboards
//$newId = $um_db->GenID('generic_seq');

// Views
//$newId = $um_db->GenID('generic_seq');

// Tickets
CerberusTicketDAO::createTicket('FCX-29293-291','Where is my order?','open','jstanden@gmail.com');
CerberusTicketDAO::createTicket('KJS-94372-874','How do I use the email parser?','open','jeff@webgroupmedia.com');
CerberusTicketDAO::createTicket('NDJ-48300-482','SSL Certificate Expiration Reminder for billing.webgroupmedia.com','open','customer@localhost');

// Agents


?>