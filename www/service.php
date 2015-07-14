<?php

include_once('./f.php');
include_once('./f.classes.php');

$serviceRequest = F::setFromRequestParameters(new ServiceRequest());

$operator = F::setFromStringName($serviceRequest->op.'Operator');

$operation = F::setFromRequestBody(F::setFromStringName($serviceRequest->op));
$dao = F::setFromStringName($serviceRequest->op.'Dao');

$operator->operate($operation, $dao);

?>