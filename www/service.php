<?php

include_once('./f.php');
include_once('./f.classes.php');

$serviceRequest = F::setFromRequest(new ServiceRequest());

$operation = F::setFromStringName($serviceRequest->op);
F::setFromRequetBody($operation);

$operator = F::setFromStringName($serviceRequest->op.'Operator');
$dao = F::setFromStringName($serviceRequest->op.'Dao');
$operator->operate($operation, $dao);

?>