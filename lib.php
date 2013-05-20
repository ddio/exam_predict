<?php 

session_start();

if( !isset( $_SESSION['phase'] ) ) {
	$_SESSION['phase'] = 0;
	$_SESSION['schools'] = null;
	$_SESSION['classes'] = null;
	$_SESSION['phone'] = null;
	$_SESSION['ch'] = 0;
	$_SESSION['chStd'] = 2;
	$_SESSION['en'] = 0;
	$_SESSION['enStd'] = 2;
	$_SESSION['ma'] = 0;
	$_SESSION['maStd'] = 2;
	$_SESSION['na'] = 0;
	$_SESSION['naStd'] = 2;
	$_SESSION['so'] = 0;
	$_SESSION['soStd'] = 2;
	$_SESSION['to'] = 0;
	$_SESSION['toStd'] = 2;
}

$User = array();
$RECORD_LIMIT = 50;
$PredictDB = new PDO('sqlite:../../db/techPredict.sqlite');

// not used..
$StdMap = array( 
	'ch' => array( 13,12,11,9,7,0 ),
	'en' => array( 14,13,10,6,4,0 ),
	'ma' => array( 12,10, 7,4,3,0 ),
	's1' => array( 14,13,11,9,7,0 ),
	's2' => array( 13,11, 9,6,5,0 ),
	'to' => array( 63,57,47,36,27,0 )
);


$SubjectMap = array( '國'=>'ch', '英'=>'en', '數'=>'ma', '一'=>'s1', '二'=>'s2' );

function GetClasses() {

	global $PredictDB;
	$ret = array();
	$sql = 'SELECT distinct id, name FROM classes order by id;';

	$queryResult = $PredictDB->prepare($sql);
	$queryResult->execute();

	while( $qRes = $queryResult->fetch( PDO::FETCH_ASSOC ) ) {
		$ret[$qRes['id']] = $qRes['name'];
	}

	return $ret;
}

function ToHome() {
	header( 'Location: user.html' );
	exit(0);
}

function GetPara( $Name, $Default = null, $Method = 'POST' ) {

	if( $Method == 'GET' ) {
		return isset($_GET[$Name]) ? $_GET[$Name] : $Default;
	} else if( $Method == 'POST' ) {
		return isset($_POST[$Name]) ? $_POST[$Name] : $Default;
	} else {
		return $Default;
	}
}

function FinishByJson( $jsonArray, $jsonEncodeOpts = 0 ) {
	header('Content-type: application/json; charset=utf-8');
	echo json_encode( $jsonArray );
}

function PassPhase( $phase, $row, $grade ) {
	global $SubjectMap;
	$subjectCol = "p$phase".'Subject';
	$lbCol = "p$phase"."Lb";
	$myGrade = 0;

	if( $row[$subjectCol] != null ) {
		$subjectStr = str_replace( '總', '國+英+數+一+二', $row[$subjectCol] );
		$subjects = explode( '+', $subjectStr );
		foreach( $subjects as $subject ) {
			if( isset( $SubjectMap[$subject] ) ) {
				$myGrade += $grade[ $SubjectMap[$subject] ];
			}
		}

		return ($myGrade - $row[$lbCol]);///(count($subjects)-1);
	} else {
		return null;
	}
}

function SortPredict( $p1, $p2 ) {
	if( $p1['info']['p0Lb'] == $p2['info']['p0Lb'] ) {
		if( $p1['dist']['to'] == $p2['dist']['to'] ) 
			return 0;
		return $p1['dist']['to'] > $p2['dist']['to'] ? 1 : -1; 
	} else {
		return $p1['info']['p0Lb'] > $p2['info']['p0Lb'] ? -1 : 1;
	}
}

define( 'LOW_BOUND', -2 );

function GetPredict( $schools, $classes, $schoolType ) {

	global $PredictDB;
	global $RECORD_LIMIT;

	$myGrade = array(
		'ch' => $_SESSION['ch'],
		'chStd' => $_SESSION['chStd'],
		'en' => $_SESSION['en'],
		'enStd' => $_SESSION['enStd'],
		'ma' => $_SESSION['ma'],
		'maStd' => $_SESSION['maStd'],
		's1' => $_SESSION['s1'],
		's1Std' => $_SESSION['s1Std'],
		's2' => $_SESSION['s2'],
		's2Std' => $_SESSION['s2Std'],
		'toStd' => $_SESSION['toStd']
	);

	$myGrade['to'] = $myGrade['ch'] + 
		$myGrade['en'] +
		$myGrade['ma'] +
		$myGrade['s1'] +
		$myGrade['s2'];

	$columns = 'd.schoolName,d.departmentName,'.
		'lbCh,lbEn,lbMa,lbS1,lbS2,lbTo,'.
		'p1Subject,p1Ratio,p1Lb,'.
		'p2Subject,p2Ratio,p2Lb,'.
		'p3Subject,p3Ratio,p3Lb,'.
		'p4Subject,p4Ratio,p4Lb,'.
		'p5Subject,p5Ratio,p5Lb,'.
		'p0Lb';

	$sql = '';

	//TODO: filter by std
	$sql = "select $columns from criteria as c, departments as d where c.departmentId = d.id";
	$sql .= " and c.lbCh >= ".$myGrade['chStd'];
	$sql .= " and c.lbEn >= ".$myGrade['enStd'];
	$sql .= " and c.lbMa >= ".$myGrade['maStd'];
	$sql .= " and c.lbS1 >= ".$myGrade['s1Std'];
	$sql .= " and c.lbS2 >= ".$myGrade['s2Std'];
	$sql .= " and c.lbTo >= ".$myGrade['toStd'];

	$acsql = array();

	if( $classes !== null ) {
		$pattern = '/^$|^\d+(,\d+)*$/';
		if( preg_match( $pattern, $classes ) ) {
			if( $_SESSION['classes'] != $classes ) {
				$acsql[] = "classes='$classes'";
				$_SESSION['classes'] = $classes;
			}
			$sql .= " and ( d.class1 in ($classes) )";
		}
	}

	if( $schools !== null ) {
		$pattern = '/^$|^\d+(,\d+)*$/';
		if( preg_match( $pattern, $schools ) ) {
			if( $_SESSION['schools'] != $schools ) {
				$acsql[] = "schools='$schools'";
				$_SESSION['schools'] = $schools;
			}
			$sql .= " and d.schoolId in ($schools)";
		}
	}

	if( count($acsql) > 0 ) {
		$isql = 'update accounts set '.implode( ',', $acsql )." where phone='".$_SESSION['phone']."'";
		$handle = $PredictDB->prepare( $isql );
		$handle->execute();
	}

	if( $schoolType <= 3 && $schoolType > 0 ) {
		$_SESSION['schoolType'] = $schoolType;

		if( $schoolType < 3 ) {
			$sql .= " and d.type = $schoolType";
		}
	}

	$queryResult = $PredictDB->prepare($sql);
	$queryResult->execute();

	$predictions = array();

	while( $qRes = $queryResult->fetch( PDO::FETCH_ASSOC ) ) {
		$pass = true;
		$allDist = array();
		for( $phase = 1; $phase <= 5; $phase++ ) {
			$allDist[$phase] = PassPhase( $phase, $qRes, $myGrade );
			if( $allDist[$phase] !== null && $allDist[$phase] < LOW_BOUND ) {
				$pass = false;
				break;
			}
		}
		if( $qRes['p0Lb'] !== null ) {
			$allDist[0] = ($myGrade['to'] - $qRes['p0Lb']);
			if( $allDist[0] < LOW_BOUND ) $pass = false;
		} else {
			$allDist[0] = LOW_BOUND;
		}

		if( $pass ) {
			$minDist = 15*5;
			$seeDist = 0;
			$toDist = 0;
			for( $i = 1; $i <= 5; $i++ ) {
				if( $allDist[$i] !== null && $allDist[$i] >= LOW_BOUND ) {
					if( $minDist > $allDist[$i] ) 
						$minDist = $allDist[$i];
					$seeDist++;
					$toDist += $allDist[$i];
				}
			}
			$seeDist = $seeDist == 0 ? 1 : $seeDist;
			$allDist['to'] = $toDist/$seeDist;
			$allDist['min'] = $minDist;
			$predictions[] = array(
				'dist' => $allDist,
				'info' => $qRes
			);
		}
	}

	usort($predictions, 'SortPredict' );

	if( count($predictions) > $RECORD_LIMIT ) {
		$predictionsLimited = array_slice( $predictions, 0, $RECORD_LIMIT );
	} else {
		$predictionsLimited = $predictions;
	}

	return $predictionsLimited;
}

