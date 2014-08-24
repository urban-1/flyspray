<?php
/**
 * @file
 * Main API handler performing
 * 
 * @author Andreas Bontozoglou
 */
define('IN_FS', true);
require_once(dirname(dirname(__FILE__)).'/header.php');
require_once('class.api.php');



// --- Get available actions
// Project based
$actions = str_replace('.php', '', 
	array_map('basename', glob_compat(BASEDIR ."/api/actions/*.php")));

$g_actions = str_replace('.php', '', 
	array_map('basename', glob_compat(BASEDIR ."/api/global_actions/*.php")));

// --- Check request
API::buildRequest();
API::checkRequest();

// Check action type
$type="";
if (array_search(Req::val('action'), $actions)!==false) 
    $type="actions"; 
else if (array_search(Req::val('action'), $g_actions)!==false) 
    $type="global_actions";

// Check for missing action
if ($type=="") API::throwErrorExit("No such action!");

// --- Load/Authenticate user
if (($uid = Flyspray::checkLogin(Req::val('user'), Req::val('pass'))) < 1) {
    API::throwErrorExit("Auth failed");
}
$user = new User($uid);

// --- Get project
if ($type=="actions") {
    if (!Req::val('proj')) 
	API::throwErrorExit("Project is required");

    $proj =  new Project(Req::val('proj'));


    // --- Check project permissions
    if (!$user->can_view_project(Req::val('proj')))
	API::throwErrorExit("Project not available for u");
}

// Run the script's run() function to get back an array
include_once(BASEDIR."/api/$type/".Req::val('action').".php");
$res =  run();


// --- handle printing 
// TODO: Api function to format JSON, JSONP, XML, CSV etc...
API::printResponse($res);

?>