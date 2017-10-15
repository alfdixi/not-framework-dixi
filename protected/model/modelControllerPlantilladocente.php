<?php
class modelControllerPlantilladocente extends indexModel{

    public $db;
    private $host;
    private $bd;
    private $user;
    private $clave;
    private $conf;

    function __construct($conf) {
        require_once $conf['folderModelos'] . 'SPDO.php';
        $this->bd = $conf['dbname'];
        $this->conf = $conf;
        $this->db = SPDO::singleton($conf['host'], $this->bd, $conf['username'], $conf['password']);
    }

    
}
?>