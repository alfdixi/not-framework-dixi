<?php
class modelControllerRegistro extends indexModel{

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

    public function crearUser($data) {
        foreach ($data as $key => $value) {
            //echo $key ."--". $value."<br>";
            $$key = $value;
        }
        // --> Crear Usuario
        $datoss=array(
            "Dominio"=>"user",
            "txtnombre"=>strtoupper($NtxtName),
            "txtcorreo"=>  strtolower($NtxtMail),
            "txtpassword"=>$NtxtPassword,
            "txtrol_id"=>1,
            "txtstatus_usuario_id"=>0,
        );
        //var_dump($datoss);
        $idUser = indexModel::bd($this->conf)->updateDominio($datoss);
        //echo "ID:".$idUser;
        //exit();
        // --> Crear Empresa
        $datos=array(
            "Dominio"=>"empresa",
            "txtempresa"=>  strtoupper($NtxtNameSmart),
            "txtgiro_id"=>$NtxtGiro,
            "txtnombre_app"=>$NtxtNameAPP,
            "txtstatus_empresa_id"=>0, 
        );
        $idEmpresa = indexModel::bd($this->conf)->updateDominio($datos);
        
        // --> Usuario Empresa
        $dato=array(
            "Dominio"=>"user_has_empresa",
            "txtuser_rel_id"=>$idUser,
            "txtempresa_id"=>$idEmpresa
        );
        indexModel::bd($this->conf)->updateDominio($dato);
        
        $validaCorreo = $this->linkValidaCorreo($idUser,$NtxtMail);
        // --> Mandar Mail
        
        $settings = (object) indexModel::bd($this->conf)->getDominio("settings")[0];
        $mensajeHTML = '<b>'.strtoupper($NtxtName).'</b><br>
            <p>Tu suscripción y tu dirección de correo electrónico están por ser confirmadas se ha añadido a la lista admin_notify. Solo verifique su correo a través de la siguiente link <a href="'.$validaCorreo.'" target="_blank" title="Verifica Aquí" >Verifica Aquí</a>.
</p>
<p>o puede usted copiar y pega en un navegador la siguiente linea.<br><br>'.$validaCorreo.'</p>
<p>
<b><a href="http://notify.com.mx/" target="_blank" title="Notify">Notify</a></b><br>
Administradores de Notify<p>';
        
        //echo $mensajeHTML;
        //exit();
        if($settings->type_mail_id==1){
            $this->sendMail($NtxtMail, $NtxtName, "Gracias por suscribirse!", utf8_decode($mensajeHTML), $opc = 0);
        }elseif($settings->type_mail_id==2){
            //$this->sendMailGetResponse($NtxtMail, $NtxtName, "Gracias por suscribirse!", $mensajeHTML, $opc = 0);
        }elseif($settings->type_mail_id==3){
            //$this->sendMailAweber($NtxtMail, $NtxtName, "Gracias por suscribirse!", $mensajeHTML, $opc = 0);
        }
        return $idUser;
    }
    private function linkValidaCorreo($idUser,$correo){
        //echo $idUser."--".$correo."<br>";
        $cad = NULL;
        $opciones = ['cost' => $idUser];
        $key = md5(microtime().rand());//password_hash(md5(microtime().rand()), PASSWORD_BCRYPT, $opciones);
        //echo "KEY:".$key."<br>";
        $key = pack('H*', $key);
        //echo "KEY:".$key."<br>";
        $llaveID = $this->encriptar($idUser);
        
        $ciphertext = $this->encriptar($correo,$key);
        //$key_size =  strlen($key);
        //$plaintext = $correo;
        //$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
        //$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        //$ciphertext = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $plaintext, MCRYPT_MODE_CBC, $iv);
        //echo $ciphertext."<br>"; 
        //$ciphertext = base64_encode($iv . $ciphertext);
        //$ciphertext = base64_encode($ciphertext);
        //echo $llaveID."||".$ciphertext."<br>"; 
        
        //$decrypted = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_128, md5($key), base64_decode($ciphertext), MCRYPT_MODE_CBC, md5(md5($key))), "\0");
        //$decrypted = $this->desencriptar($key,$ciphertext);
        //echo $decrypted."<br>"; 
        //exit();
        
        
        
        $ciphertext_base64 = base64_encode($llaveID."||".$ciphertext);
        $cad = "http://admin.notify.com.mx/validar/".$ciphertext_base64;
        //echo  $cad . "<hr>";
        //echo $iv_size."||".$key."<br>";
        $llave = base64_encode($key);
        $datoss=array(
            "Dominio"=>"user",
            "txtllave"=>$llave
        );
        //var_dump($datoss);
        //exit();
        indexModel::bd($this->conf)->updateDominio($datoss,$idUser);
        return $cad;
    }
    public function encriptar($cadena,$key=""){
        //$key='';  // Una clave de codificacion, debe usarse la misma para encriptar y desencriptar
        $encrypted = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($key), $cadena, MCRYPT_MODE_CBC, md5(md5($key))));
        return $encrypted; //Devuelve el string encriptado

    }

    
    /*
    private function encriptar($cadena){
        $key='';  // Una clave de codificacion, debe usarse la misma para encriptar y desencriptar
        $encrypted = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($key), $cadena, MCRYPT_MODE_CBC, md5(md5($key))));
        return $encrypted; //Devuelve el string encriptado

    }
    */
    
}
?>