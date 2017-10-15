<?php
$debug = false;
if($_SERVER["SERVER_NAME"]=="localhost"){
    $debug = true;
}
if($debug){
    return array(
        'title'=>'Framework DIXI',
        'connectionString' => 'mysql',
        'host' => 'localhost',
        'dbname' => 'bddixi',
        'username' => 'root',
        'password' => '',
        'folderControladores' => 'protected/controller/',
        'folderModelos' => 'protected/model/',
        'folderVistas' => 'protected/views/',
        'pathSite' => 'http://'.$_SERVER["SERVER_NAME"].'/not-framework-dixi/', 
        'pathCMSSite' => 'http://'.$_SERVER["SERVER_NAME"].'/not-framework-dixi/',
        'timezone' => 'America/Mexico_City'
    );
} else {
    return array(
        'title'=>'Framework DIXI',
        'connectionString' => 'mysql',
         'host' => 'localhost',
        'dbname' => '',
        'username' => '',
        'password' => '',
        'folderControladores' => 'protected/controller/',
        'folderModelos' => 'protected/model/',
        'folderVistas' => 'protected/views/',
        'pathSite' => 'http://framework.dixi-project.com/',
        'pathCMSSite' => 'http://framework.dixi-project.com/admin',
        'timezone' => 'America/Mexico_City'
    );
}
?>