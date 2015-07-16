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
			$parameterValue = urldecode($lines[$i++]);
			$parameterValueParsed = F::parse($class_name, $name, $parameterValue);
			$instance->{$name} = $parameterValueParsed;
		}
		return $instance;
	}

	public static function getFromInstance($instance) {
		$class_name = get_class($instance);
		$members = get_class_vars($class_name);
		$result = array();
		foreach ($members as $name => $value) {
			$lineValue = urlencode($value);
			array_push($result, $lineValue, "\n");
		}
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

}

abstract class N {

	public static function booleanValue($value) {
		return strtolower($value) == 'true' || $value == 1 ? '1': '0';
	}

}

abstract class D {

	public function open() {
		@$con = mysql_connect('localhost', 'user', 'pass');
		if (!$con) {
			throw new Exception(mysql_errno());
		}
		@$db = mysql_select_db('base'), $con);
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
		array_push($sql, 'SELECT * FROM ', strtolower($class_name), ' WHERE id=', self::quotes($class_name, 'id', $vo->id, $con));
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

?>