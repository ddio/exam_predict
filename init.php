<?php

require('lib.php');

$ret = array( 
	'schools' => array(), 
	'schoolsSelected' => $_SESSION['schools'],
);

$sql = 'SELECT distinct schoolName, schoolId FROM departments order by schoolId;';

$queryResult = $PredictDB->prepare($sql);
$queryResult->execute();

while( $qRes = $queryResult->fetch( PDO::FETCH_ASSOC ) ) {
	$ret['schools'][$qRes['schoolId']] = $qRes['schoolName'];
}

FinishByJson( $ret );


