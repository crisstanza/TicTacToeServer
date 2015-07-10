<?php

date_default_timezone_set('UTC');

class F { 

	public static function setFromRequest($instance) {
		$class_name = get_class($instance);
		$members = get_class_vars($class_name);
		foreach ($members as $name => $value) {
			$parameterValue = isset($_REQUEST[$name]) ? urldecode($_REQUEST[$name]) : '';
			$parameterValueParsed = Base::parse($class_name, $name, $parameterValue);
			$instance->{$name} = $parameterValueParsed;
		}
		return $instance;
	}

	public static function getRequestBody() {

		// $entityBody = stream_get_contents(STDIN);


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

include_once('./c.php');

?>