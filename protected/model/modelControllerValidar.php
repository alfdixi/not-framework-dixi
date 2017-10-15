<?php
class modelControllerValidar extends indexModel{

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

    public function validarMail($data) {
        $res=null;
        foreach ($data as $key => $value) {
            //echo $key ."--". $value."<br>";
            $$key = $value;
        }
        $getCl = substr($con, 8);
        //echo $getCl."<br>";
        $extr = base64_decode($getCl);
        //echo "D:".$extr."<br>";
        $dd = explode("||", $extr);
        //var_dump($dd);
        $nn = $this->desencriptar($dd[0]);
        //echo "NN:".$nn;
        /*
        $usuario = (object) indexModel::bd($this->conf)->getDominio("user",$nn)[0];
        var_dump($usuario);
        $llave = base64_decode($usuario->llave);
        echo "Llave:".$llave."<br>";
        $des = $this->desencriptar($dd[1], $llave);
        echo $des;
        exit();
         */
        
        
        
        $datoss=array(
            "Dominio"=>"user",
            "txtstatus_usuario_id"=>1
        );
        
        $res = indexModel::bd($this->conf)->updateDominio($datoss,$nn);
        if(!is_numeric($nn)){
            $res = 0;
        }
        //exit();
        return $res;
    }
    private function linkValidaCorreo($ciphertext_base64,$key,$iv_size){
        $ciphertext_dec = base64_decode($ciphertext_base64);
        # recupera la IV, iv_size debería crearse usando mcrypt_get_iv_size()
        $iv_dec = substr($ciphertext_dec, 0, $iv_size);
        # recupera el texto cifrado (todo excepto el $iv_size en el frente)
        $ciphertext_dec = substr($ciphertext_dec, $iv_size);
        # podrían eliminarse los caracteres con valor 00h del final del texto puro
        $plaintext_dec = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $ciphertext_dec, MCRYPT_MODE_CBC, $iv_dec);
        return $plaintext_dec;
    }
/*
    private function desencriptar($cadena){
        $key='';  // Una clave de codificacion, debe usarse la misma para encriptar y desencriptar
        $decrypted = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($key), base64_decode($cadena), MCRYPT_MODE_CBC, md5(md5($key))), "\0");
       return $decrypted;  //Devuelve el string desencriptado
   }
   */
   public function desencriptar($cadena,$key=""){
         //$key='';  // Una clave de codificacion, debe usarse la misma para encriptar y desencriptar
         $decrypted = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($key), base64_decode($cadena), MCRYPT_MODE_CBC, md5(md5($key))), "\0");
        return $decrypted;  //Devuelve el string desencriptado
    }
}
?>