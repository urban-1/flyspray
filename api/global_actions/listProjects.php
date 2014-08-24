<?php

/**
 * Return a list of projects
 */
function run(){
    global $user;
    $res = Flyspray::listProjects();
    API::cleanAss($res);
    return $res;
}
?>