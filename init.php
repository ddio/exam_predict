<?php

require('lib.php');

$ret = array( 
	'schools' => array(), 
	'classes' => $ClassMap,
	'schoolsSelected' => $_SESSION['schools'],
	'classesSelected' => $_SESSION['classes']
);

$sql = 'SELECT distinct schoolName, schoolId FROM departments;';

$queryResult = $PredictDB->prepare($sql);
$queryResult->execute();

while( $qRes = $queryResult->fetch( PDO::FETCH_ASSOC ) ) {
	$ret['schools'][$qRes['schoolId']] = $qRes['schoolName'];
}

FinishByJson( $ret );


