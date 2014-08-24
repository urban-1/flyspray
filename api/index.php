<?php
/**
 * @file
 * Main API handler...
 * 
 * @author Andreas Bontozoglou
 */
define('IN_FS', true);
require_once(dirname(dirname(__FILE__)).'/header.php');
require_once('class.api.php');

// --- Get available actions
$actions = str_replace('.php', '', 
	array_map('basename', glob_compat(BASEDIR ."/api/actions/*.php")));


// --- Check request
if (!Req::val('user')) API::throwErrorExit("User is required");
if (!Req::val('action')) API::throwErrorExit("Action is required");

// --- Load user
$sql = $db->Query('SELECT u.user_id FROM {users} u WHERE u.user_name=? LIMIT 1',
		    array(Req::val('user')));

if ($db->countRows($sql)==0) 
    API::throwErrorExit("Invalid user?!");

$uid = $db->FetchOne($sql);
$uid = $uid[0];

$user = new User($uid);

// --- Get project
// TODO: Support list projects before this point
if (!Req::val('proj')) API::throwErrorExit("Project is required");
$proj =  new Project(Req::val('proj'));


// --- Authenticate
if (!$user->can_view_project(Req::val('proj')))
    API::throwErrorExit("Project not available for u");

// --- Sort out actions
$actId = array_search(Req::val('action'), $actions);
if ($actId===false) 
    API::throwErrorExit("No such action!");

// Run the script's run() function to get back an array
include_once(BASEDIR."/api/actions/".Req::val('action').".php");
$res =  run();


// --- handle printing 
// TODO: Api function to format JSON, JSONP, XML, CSV etc...
API::printResponse($res);

?>