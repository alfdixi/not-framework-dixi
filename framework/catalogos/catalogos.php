<?php
class Catalogos {
    public function __construct() {
        
    }
    public static function getRelacionTable($db, $nameBase, $table,$id=null){
        $cad="";
        $ss="SHOW TABLES";
        $recordset = $db->prepare($ss);
        $recordset->execute();
        $o = 0;
        while ($item1 = $recordset->fetch(PDO::FETCH_OBJ)) {
            $campo = "Tables_in_".$nameBase;
            $nombreTabla = $item1->$campo;
            $numCarac = strlen($table)+4;
            if($table."_has" == substr($nombreTabla,0,$numCarac)){
                $nom = explode("_has_", $nombreTabla);
                if(is_null($id)){
                    $registrosActuales=null;
                }else{
                    $campos = array($nom[0]."_id"=>$id);
                    $registrosActuales=Catalogos::getDataArray($db,$nombreTabla,$nameBase,$campos);
                }
                $dat = array(
                    "nombre"=>ucfirst($nom[1]),
                    "name"=>$nombreTabla,
                    "registros"=>Catalogos::getData($db,$nom[1],$nameBase),
                    "registrosActuales"=>$registrosActuales,
                );
                $cad[]=$dat;
            }
        }
        return $cad;
    }

    public static function getFields($nameBase, $db, $table) {
        $dat = null;
        $cad = array(
            "password"=>0,
        );
        $estructuraTable = Catalogos::getStructureTable($nameBase, $db, $table);
        try{
            // --> Obtener datos de los Campos
            $ss = "SHOW FULL COLUMNS FROM " . $table;
            //echo $ss."<br>";
            $recordset1 = $db->prepare($ss);
            $recordset1->execute();
            foreach ($recordset1->fetchAll(PDO::FETCH_OBJ) as $key => $row1) {
                $cad = null;
                $Field = $row1->Field;
                if($row1->Comment != ""){
                    $arr1 = str_split($row1->Comment);
                    foreach ($arr1 as $key => $value) {
                        $num = ord($value);
                        if($num == 147 || $num == 148){
                            $letra ='"';
                        }else{
                            $letra = $value;
                        }
                        $cad .= $letra;
                    }       
                    $commentField = json_decode($cad,true);
                    $Field=$commentField["name"];
                }
                
                $nameCamp = str_replace("_id", " ", $Field);
                //$nameCamp = str_replace("id_tra", " ", $nameCamp);
                //$nameCamp = str_replace("id_conf", " ", $nameCamp);
                //$nameCamp = str_replace("id_", " ", $nameCamp);
                //$nameCamp = str_replace("_", " ", $nameCamp);

                if(array_key_exists($row1->Field, $cad)){
                    
                }else{
                    $entrar = 0;
                    if(isset($estructuraTable["structure"]["skip"])){
                        foreach ($estructuraTable["structure"]["skip"] as $key => $value) {

                            if($row1->Field == $value){
                                $entrar = 1;
                            }
                        }
                    }
                    if($entrar == 0){
                        $dat[$row1->Field] = ucfirst($nameCamp);
                    }
                }
            }
            return $dat;
        } catch (PDOException $e) {
            echo $e->getMessage() . "--" . $e->getCode();
        }
    }

    public static function getFieldsAll($db, $table,  $base) {
        //echo "getFieldsAll<br>";
        //var_dump($db);
        $relaciones = Catalogos::getEstructuraBD($db,$base);
        //--> Estructura
        $estructuraTable = Catalogos::getStructureTable($base, $db, $table);
        $dat = null;
        try {
            // --> Obtener datos de los Campos
            $ss = "SHOW FULL COLUMNS FROM " . $table;
            $recordset1 = $db->prepare($ss);
            $recordset1->execute();
            foreach ($recordset1->fetchAll(PDO::FETCH_OBJ) as $key => $row1) {
                $cad = null;
                $Field = $row1->Field;
                if($row1->Comment != ""){
                    $arr1 = str_split($row1->Comment);
                    foreach ($arr1 as $key => $value) {
                        $num = ord($value);
                        if($num == 147 || $num == 148){
                            $letra ='"';
                        }else{
                            $letra = $value;
                        }
                        $cad .= $letra;
                    }       
                    $commentField = json_decode($cad,true);
                    $Field=$commentField["name"];
                }
                
                $nameCamp = str_replace("_id", " ", $row1->Field);
                //$nameCamp = str_replace("id_cat", " ", $row1->Field);
                //$nameCamp = str_replace("id_tra", " ", $nameCamp);
                //$nameCamp = str_replace("id_conf", " ", $nameCamp);
                //$nameCamp = str_replace("id_", " ", $nameCamp);
                $nameCamp = str_replace("_", " ", $nameCamp);
                $ttipo = explode("(",$row1->Type);
                $tam = str_replace(")", "", $ttipo[1]);
                $paso = true;
                foreach ($estructuraTable["structure"]["skip"] as $key => $value) {
                    if($value==$row1->Field){
                        $paso=false;
                    }
                }
                if($paso){
                    if($row1->Field != "user_id"){
                        $dat[$row1->Field] = array(
                            "nombre" => ucfirst($nameCamp),
                            "nombreSalida" => ucfirst($Field),
                            "tipo" => $ttipo[0],
                            "size" => $tam,
                            "relaciones" => array($relaciones[$table][$row1->Field],Catalogos::getData($db,$relaciones[$table][$row1->Field]["tabla"],$nameBase))
                        );
                    }
                }
            }
            //$relaciones = Catalogos::getEstructuraBD($db,$base);
            return $dat;
        } catch (PDOException $e) {
            echo $e->getMessage() . "--" . $e->getCode();
        }
    }

    public static function getSql($db, $ss) {
        $dat = null;
        $relaciones = Catalogos::getEstructuraBD($db,$base);
        try {
            $recordset = $db->prepare($ss);
            $recordset->execute();
            $item1 = $recordset->fetchAll(PDO::FETCH_OBJ);
            return $item1;
        } catch (PDOException $e) {
            echo $e->getMessage() . "--" . $e->getCode();
        }
    }
    
    public static function getData($db, $table, $base, $id = null, $limite = null) {
        $dat = null;
        $relaciones = Catalogos::getEstructuraBD($db,$base);
        try {
            if (is_null($limite)) {
                if (is_null($id)) {
                    $ss = "SELECT * FROM $table ORDER BY id DESC";
                } else {
                    $ss = "SELECT * FROM $table WHERE id = " . $id."  ORDER BY id DESC";
                }
            }else{
                if (is_null($id)) {
                    $ss = "SELECT * FROM $table ORDER BY ".$limite["campo"]." ".$limite["orden"]." LIMIT ".$limite["limite"]."";
                } else {
                    $ss = "SELECT * FROM $table WHERE id = " . $id."  ORDER BY ".$limite["campo"]." ".$limite["orden"]." LIMIT ".$limite["limite"]."";
                }
            }
            $recordset = $db->prepare($ss);
            $recordset->execute();
            $o = 0;
            while ($item1 = $recordset->fetch()) {
                $dat[$o] = $item1;
                $datt = null;
                if (isset($relaciones[$table])) {
                    $datt = null;
                    foreach ($relaciones[$table] as $key => $value) {
                        if($key != "municipio_id" && $key != "areas_ocupacion_id" && $key != "ocupacion_id"){
                            $resul = Catalogos::getCampoExterno($base, $db, $relaciones[$table][$key]["tabla"], $value["campoReturn"], $item1[$key]);
                            $datt[$key] = $resul;
                        }
                    }
                    $dat[$o]["relaciones"] = $datt;
                }
                $o++;
            }
            return $dat;
        } catch (PDOException $e) {
            echo $e->getMessage() . "--" . $e->getCode();
        }
    }

    public static function getDataArray($db, $table, $base, $valores=null) {
        $dat = null;
        $relaciones = Catalogos::getEstructuraBD($db,$base);
        try {
            if(is_null($valores)){
                $ss = "SELECT * FROM $table ORDER BY id DESC" ;
            }else{
                $where="";
                foreach ($valores as $key => $value) {
                    $where .= "{$key}='{$value}' AND ";
                }
                $where = substr($where, 0,-4);
                $ss = "SELECT * FROM {$table} WHERE {$where}  ORDER BY id DESC";
            }
            $recordset = $db->prepare($ss);
            $recordset->execute();
            $o = 0;
            while ($item1 = $recordset->fetch()) {
                $dat[$o] = $item1;
                $datt = null;
                if (isset($relaciones[$table])) {
                    $datt = null;
                    foreach ($relaciones[$table] as $key => $value) {
                        $resul = Catalogos::getCampoExterno($base, $db, $relaciones[$table][$key]["tabla"], $value["campoReturn"], $item1[$key]);
                        $datt[$key] = $resul;
                    }
                    $dat[$o]["relaciones"] = $datt;
                }
                $o++;
            }
            return $dat;
        } catch (PDOException $e) {
            echo $e->getMessage() . "--" . $e->getCode();
        }
    }
    
    public static function getDataID($db, $table, $id, $campo=null) {
        $dat = null;
        try {
            if(is_null($campo)){
                $ss = "SELECT * FROM $table WHERE id = " . $id;
            }else{
                $ss = "SELECT * FROM {$table} WHERE {$campo} = " . $id;
            }
            $recordset = $db->prepare($ss);
            $recordset->execute();
            $item1 = $recordset->fetch(PDO::FETCH_OBJ);
            return $item1;
        } catch (PDOException $e) {
            echo $e->getMessage() . "--" . $e->getCode();
        }
    }
    /*
    public static function getDataForField($db, $table, $campo, $id = null) {
        $dat = null;
        $relaciones = Catalogos::getEstructuraBD($db,$base);
        $estructuraTable = Catalogos::getStructureTable($db, $table);
        try {
            $ss = "SELECT id FROM {$table} WHERE REPLACE(LOWER({$campo}),' ','_') = '{$id}'";
            $recordset = $db->prepare($ss);
            $recordset->execute();
            $o = 0;
            $item1 = $recordset->fetch(PDO::FETCH_OBJ);
            $dat=$item1->id; 
            return $dat;
        } catch (PDOException $e) {
            echo $e->getMessage() . "--" . $e->getCode();
        }
    }
    */
    public static function getEstructuraBD($db,$base) {
        $recordset1 = null;
        try {
            // --> Limpiar tablas
            $db->prepare("SET FOREIGN_KEY_CHECKS = 0")->execute();
            //$db->prepare("TRUNCATE conf_tabla")->execute();
            //$db->prepare("TRUNCATE conf_campo")->execute();
            $db->prepare("SET FOREIGN_KEY_CHECKS = 1")->execute();

            $ss = "SHOW TABLES";
            $recordset = $db->prepare($ss);
            //--> validaciones
            //$recordset->bindParam("v_correo", $txt_correo, PDO::PARAM_STR);
            $recordset->execute();
            $nameTaB = "Tables_in_".$base;
            //echo $nameTaB;
            while ($row = $recordset->fetch(PDO::FETCH_OBJ)) {
                //var_dump($row);
                
                if (substr($row->$nameTaB, 0, 5) != "conf_") {
                    $esT = Catalogos::getStructureTable($base,$db, $row->$nameTaB);
                    //var_dump($esT);
                    //echo "<hr>";
                    //if($row->$nameTaB=="user") echo "<h2>TABLA:".$row->$nameTaB."</h2>";
                    // --> Obtener datos de los Campos
                    /*
                    $ss = "SHOW FULL COLUMNS FROM " . $row->$nameTaB;

                    $recordset1 = $db->prepare($ss);
                    $recordset1->execute();
                    if($row->$nameTaB=="empresa") echo '<table border="1"><tr><th>Campo</th><th>Tipo</th><th>Commentario</th></tr>';
                    while($campos = $recordset1->fetch(PDO::FETCH_OBJ)){
                        if($row->$nameTaB=="empresa") echo '<tr><td>'.$campos->Field.'</td><td>'.$campos->Type.'</td><td>'.$campos->Comment.'</td></tr>' ;
                    }
                    if($row->$nameTaB=="empresa") echo '</table>';
                    */
                    // --> Relaciones
                    $ss = "SHOW CREATE TABLE " . $row->$nameTaB;
                    //echo $ss."<br>";
                    $recordset1 = $db->prepare($ss);
                    $recordset1->execute();
                    while($campos = $recordset1->fetch(PDO::FETCH_OBJ)){
                        foreach ($campos as $key => $value) {
                            //if($row->$nameTaB=="user")  echo $key ."--". $value."<br>";
                            if($key=="Create Table"){
                                $structure = $value;
                            }
                        }
                        //if($row->$nameTaB=="empresa") var_dump($structure);
                        $dat = explode("ENGINE",$structure);
                        //if($row->$nameTaB=="user") var_dump($dat);
                        $dat1 = explode("CONSTRAINT", $dat[0]);
                        
                        if(count($dat1) > 1){
                            foreach ($dat1 as $key => $value) {
                                if($key==0){
                                    continue; 
                                }
                                $limpiar = str_replace("ON DELETE NO ACTION ON UPDATE NO ACTION,", "", $value);
                                $limpiar = str_replace("ON DELETE NO ACTION ON UPDATE NO ACTION", "", $limpiar);
                                $limpiar = str_replace("`", "", $limpiar);
                                $limpiar = str_replace(")", "", $limpiar);
                                $limpiar = str_replace("(", "", $limpiar);
                                $dat3 = explode("FOREIGN KEY",$limpiar);
                                $nameRelacion = trim($dat3[0]);
                                $dat4 = explode("REFERENCES",$dat3[1]);
                                $campoRelacion = trim($dat4[0]);
                                $dat5 = explode(" ",$dat4[1]);
                                $tabla = trim($dat5[1]);
                                $campo = trim($dat5[2]);
                                $nuevocampo = str_replace("_id", "", $campoRelacion);
                                $nuevocampo2 = str_replace("_id1", "", $campoRelacion);
                                $esTablaa = Catalogos::getStructureTable($base, $db, $nuevocampo);
                                $esTablaa2 = Catalogos::getStructureTable($base, $db, $nuevocampo2);
                                if(is_array($esT["structure"]["return"]) or is_array($esTablaa["structure"]["return"]) or is_array($esTablaa2["structure"]["return"])){
                                    if(is_array($esTablaa["structure"]["return"])){
                                        $camposReturn = $esTablaa["structure"]["return"];
                                    }
                                    if(is_array($esTablaa2["structure"]["return"])){
                                        $camposReturn = $esTablaa2["structure"]["return"];
                                    }
                                    if(is_array($esT["structure"]["return"])){
                                        $camposReturn = $esT["structure"]["return"];
                                    }
                                }else{
                                    $camposReturn=array($tabla);
                                }
                                $dd[$row->$nameTaB][$campoRelacion]=array("tabla" => $tabla,"campoReturn" => $camposReturn);
                                
                            }
                        }
                    }
                }
                
            }
            return $dd;
        } catch (PDOException $e) {
            echo $e->getMessage() . "--" . $e->getCode();
        }
    }
    /*
    public static function getRelaciones($db, $tabla, $campo, $valor = null) {
        $datt = null;
        $relaciones = Catalogos::relaciones();
        if (isset($relaciones[$tabla][$campo])) {
            $datt = Catalogos::getCampoExterno($db, $relaciones[$tabla][$campo]["tabla"], $relaciones[$tabla][$campo]["campoReturn"], $valor);
        }
        return $datt;
    }
    */
    public static function relaciones() {
        $relaciones = array(
            "user" => array(
                "rol_id" => array(
                    "tabla" => "role",
                    "campoReturn" => array("role"),
                )
            )
        );
        return $relaciones;
    }

    public static function getStructureTable($nameBase, $db, $table){
        $sss="SELECT table_comment FROM INFORMATION_SCHEMA.TABLES WHERE table_schema='{$nameBase}' AND table_name='{$table}' ";
        $recordsets = $db->prepare($sss);
        $recordsets->execute();
        $item2 = $recordsets->fetch(PDO::FETCH_OBJ);
        $cad = null;
        if($item2){
            $arr1 = str_split($item2->table_comment);
            foreach ($arr1 as $key => $value) {
                $num = ord($value);
                if($num == 147 || $num == 148){
                    $letra ='"';
                }else{
                    $letra = $value;
                }
                $cad .= $letra;
            }       
        }
        $table_comment = $cad;
        $comment = json_decode($table_comment,true);
        return $comment;
    }
    
    public static function getCampoExterno($nameBase, $db, $table, $campoReturn, $valor = null) {
        $dat = null;
        try {
            $ss = "SELECT * FROM $table";
            $recordset = $db->prepare($ss);
            $recordset->execute();
            while ($item1 = $recordset->fetch(PDO::FETCH_OBJ)) {
                $retorno = "";
                foreach ($campoReturn as $key => $value) {
                    $retorno .= $item1->$value . " ";
                }
                if(trim($retorno)==""){
                    $sss="SELECT table_comment FROM INFORMATION_SCHEMA.TABLES WHERE table_schema='{$nameBase}' AND table_name='{$table}' ";
                    $recordsets = $db->prepare($sss);
                    $recordsets->execute();
                    $item2 = $recordsets->fetch(PDO::FETCH_OBJ);
                    $comment = json_decode($item2->table_comment,true);
                    foreach ($comment["structure"]["return"] as $key => $value) {
                        $retorno .= $item1->$value . " ";
                    }
                }
                $dat[$item1->id] = $retorno;
            }
            return $dat;
        } catch (PDOException $e) {
            echo $e->getMessage() . "--" . $e->getCode();
        }
    }

    public static function guardarRegistro($nameBase, $path, $bd, $tabla, $campos, $camposRelacionados) {
        $camposD = null;
        $valoresD = null;
        // --> Inserta campos de fecha y fecha registro de forma automatica
        $cam = Catalogos::getFields($nameBase, $bd, $tabla);
        foreach ($cam as $key => $value) {
            if ($key == "fecha" or $key == "fecha_registro") {
                $camposD .= $key . ",";
                $valoresD .= "NOW(),";
            }elseif($key == "user_id"){
                if(isset($_COOKIE["idUser"]) && $_COOKIE["idUser"]>0){
                    $camposD .= $key . ",";
                    $valoresD .= $_COOKIE["idUser"].",";
                }
            }
        }

        foreach ($campos as $key => $value) {
            if($key=="Cpassword"){
                continue;
            }
            if($key=="password"){
                if(trim($value) != ""){
                    $camposD .= $key . ",";
                    $valoresD .= "MD5('" . $value . "'),";
                }
            }elseif($key=="password1"){
                if(trim($value) != ""){
                    $camposD .= "password,";
                    $valoresD .= "MD5('" . $value . "'),";
                }
            }elseif($key=="birthdate"){ 
                $y=  substr($value, 6,4);
                $m=  substr($value, 3,2);
                $d=  substr($value, 0, 2);
                $h=  substr($value, 11, 2);
                $n=  substr($value, 14, 2);
                $nv = $y."-".$m."-".$d." ".$h.":".$n;
                $camposD .= $key . ",";
                $valoresD .= "'" . $value . "',";
            }else{
                    if($tabla=="blog"){
                        $value = str_replace("../fileman/Uploads/", $path."/fileman/Uploads/", $value);
                    }
                    $camposD .= $key . ",";
                    if(is_numeric($value)){
                        $valoresD .= "" . $value . ",";
                    }else{
                        $valoresD .= "'" . $value . "',";
                    }
                    
            }
        }
        $camposD = substr($camposD, 0, -1);
        $valoresD = substr($valoresD, 0, -1);
        $ss = "INSERT INTO $tabla ($camposD) VALUES ($valoresD)";
        $recordset = $bd->prepare($ss);
        $recordset->execute();
        $idReg = $bd->lastInsertId();
        
        // --> Guardar imagen
        if (isset($_FILES["txFile"])) {
            $estructura = dirname(__FILE__)."/../../includes/images/{$tabla}/";
            //echo "X:".$estructura;
            if (!file_exists($estructura)) {
                if (!mkdir($estructura, 0777, true)) {
                    die('Fallo al crear las carpetas...');
                }
            }
            $file_name = $_FILES["txFile"]["name"];
            $ext =explode(".", $file_name);
            if($tabla=="profile"){
                $idReg = $campos["url"];
            }
            $add = $estructura . "/".$idReg.".".strtolower($ext[1]);
            if (move_uploaded_file($_FILES["txFile"]["tmp_name"], $add)) {
                $ex =  strtolower($ext[1]);
                if($ex != "jpg") @unlink($estructura."$idReg.jpg");
                if($ex != "png") @unlink($estructura."$idReg.png");
                if($ex != "gif") @unlink($estructura."$idReg.gif");
            } else {
                
            }  
        }
        // --> Guardar PDF
        if (isset($_FILES["txFilePDF"])) {
            $estructura = dirname(__FILE__)."/../../includes/pdfs/{$tabla}/";
            //echo "X:".$estructura;
            if (!file_exists($estructura)) {
                if (!mkdir($estructura, 0777, true)) {
                    die('Fallo al crear la carpeta de PDF...');
                }
            }
            $file_name = $_FILES["txFilePDF"]["name"];
            $ext =explode(".", $file_name);
            if($tabla=="profile"){
                $idReg = $campos["url"];
            }
            $add = $estructura . "/".$idReg.".pdf";
            if (move_uploaded_file($_FILES["txFilePDF"]["tmp_name"], $add)) {
                
            } else {
                
            }  
        }
        // --> Si campos relacionados
        if(!is_null($camposRelacionados)){
            foreach ($camposRelacionados as $key => $value) {
                $ss = "DELETE FROM {$key} WHERE {$tabla}_id = " . $id;
                $recordset = $bd->prepare($ss);
                $recordset->execute();
                // --> Borrar todoas las relaciones agregar nuevas
                foreach ($value as $k => $v) {
                    $ssw = "INSERT INTO {$key} VALUES (0,{$id},{$v})";
                    $recordsetw = $bd->prepare($ssw);
                    $recordsetw->execute();
                }
            }
        }
        return $idReg;
    }

    public static function borrarRegistro($bd, $tabla, $id) {
        $ss = "DELETE FROM $tabla WHERE id = " . $id;
        $recordset = $bd->prepare($ss);
        $recordset->execute();
        return $id;
    }

    public static function editarRegistro($nameBase, $path, $bd, $tabla, $campos, $camposRelacionados, $id) {
        $camposD = null;
        $valoresD = null;
        // --> Inserta campos de fecha y fecha registro de forma automatica
        $cam = Catalogos::getFields($nameBase, $bd, $tabla);
        foreach ($cam as $key => $value) {
            if ($key == "fecha" or $key == "fecha_registro") {
                $camposD .= $key . "=NOW(),";
            }elseif($key == "user_id" && ($tabla != "user_has_servicio")){
                $camposD .= $key . "=".$_COOKIE["idUser"].",";
            }
        }
        foreach ($campos as $key => $value) {
            if($key=="password"){
                if(trim($value) != ""){
                    $camposD .= $key . "=MD5('" . $value . "'),";
                }
            }else{
                if($key=="birthdate"){
                    $y=  substr($value, 6,4);
                    $m=  substr($value, 3,2);
                    $d=  substr($value, 0, 2);
                    $h=  substr($value, 11, 2);
                    $n=  substr($value, 14, 2);
                    $nv = $y."-".$m."-".$d." ".$h.":".$n;
                    $camposD .= $key . "='" . $value . "',";
                }else{
                    if($tabla=="blog"){
                        $value = str_replace("../fileman/Uploads/", $path."/fileman/Uploads/", $value);
                    }
                    if(is_numeric($value)){
                        $camposD .= $key . "=" . $value . ",";
                    }else{
                        $camposD .= $key . "='" . $value . "',";
                    }
                }
            }
        }
        $camposD = substr($camposD, 0, -1);
        $ss = "UPDATE $tabla SET $camposD WHERE id = " . $id;
        $recordset = $bd->prepare($ss);
        $recordset->execute();
        $idReg = $id;
        // --> Guardar imagen
        if (isset($_FILES["txFile"])){
            $estructura = dirname(__FILE__)."/../../includes/images/{$tabla}/";
            if (!file_exists($estructura)) {
                if (!mkdir($estructura, 0777, true)) {
                    die('Fallo al crear las carpetas...');
                }
            }
            $file_name = $_FILES["txFile"]["name"];
            $ext =explode(".", $file_name);
            if($tabla=="profile"){
                $idReg = $campos["url"];
            }
            $add = $estructura . "".$idReg.".".strtolower($ext[1]);
                if (move_uploaded_file($_FILES["txFile"]["tmp_name"], $add)) {
                    $ex =  strtolower($ext[1]);
                    if($ex != "jpg") @unlink($estructura."$idReg.jpg");
                    if($ex != "png") @unlink($estructura."$idReg.png");
                    if($ex != "gif") @unlink($estructura."$idReg.gif");
                } else {
                }
        }
        // --> Guardar PDF
        if (isset($_FILES["txFilePDF"])) {
            $estructura = dirname(__FILE__)."/../../includes/pdfs/{$tabla}/";
            //echo "X:".$estructura;
            if (!file_exists($estructura)) {
                if (!mkdir($estructura, 0777, true)) {
                    die('Fallo al crear la carpeta de PDF...');
                }
            }
            $file_name = $_FILES["txFilePDF"]["name"];
            $ext =explode(".", $file_name);
            if($tabla=="profile"){
                $idReg = $campos["url"];
            }
            $add = $estructura . "/".$idReg.".pdf";
            if (move_uploaded_file($_FILES["txFilePDF"]["tmp_name"], $add)) {
                
            } else {
                
            }  
        }
        // --> Si campos relacionados
        if(!is_null($camposRelacionados)){
            foreach ($camposRelacionados as $key => $value) {
                $ss = "DELETE FROM {$key} WHERE {$tabla}_id = " . $id;
                $recordset = $bd->prepare($ss);
                $recordset->execute();
                // --> Borrar todoas las relaciones agregar nuevas
                foreach ($value as $k => $v) {
                    $ssw = "INSERT INTO {$key} VALUES (0,{$id},{$v})";
                    $recordsetw = $bd->prepare($ssw);
                    $recordsetw->execute();
                }
            }
        }
        return $id;
    }

    public function IsImgTable($db, $tabla) {
        try {
            $ss = "SELECT * FROM conf_tabla WHERE tabla = '$tabla'";
            $recordset = $db->prepare($ss);
            $recordset->execute();
            $item1 = $recordset->fetch(PDO::FETCH_OBJ);
            return $item1->img;
        } catch (PDOException $e) {
            echo $e->getMessage() . "--" . $e->getCode();
        }
    }
}
?>