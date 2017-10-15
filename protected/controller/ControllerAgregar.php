<?php
class controllerAgregar extends Controller {
    function __construct($view, $conf, $var, $acc) {
        parent::__construct($view, $conf, $var, $acc);
    } 
    public function main() {
        $this->data["accion"] = "Agregar";
        $this->data["nameTable"] = indexModel::bd($this->conf)->getEstructuraTable($dominio)["structure"]["nameTable"];
        $this->data["dominio"] = $this->var["Dominio"];
        $this->data["campos"] = indexModel::bd($this->conf)->getcamposAll($this->var["Dominio"]);
        $this->data["isImg"] = indexModel::bd($this->conf)->getEstructuraTable($this->var["Dominio"])["structure"]["img"];
        $this->data["isPDF"] = indexModel::bd($this->conf)->getEstructuraTable($this->var["Dominio"])["structure"]["pdf"];
        $this->view->show("addCatalogo.html", $this->data, $this->accion); 
    }
}
?>