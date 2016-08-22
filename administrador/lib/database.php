<?php
if (file_exists("conf/clave.php")){ 
	include("conf/clave.php");
}

if (file_exists("../conf/clave.php")){ 
	include("../conf/clave.php");
}


$db= new  Database();
$dbaux= new  Database();

class Database
{
	var $Host = "localhost";
    var $Database = "mottif_com_searca";
    var $User     = "motti_searca";
    var $Password = "M0tt1S3rc4_2016";
	var $R = array();
    var $Row;
    var $Errno    = 0;
    var $Error    = "";
    var $Link_ID  = 0;
    var $Query_ID = 0;
	


function enviar_sql($Query_String) 
	{
		/*//echo "<script language='javascript'>";
		//echo "window.open('http://201.245.145.107:8080/sistema/sql.php?sql=$Query_String');";
		//echo "";
		//echo "</script>";
		//exit;*/
	}

function escribe_sql($sql){
  $ar=fopen("../sincronia/sql_$codigo_usuario.txt","a") or die("Problemas en la creacion");
  $sql="@@@@@@@@".$sql;
  fputs($ar,"$sql");
  fclose($ar);
}

	
	
	function Database($query = "")
    {
        $this->query($query);
    }

    function connect($Database = "", $Host = "", $User = "", $Password = "")
    {
        if ("" == $Database)
            $Database = $this->Database;
        if ("" == $Host)
            $Host     = $this->Host;
        if ("" == $User)
            $User     = $this->User;
        if ("" == $Password)
            $Password = $this->Password;
        if ( 0 == $this->Link_ID )
        {
            $this->Link_ID=mysql_connect($Host, $User, $Password);
            if (!$this->Link_ID)
            {
                $this->halt("Database connect($Host, $User, \$Password) failed.");
                return 0;
            }
            if (!@mysql_select_db($Database,$this->Link_ID))
            {
                $this->halt("Cannot use database ".$this->Database);
                return 0;
            }
        }
	
        return $this->Link_ID;
    }

    function free()
    {
        @mysql_free_result($this->Query_ID);
        $this->Query_ID = 0;
    }
	
	
	
    function html_codigo($String)
    {
		
        return ($String);
    }


    function query($Query_String)
    {
	
	
	$Query_String=$this->html_codigo($Query_String);
	
        if ($Query_String == "")
            return 0;
        if (!$this->connect())
        {
            return 0;
        };
        if ($this->Query_ID)
        {
			for($f=0;$f<@mysql_num_fields($this->Query_ID);$f++)
			{
				eval("unset(\$this->".mysql_field_name($this->Query_ID,$f).");");
			}
            $this->free();
        }
        $this->Query_ID = @mysql_query($Query_String,$this->Link_ID);
        $this->Row = 0;
        $this->Errno = mysql_errno();
        $this->Error = mysql_error();
        if (!$this->Query_ID)
        {
            $this->halt("Invalid SQL: ".$Query_String);
        }
        return $this->Query_ID;
    }
	
	function argumento($sql,$column){
		if(!$link=mysql_connect($Host, "root",  "")){
			echo "Error: Seleccionando el servidor";
			exit();
		}

		if(!mysql_select_db("sis_dis",$link)){
			echo "Error: Seleccionando la Base de Datos";
			exit();
		}
		$result = mysql_query($sql,$link);
		if($fila = mysql_fetch_assoc($result)){
			echo $fila[$column];
		}
		mysql_close($link);
		return $valor;
	}	
	
	
    function next_row()
    {
        if (!$this->Query_ID)
        {
            $this->halt("next_record called with no query pending.");
            return 0;
        }
        $this->R = @mysql_fetch_array($this->Query_ID);
		if(is_array($this->R))
			for($f=0;$f<mysql_num_fields($this->Query_ID);$f++)
				eval("\$this->".mysql_field_name($this->Query_ID,$f)."=stripslashes(\"".addslashes($this->R[$f])."\");");
		else
			for($f=0;$f<mysql_num_fields($this->Query_ID);$f++)
				eval("unset(\$this->".mysql_field_name($this->Query_ID,$f).");");
        $this->Row   += 1;
        $this->Errno  = mysql_errno();
        $this->Error  = mysql_error();
        return is_array($this->R);

    }
    
    function import_row($arr)
    {
        if (!$this->Query_ID)
        {
            $this->halt("import_record called with no query pending.");
            return 0;
        }
        $this->R = @mysql_fetch_array($this->Query_ID);
		if(is_array($this->R))
			for($f=0;$f<mysql_num_fields($this->Query_ID);$f++)
                if(isset($arr[mysql_field_name($this->Query_ID,$f)."_img"]))
                {
                    if($arr[mysql_field_name($this->Query_ID,$f)."_del"])
                        eval("\$this->".mysql_field_name($this->Query_ID,$f)."=\"\";");
                    elseif($arr[mysql_field_name($this->Query_ID,$f)]&&$arr[mysql_field_name($this->Query_ID,$f)]!="none")
                    {
                        $tmpname=tempnam("grafika/","tmp");
                        copy($arr[mysql_field_name($this->Query_ID,$f)],$tmpname);
                        eval("\$this->".mysql_field_name($this->Query_ID,$f)."=\$tmpname;");
                    }
                    else
                        eval("\$this->".mysql_field_name($this->Query_ID,$f)."=\$arr[\"".mysql_field_name($this->Query_ID,$f)."_img\"];");
                }
                else
                {
                    eval("\$this->".mysql_field_name($this->Query_ID,$f)."=stripslashes(\$arr[\"".mysql_field_name($this->Query_ID,$f)."\"]);");
                }
            
		else
			for($f=0;$f<mysql_num_fields($this->Query_ID);$f++)
				eval("unset(\$this->".mysql_field_name($this->Query_ID,$f).");");
        $this->Row   += 1;
        $this->Errno  = mysql_errno();
        $this->Error  = mysql_error();
        return is_array($this->R);
    }

    function seek($pos = 0)
    {
        $status = @mysql_data_seek($this->Query_ID, $pos);
        if ($status)
            $this->Row = $pos;
        else
        {
            $this->halt("seek($pos) failed: result has ".$this->num_rows()." rows");
            @mysql_data_seek($this->Query_ID, $this->num_rows());
            $this->Row = $this->num_rows;
            return 0;
        }
        return 1;
    }

    function affected_rows()
    {
        return @mysql_affected_rows($this->Link_ID);
    }

    function num_rows()
    {
        return @mysql_num_rows($this->Query_ID);
    }

    function insert_id()
    {
        return @mysql_insert_id($this->Link_ID);
    }

    function num_fields()
    {
        return @mysql_num_fields($this->Query_ID);
    }

    function get_field($Name)
    {
        return $this->R[$Name];
    }

    function halt($msg)
    {
        $this->Error = @mysql_error($this->Link_ID);
        $this->Errno = @mysql_errno($this->Link_ID);
		printf("<b>Database error:</b> %s<br>\n", $msg);
        printf("<b>MySQL Error</b>: %s (%s)<br>\n",$this->Errno,$this->Error);
    }
	
	
		function html_correccion($String)
    {
		$String=str_replace("Á","A",$String);
		$String=str_replace("À","A",$String);
		$String=str_replace("É","E",$String);
		$String=str_replace("È","E",$String);
		$String=str_replace("Í","I",$String);
		$String=str_replace("Ì","I",$String);
		$String=str_replace("Ó","O",$String);
		$String=str_replace("Ò","O",$String);
		$String=str_replace("Ú","U",$String);
		$String=str_replace("Ù","U",$String);
		$String=str_replace("á","a",$String);
		$String=str_replace("à","a",$String);
		$String=str_replace("é","e",$String);
		$String=str_replace("è","e",$String);
		$String=str_replace("í","i",$String);
		$String=str_replace("ì","i",$String);
		$String=str_replace("ó","o",$String);
		$String=str_replace("ò","o",$String);
		$String=str_replace("ú","u",$String);
		$String=str_replace("ù","u",$String);
		$String=str_replace("Ä","A",$String);
		$String=str_replace("Â","A",$String);
		$String=str_replace("Ë","E",$String);
		$String=str_replace("Ê","E",$String);
		$String=str_replace("Ï","I",$String);
		$String=str_replace("Ö","O",$String);
		$String=str_replace("Ô","O",$String);
		$String=str_replace("Ü","U",$String);
		$String=str_replace("Û","U",$String);
		$String=str_replace("ä","a",$String);
		$String=str_replace("â","a",$String);
		$String=str_replace("ë","e",$String);
		$String=str_replace("ê","e",$String);
		$String=str_replace("ï","i",$String);
		$String=str_replace("î","i",$String);
		$String=str_replace("ö","o",$String);
		$String=str_replace("ü","u",$String);
		$String=str_replace("û","u",$String);
		$String=str_replace("å","a",$String);
		$String=str_replace("Ñ","N",$String);
		$String=str_replace("Õ","O",$String);
		$String=str_replace("ã","a",$String);
		$String=str_replace("ñ","n",$String);
		$String=str_replace("Ý","Y",$String);
		$String=str_replace("õ","o",$String);
		$String=str_replace("ý","y",$String);
		//echo $String;
        return ($String);
    }
	
	function close()
	{
		$this->free();
		mysql_close($this->Link_ID);
	}
	
}

?>
