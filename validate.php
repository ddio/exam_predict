<?php

require('lib.php');

function GetStd( $subject, $score ) {
	global $StdMap;
	if( array_key_exists( $subject, $StdMap ) ) {
		for( $std = 0; $std <= 5; $std++ ) {
			if( $score >= $StdMap[$subject][$std] )
				return $std;
		}
	}

	return 5;
}

$phase = GetPara( 'phase', 0 );

if( $phase == 1 ) {
	$emailPreg = '/^[^\W][a-zA-Z0-9_]+(\.[a-zA-Z0-9_]+)*\@[a-zA-Z0-9_]+(\.[a-zA-Z0-9_]+)*\.[a-zA-Z]{2,4}$/';
	$name = GetPara( 'name', '' );
	$phone = GetPara( 'phone', '' );
	$email = GetPara( 'email', '' );
	$city = GetPara( 'city', '' );

	if( $name == '' || $phone == '' || $email == '' || $city == '' ) {
		ToHome();
	}

	if( !preg_match( $emailPreg, $email ) ) {
		ToHome();
	}

	$userSql = 'insert or replace into accounts '.
				'(phone, name, email, city) values '.
				"('$phone', '$name', '$email', '$city')";

	$handle = $PredictDB->prepare($userSql);
	$handle->execute();

	$_SESSION['phase'] = 1;
	$_SESSION['phone'] = $phone;
	$_SESSION['email'] = $email;
	$_SESSION['name'] = $name;

	header( 'Location: grade.html' );

} else if( $phase == 2 ) {

	if( !isset($_SESSION['phase']) || $_SESSION['phase'] < 1 ) {
		ToHome();
	}

	$ch = intval( GetPara('ch', -1) );
	$en = intval( GetPara('en', -1) );
	$ma = intval( GetPara('ma', -1) );
	$s1 = intval( GetPara('s1', -1) );
	$s2 = intval( GetPara('s2', -1) );
	$classes = intval( GetPara('classes', 1) );
	$phone = $_SESSION['phone'];

	if( $ch < 0 || $en < 0 || $ma < 0 || $s1 < 0 || $s2 < 0 ) {
		ToHome();
	}

	$gradeSql = 'update accounts set '.
		"ch=$ch, en=$en, ma=$ma, s1=$s1, s2=$s2, classes='$classes' where phone='$phone'";
	$handle = $PredictDB->prepare( $gradeSql );
	$handle->execute();

	$_SESSION['phase'] = 2;
	$_SESSION['ch'] = $ch;
	$_SESSION['chStd'] = GetStd( 'ch', $ch );
	$_SESSION['en'] = $en;
	$_SESSION['enStd'] = GetStd( 'en', $en );
	$_SESSION['ma'] = $ma;
	$_SESSION['maStd'] = GetStd( 'ma', $ma );
	$_SESSION['s1'] = $s1;
	$_SESSION['s1Std'] = GetStd( 's1', $s1 );
	$_SESSION['s2'] = $s2;
	$_SESSION['s2Std'] = GetStd( 's2', $s2 );
	$_SESSION['toStd'] = GetStd( 'to', $ch+$en+$ma+$s1+$s2 );
	$_SESSION['classes'] = $classes;

	header( 'Location: predict.html' );

} else {
	ToHome();
}

