<?php
include_once(BASEDIR."/includes/class.backend.php");
function run(){
    global $proj, $user;
    
    $visible = explode(' ',
	trim($proj->id ? $proj->prefs['visible_columns'] : $fs->prefs['visible_columns']));

    $lim = (Req::val('limit') ? Req::val('limit') : APIDEFLIM);
    $res = Backend::get_task_list($_REQUEST,$visible,0,$lim);

    API::cleanAss($res);
    return $res;
}
?> 
