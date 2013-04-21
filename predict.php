<?php

require( 'lib.php' );

if( !isset($_SESSION['phase']) || $_SESSION['phase'] != 2 ) {
	ToHome();
}

$schools = GetPara( 'schools', null, 'GET' );
$classes = GetPara( 'classes', null, 'GET' );
$schoolType = intval( GetPara( 'schoolType', 3, 'GET' ) );

$ret = GetPredict( $schools, $classes, $schoolType );

FinishByJson( array( 'results'=> $ret ));

//print_r($predictions);
//print_r($myGrade);

