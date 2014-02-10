<?php 

session_start();

if( !isset( $_SESSION['phase'] ) ) {
	$_SESSION['phase'] = 0;
	$_SESSION['schools'] = null;
	$_SESSION['classes'] = null;
	$_SESSION['phone'] = null;
	$_SESSION['ch'] = 0;
	$_SESSION['chStd'] = 5;
	$_SESSION['en'] = 0;
	$_SESSION['enStd'] = 5;
	$_SESSION['ma'] = 0;
	$_SESSION['maStd'] = 5;
	$_SESSION['na'] = 0;
	$_SESSION['naStd'] = 5;
	$_SESSION['so'] = 0;
	$_SESSION['soStd'] = 5;
	$_SESSION['to'] = 0;
	$_SESSION['toStd'] = 5;
}

$User = array();
$RECORD_LIMIT = 50;
$PredictDB = new PDO('sqlite:../../db/2014examPredict.sqlite');

require_once( 'def.php' );

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

function GetProbSingle( $subject, $preGrade, $curGrades ) {

	global $SubjectMap;
	global $SumMap;
	global $GradeProbCache;

	if( !isset( $SubjectMap[$subject] ) ) {
		return null;
	}

	$topGrade = $subject == '總' ? 75 : 15;
	$subject = $SubjectMap[ $subject ];
	$curGrade = $curGrades[ $subject ];

	if( !isset( $GradeProbCache[$subject] ) ) {
		$GradeProbCache[$subject] = array();
	}
	if( !isset( $GradeProbCache[$subject][$preGrade] ) ) {
		$curPos = 0;
		if( $curGrade < $topGrade ) {
			$curPos = $SumMap[$subject]['cur'][ $curGrade+1 ];
		}
		$preSumMap = $SumMap[$subject]['pre'];
		$preLoGrade = 0;
		while( $preLoGrade < $topGrade ) {
			if( $preSumMap[ $preLoGrade+1 ] > $curPos ) {
				$preLoGrade++;
			} else {
				break;
			}
		}
		if( $preLoGrade > $preGrade || $preLoGrade == $topGrade ) {
			$pob =  1;
		} else if( $preLoGrade < $preGrade ) {
			$pob = 0;
		} else {
			$pob = ( $preSumMap[$preLoGrade] - $curPos ) / 
					( $preSumMap[$preLoGrade] - $preSumMap[$preLoGrade+1] );
		}

		$GradeProbCache[$subject][$preGrade] = $pob;
	}
	
	return $GradeProbCache[$subject][$preGrade];
}

function GetProbMulti( $subjects, $preGrade, $curGrades ) {

	global $SubjectMap;
	global $SumMap;
	global $GradeMapCache;

	$curSum = 0;
	$totalTopGrade = 0;

	foreach( $subjects as $subject ) {
		if( !isset( $SubjectMap[ $subject ] ) ) {
			continue;
		}
		$topGrade = $subject == '總' ? 75 : 15;
		$subject = $SubjectMap[ $subject ];
		$curGrade = $curGrades[ $subject ];

		$totalTopGrade += $topGrade;

		if( !isset( $GradeMapCache[ $subject ] ) ) {
			$curPos = 0;
			if( $curGrade < $topGrade ) {
				$curPos = $SumMap[$subject]['cur'][ $curGrade+1 ];
			}
			$preSumMap = $SumMap[$subject]['pre'];
			$preLoGrade = 0;
			while( $preLoGrade < $topGrade ) {
				if( $preSumMap[ $preLoGrade+1 ] > $curPos ) {
					$preLoGrade++;
				} else {
					break;
				}
			}
			if( $preLoGrade == $topGrade ) {
				$GradeMapCache[ $subject ] = $topGrade;
			} else {
				$GradeMapCache[ $subject ] = $preLoGrade + 
						( $preSumMap[$preLoGrade] - $curPos ) / 
						( $preSumMap[$preLoGrade] - $preSumMap[$preLoGrade+1] );
			}
		}

		$curSum += $GradeMapCache[ $subject ];
	}

	$curDifprev = $curSum - $preGrade;

	if( $curDifprev >= 1 || $curSum == $totalTopGrade ) {
		return 1;
	} else if( $curDifprev == 0 ) {
		return 0.7;
	} else if( $curDifprev >= -1 ) {
		return 0.4;
	} else {
		return 0;
	}
}

function PassPhase( $phase, $row, $grade ) {
	global $SubjectMap;
	global $GradeProbCache;

	$subjectCol = "p$phase".'Subject';
	$lbCol = "p$phase"."Lb";
	$myGrade = 0;

	if( $row[$subjectCol] == null ) {
		return null;
	}
	
	$subjects = explode( '+', $row[$subjectCol] );

	if( count( $subjects ) == 1 ) {
		return GetProbSingle( $subjects[0], $row[ $lbCol ], $grade );
	} else if( count( $subjects ) > 1 ) {
		return GetProbMulti( $subjects, $row[ $lbCol ], $grade );
	} else {
		return null;
	}
}

function SortPredict( $p1, $p2 ) {

	if( $p1['info']['p0Lb'] == $p2['info']['p0Lb'] ) {

		if( $p1['dist']['to'] == $p2['dist']['to'] ) {

			$p1N = str_replace( '總', '++++', $p1['info']['p1Subject'] );
			$p1N = count( explode( '+', $p1N ) );
			$p1N = $p1N == 0 ? 5 : $p1N;
			
			$p2N = str_replace( '總', '++++', $p2['info']['p1Subject'] );
			$p2N = count( explode( '+', $p2N ) );
			$p2N = $p2N == 0 ? 5 : $p2N;
			
			$p1Score = $p1['info']['p1Lb']/$p1N;
			$p2Score = $p2['info']['p1Lb']/$p2N;

			if( $p1Score == $p2Score ) {

				if( $p1N == $p2N ) {
					return 0;
				} else {
					return $p1N > $p2N ? -1 : 1;
				}
			}

			return $p1Score > $p2Score ? -1 : 1;

		} else {
			return $p1['dist']['to'] > $p2['dist']['to'] ? 1 : -1; 
		}

	} else {
		return $p1['info']['p0Lb'] > $p2['info']['p0Lb'] ? -1 : 1;
	}
}

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
		'na' => $_SESSION['na'],
		'naStd' => $_SESSION['naStd'],
		'so' => $_SESSION['so'],
		'soStd' => $_SESSION['soStd'],
		'toStd' => $_SESSION['toStd']
	);

	$myGrade['to'] = $myGrade['ch'] + 
		$myGrade['en'] +
		$myGrade['ma'] +
		$myGrade['na'] +
		$myGrade['so'];

	$columns = 'd.schoolName,d.departmentName,d.acceptNum,sex,p2ExpectedPass,p1ExpectedPass,p1RealPass,lbCh,lbEn,lbMa,lbSo,lbNa,lbTo,p1Subject,p1Ratio,p1Lb,p2Subject,p2Ratio,p2Lb,p3Subject,p3Ratio,p3Lb,p4Subject,p4Ratio,p4Lb,p5Subject,p5Ratio,p5Lb,p0Lb,p2RealPass,p2SubPass,p2LbCount,toeic';

	$sql = '';

	//TODO: filter by std
	$sql = "select $columns from criteria as c, departments as d where c.departmentId = d.id";
	$sql .= " and c.lbCh >= ".$myGrade['chStd'];
	$sql .= " and c.lbEn >= ".$myGrade['enStd'];
	$sql .= " and c.lbMa >= ".$myGrade['maStd'];
	$sql .= " and c.lbNa >= ".$myGrade['naStd'];
	$sql .= " and c.lbSo >= ".$myGrade['soStd'];
	$sql .= " and c.lbTo >= ".$myGrade['toStd'];

	$acsql = array();

	if( $classes !== null ) {
		$pattern = '/^$|^\d+(,\d+)*$/';
		if( preg_match( $pattern, $classes ) ) {
			if( $_SESSION['classes'] != $classes ) {
				$acsql[] = "classes='$classes'";
				$_SESSION['classes'] = $classes;
			}
			$sql .= " and ( d.class1 in ($classes) or d.class2 in ($classes) )";
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
			if( $allDist[$phase] !== null && $allDist[$phase] <= 0 ) {
				$pass = false;
				break;
			}
		}
		if( $qRes['p0Lb'] !== null ) {
			$allDist[0] = GetProbSingle( '總', $qRes['p0Lb'], $myGrade );
			if( $allDist[0] <= 0 ) $pass = false;
		} else {
			$allDist[0] = 1;
		}

		if( $pass ) {
			$minDist = 1;
			$toDist = 1;
			for( $i = 1; $i <= 5; $i++ ) {
				if( $allDist[$i] !== null && $allDist[$i] >= 0 ) {
					if( $minDist > $allDist[$i] ) 
						$minDist = $allDist[$i];
					$toDist *= $allDist[$i];
				}
			}
			$allDist['to'] = $toDist;
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

	$lvToStr = array( '極低', '低', '普通', '高', '極高' );

	foreach( $predictionsLimited as &$aPrediction ) {
		$totalP = floor( ($aPrediction['dist']['to']*100)/25 );
		$totalP = $totalP > 4 ? 4 : $totalP;
		$aPrediction['dist'] = array( 'cheet' => $aPrediction['dist']['to'], 'level' => $totalP, 'text' => $lvToStr[$totalP] );
	}

	return $predictionsLimited;
}

