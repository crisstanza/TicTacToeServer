<?php

include_once('./f.php');
include_once('./f.classes.php');

$serviceRequest = new ServiceRequest();
setFromRequest($serviceRequest);

if (F::isPost()) {
	echo 1;	
} else {
	echo 2;	
}

echo '<br>' . $serviceRequest->op;

?>