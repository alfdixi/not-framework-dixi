<?php
class controllerLogin {
    function __construct($view, $conf, $var, $acc) {
        $this->view = $view;
        $this->conf = $conf;
        $this->var = $var;
        $this->accion = $acc;
    }
    public function main() {
        $data=null;
        $usu = $this->var["username"];
        $pass = $this->var["pwd"];
        $res = indexModel::bd($this->conf)->validarAcceso($usu,$pass);
        $dd = explode("|", $res);
        if($dd[0]>0){
            $data["isCorrect"] = TRUE;
            $data["tituloMensaje"] = "Acceso correcto.";
            $data["Mensaje"] = "El usuario es valido.";
            if($dd[1]==1){// --> Administrador
                $data["return"] = $this->conf["pathCMSSite"]."home";
            }
            
            $data["tiempo"] = "3";
        }else{
            $data["isCorrect"] = FALSE;
            $data["tituloMensaje"] = "Error en el login.";
            $data["Mensaje"] = "El usuario o contaseña son incorectos o el usuario aun no es validado.";
            $data["return"] = $this->conf["pathCMSSite"];
            $data["tiempo"] = "3";
        }
        $data["return"]=indexModel::bd($this->conf)->getMensaje($data);
        $templa  = "mensajeBackEnd.html";
        $this->view->show($templa, $data, $this->accion); 
    }
}
?>