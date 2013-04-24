<?php

require('lib.php');

$ret = array( 
	'schools' => array(), 
	'classes' => array(),
	'schoolsSelected' => $_SESSION['schools'],
	'classesSelected' => $_SESSION['classes']
);

$sql = 'SELECT distinct id, name FROM classes order by id;';

$queryResult = $PredictDB->prepare($sql);
$queryResult->execute();

while( $qRes = $queryResult->fetch( PDO::FETCH_ASSOC ) ) {
	$ret['classes'][$qRes['id']] = $qRes['name'];
}



$sql = 'SELECT distinct schoolName, schoolId FROM departments order by schoolId;';

$queryResult = $PredictDB->prepare($sql);
$queryResult->execute();

while( $qRes = $queryResult->fetch( PDO::FETCH_ASSOC ) ) {
	$ret['schools'][$qRes['schoolId']] = $qRes['schoolName'];
}

FinishByJson( $ret );


