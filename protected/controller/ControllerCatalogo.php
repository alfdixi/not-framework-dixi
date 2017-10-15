<?php
class ControllerCatalogo extends Controller {
    function __construct($view, $conf, $var, $acc) {
        parent::__construct($view, $conf, $var, $acc);
    } 
    public function main(){
        //indexModel::bd($this->conf)->controlAcceso(["0"]);
        $dat = explode("/", $this->var["con"]);
        $dominio = $dat[1];
        $this->data["activeRol"] = "sfActive";
        $this->data["nameTable"] = indexModel::bd($this->conf)->getEstructuraTable($dominio)["structure"]["nameTable"];
        $this->data["isImg"] = indexModel::bd($this->conf)->getEstructuraTable($dominio)["structure"]["img"];
        $this->data["dominio"] = $dominio;
        $this->data["campos"] = indexModel::bd($this->conf)->getcampos($dominio);
        $this->data["datos"] = indexModel::bd($this->conf)->getDominio($dominio);
        asort($this->data["datos"]);
        $this->view->show("adminCatalogo.html", $this->data, $this->accion); 
    }
}
?>