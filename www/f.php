<?php

/*
	This is the "F" framework.
*/

date_default_timezone_set('UTC');

/*
	type=numeric|string|bool
	mandatory=true|false
	id=true|false
	transient=true|false
*/
class ServiceRequest {

	/** type=string&mandatory=false */
	var $op;

}

abstract class F {

	public static function setFromStringName($class_name) {
		return new $class_name;
	}

	public static function setFromRequestParameters($instance) {
		$class_name = get_class($instance);
		$members = get_class_vars($class_name);
		foreach ($members as $name => $value) {
			$parameterValue = isset($_REQUEST[$name]) ? urldecode($_REQUEST[$name]) : '';
			$parameterValueParsed = I::parse($class_name, $name, $parameterValue);
			$instance->{$name} = $parameterValueParsed;
		}
		return $instance;
	}

	public static function setFromRequestBody($instance) {
		$class_name = get_class($instance);
		$members = get_class_vars($class_name);
		$requestBody = self::getRequestBody();
		$lines = explode("\n", $requestBody);
		$i = 0;
		foreach ($members as $name => $value) {
			$parameterValue = $lines[$i++];
			$parameterValueParsed = F::parse($class_name, $name, $parameterValue);
			$instance->{$name} = $parameterValueParsed;
		}
		return $instance;
	}

	public static function getFromInstance($instance) {
		$members = get_object_vars($instance);
		ksort($members, SORT_REGULAR);
		$result = array();
		foreach ($members as $name => $value) {
			$lineValue = $value;
			array_push($result, $lineValue, "\n");
		}
		array_pop($result);
		return join($result);
	}

	public static function getRequestBody() {
		// return stream_get_contents(STDIN);
		$rawInput = fopen('php://input', 'r');
		$tempStream = fopen('php://temp', 'r+');
		stream_copy_to_stream($rawInput, $tempStream);
		rewind($tempStream);
		return stream_get_contents($tempStream);
	}

	public static function isPost() {
		$method = $_SERVER['REQUEST_METHOD'];
		return $method == 'POST';
	}

}

abstract class I {

	public static function annotationValue($class_name, $property, $annotation_name) {
		$rc = new ReflectionClass($class_name);
		$comment = $rc->getProperty($property)->getDocComment();
		$start = strpos($comment, '/**') + 3;
		$end = strpos($comment, '*/') - 3;
		$annotationString = trim(substr($comment, $start, $end));
		parse_str($annotationString, $annotation);
		return isset($annotation[$annotation_name]) ? $annotation[$annotation_name] : '';
	}

	public static function parse($class_name, $property, $value) {
		$type = self::type($class_name, $property);
		switch ($type) {
    		case 'bool':
    			return N::booleanValue($value);
        	break;
			default:
				return $value;
        	break;
		}
	}

	public static function type($class_name, $property) {
		return self::annotationValue($class_name, $property, 'type');
	}

	public static function mandatory($class_name, $property) {
		return N::booleanValue(self::annotationValue($class_name, $property, 'mandatory'));
	}

	public static function transient($class_name, $property) {
		return N::booleanValue(self::annotationValue($class_name, $property, 'transient'));
	}

}

abstract class N {

	public static function booleanValue($value) {
		return strtolower($value) == 'true' || $value == 1 ? '1': '0';
	}

}

abstract class D {

	/*
		Códigos de erro conhecidos:
			- 1040 : too many connections
			- 1044 : usuário inválido
			- 1045 : senha inválida
			- 1049 : banco de dados desconhecido
			- 1054 : coluna desconhecida na cláusula where
			- 1062 : chave duplicada em consulta sql executada
			- 1064 : erro de sintaxe em consulta sql executada
			- 2002, 2003 : servidor de banco de dados desligado ou host inválido
			- 1146 : ???
	*/
	public function open() {
		@$con = mysql_connect('localhost', 'u245853626_user', 'password');
		if (!$con) {
			throw new Exception(mysql_errno());
		}
		@$db = mysql_select_db('u245853626_base', $con);
		if (!$db) {
			throw new Exception(mysql_errno());
		}
		return $con;
	}

	public function close($con, $conWasNull) {
		if ($conWasNull) {
			@$rs = mysql_close($con);
			if (!$rs) {
				throw new Exception(mysql_errno().' '.mysql_error());
			}
		}
	}

	public function setFromResultSet($row, $obj) {
		$members = get_class_vars(get_class($obj));
		foreach ($members as $name => $value) {
			$transient = I::transient(get_class($obj), $name);
			if (!$transient) {
				$obj->{$name} = $row[$name];
			}
		}
		return $obj;
	}

	public static function quotes($class_name, $property, $str, $con) {
		$type = I::type($class_name, $property);
		if (strlen(trim($str)) <= 0) {
			return "NULL";
		} else if (is_numeric($str) || is_bool($str)){
			return $str;
		} else {
			return "'".mysql_real_escape_string($str, $con)."'";
		}
	}

	private function select($sql, $con) {
		@$rs = mysql_query($sql, $con);
		if (!$rs) {
			if ($con == null) {
				throw new Exception('$con == null');
			} else {
				throw new Exception(mysql_errno().' '.mysql_error());
			}
		}
		return $rs;
	}

	public static function sqlFindById($con, $obj) {
		$class_name = get_class($obj);
		$sql = array();
		array_push($sql, 'SELECT * FROM ', strtolower($class_name), ' WHERE id=', self::quotes($class_name, 'id', $obj->id, $con));
		return join($sql);
	}

	public function findById($obj) {
		$con = self::open();
		$sql = self::sqlFindById($con, $obj);
		$rs = self::select($sql, $con);
		$result = null;
		if($row = mysql_fetch_assoc($rs)) {
			$result = call_user_func_array(array('self', 'rowToObjectTransformer'), array($row, $con));
		}
		self::close($con, true);
		return $result;
	}

}

abstract class E {

	public static function sendMail($from, $namefrom, $to, $nameto, $subject, $message, $ccArray=null, $bccArray=null) {
		$smtpServer = 'localhost';
		$port = 25;
		$timeout = 30;
		$username = '';
		$password = '';
		$localhost = $_SERVER['SERVER_NAME'];
		$newLine = "\r\n";

		$smtpConnect = fsockopen($smtpServer, $port, $errno, $errstr, $timeout);
		$smtpResponse = fgets($smtpConnect);
		if( empty($smtpConnect) ) {
			$return "Failed to connect: $smtpResponse";
		}

		fputs($smtpConnect,"AUTH LOGIN" . $newLine);
		$smtpResponse = fgets($smtpConnect);
		fputs($smtpConnect, base64_encode($username) . $newLine);
		$smtpResponse = fgets($smtpConnect);
		fputs($smtpConnect, base64_encode($password) . $newLine);
		$smtpResponse = fgets($smtpConnect);
		fputs($smtpConnect, "HELO $localhost" . $newLine);
		$smtpResponse = fgets($smtpConnect);
		fputs($smtpConnect, "MAIL FROM: $from" . $newLine);
		$smtpResponse = fgets($smtpConnect);
		fputs($smtpConnect, "RCPT TO: $to" . $newLine);
		$smtpResponse = fgets($smtpConnect);
		fputs($smtpConnect, "DATA" . $newLine);
		$smtpResponse = fgets($smtpConnect);

		$headers = "MIME-Version: 1.0" . $newLine;
		$headers .= "Content-type: text/html; charset=UTF-8" . $newLine;
		$headers .= "To: ".self::encodeHeaderValue($nameto)." <$to>" . $newLine;
		$headers .= "From: ".self::encodeHeaderValue($namefrom)." <$from>" . $newLine;
		if ( $ccArray != null ) {
			foreach ( $ccArray as $cc ) {
				$headers .= "Cc: ".self::encodeHeaderValue($cc['name'])." <".$cc['email'].">" . $newLine;
			}
		}
		if ( $bccArray != null ) {
			foreach ( $bccArray as $bcc ) {
				$headers .= "Bcc: ".self::encodeHeaderValue($bcc['name'])." <".$bcc['email'].">" . $newLine;
			}
		}
		fputs($smtpConnect, "Subject: ".self::encodeHeaderValue($subject)."\n$headers\n\n$message\n.\n");
		$smtpResponse = fgets($smtpConnect);
		fputs($smtpConnect, "QUIT" . $newLine);
		$smtpResponse = fgets($smtpConnect);
		return true;
	}

	public static function encodeHeaderValue($str) {
		return empty($str) ? '' : '=?UTF-8?B?'.base64_encode($str).'?=';
		}
	}

}

?>