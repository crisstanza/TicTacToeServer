<?php

date_default_timezone_set('UTC');

class ServiceRequest {

	/** type=string&mandatory=false */
	var $op;

}

class F { 

	public static function setFromStringName($class_name) {
		return new $class_name;
	}

	public static function setFromRequest($instance) {
		$class_name = get_class($instance);
		$members = get_class_vars($class_name);
		foreach ($members as $name => $value) {
			$parameterValue = isset($_REQUEST[$name]) ? urldecode($_REQUEST[$name]) : '';
			$parameterValueParsed = F::parse($class_name, $name, $parameterValue);
			$instance->{$name} = $parameterValueParsed;
		}
		return $instance;
	}

	public static function parse($class_name, $property, $value) {
		$type = F::type($class_name, $property);
		switch ($type) {
    		case 'bool':
    			return F::boolean_value($value);
        	break;
			default:
				return $value;
        	break;
		}
	}

	public static function type($class_name, $property) {
		return F::annotation_value($class_name, $property, 'type');
	}

	public static function boolean_value($value) {
		return strtolower($value) == 'true' || $value == 1 ? '1': '0';
	}

	public static function annotation_value($class_name, $property, $annotation_name) {
		$rc = new ReflectionClass($class_name);
		$comment = $rc->getProperty($property)->getDocComment();
		$start = strpos($comment, '/**') + 3;
		$end = strpos($comment, '*/') - 3;
		$annotationString = trim(substr($comment, $start, $end));
		parse_str($annotationString, $annotation);
		return isset($annotation[$annotation_name]) ? $annotation[$annotation_name] : '';
	}

	public static function setFromRequetBody($instance) {
		return $instance;
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

?>