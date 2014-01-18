<?php

require('class.phpmailer.php');
require('class.smtp.php');
require('lib.php');

mb_internal_encoding('UTF-8');

function tos( $str ) {
	return mb_encode_mimeheader($str, 'UTF-8');
}

function genHtml() {
	$results = GetPredict( $_SESSION['schools'], $_SESSION['classes'], $_SESSION['schoolType'] );
	$url = $_SERVER['HTTP_HOST'].dirname($_SERVER['SCRIPT_NAME']);
	ob_start();?>

<a href="http://www.urfuture.com.tw"><img class="header" src="http://www.urfuture.com.tw/wp-content/themes/bluediamond/images/default-logo.png" alt="卓遠教育顧問中心" /></a>
<div id="school-detail" class="columns twelve">
<?php if ( count($results) == 0 ): ?>
	<p id="not-found"> 查無結果 </p>
<?php else: ?>
<?php foreach( $results as $result ): ?>
<?php 
$in = $result['info'];
$dst = $result['dist']; 
$pb='font-size:0.9em;padding:1px 3px;color:#fff;';
$pbl=$pb.'border-top-left-radius:3px;border-bottom-left-radius:3px;';
$pbr=$pb.'border-top-right-radius:3px;border-bottom-right-radius:3px;';
$pbls=array( $pbl.'background-color:#f00;', $pbl.'background-color:#f00;', $pbl.'background-color:#fa0;', $pbl.'background-color:#fa0;', $pbl.'background-color:#0f0;color:#000;' );
$pbrs=array( $pbr.'background-color:#f00;', $pbr.'background-color:#fa0;', $pbr.'background-color:#fa0;', $pbr.'background-color:#0f0;color:#000;', $pbr.'background-color:#0f0;color:#000;' );
?>
	<div class="row" style="margin: 10px;0px;">
		<div class="title">
			<span style="font-size:1.2em;color:#f90;"><?php echo $in['schoolName']?></span> 
			<span style="font-size:1.2em;color:#f90;"><?php echo $in['departmentName']?></span>
			<span style="<?php echo $pbls[$dst['level']]?>">機率</span><span style="<?php echo $pbrs[$dst['level']]?>"><?php echo $dst['text']?></span>
		</div>
		<div class="quota" style="float:left;width:13%;">
			<strong><span class="long-name">名額</span>異動</strong><br />
			<span><?php echo $in['p2ExpectedPass']?></span>-<span><? echo $in['acceptNum'] ?></span>
		</div>
		<?php for( $phase = 1; $phase <= 5; $phase++ ) : ?>
		<?php $p = 'p'.$phase; ?>
		<?php if( $in[$p.'Subject'] ): ?>
			<div class="criterion" style="float:left;width:15%;">
			<strong><span class="long-name">篩選</span>順序<?php echo $phase?></strong><br />
				<dl>
					<dt style="width:30%;float:left;color:#777;"> 科目 </dt> 
					<dd style="width:68%;margin-left:2%;float:left;"><?php echo $in[$p.'Subject']?></dd>
					<dt style="width:30%;float:left;color:#777;"> 倍率 </dt> 
					<dd style="width:68%;margin-left:2%;float:left;"><?php echo $in[$p.'Ratio']?></dd>
					<dt style="width:30%;float:left;color:#777;"> 去年 </dt> 
					<dd style="width:68%;margin-left:2%;float:left;"><?php echo $in[$p.'Lb']?></dd>
					<span style="clear: both"></span>
				</dl>
			</div>
		<?php endif; ?>
		<?php endfor; ?>
		<?php if( $in['toeic'] == 1 ): ?>
			<div class="toeic" style="float:left;width:12%;">
				<strong>有</strong><br />TOEIC<span class="long-name"><br />第二階段<br />加分</span>
			</div>
		<?php endif; ?>
	</div>
	<div style="clear: both"></div>
<?php endforeach; ?>
<?php endif; ?>
</div>

<?php
	$html = ob_get_contents();
	ob_end_clean();

	return $html;
}

require( 'conf.php' );

$mail  = new PHPMailer();   
$mail->IsSMTP();

$mail->SMTPAuth   = true;
$mail->Host       = $Mail['host'];
$mail->Username   = $Mail['username'];
$mail->Password   = $Mail['password'];

$mail->From       = $Mail['from'];
$mail->FromName   = tos("卓遠教育顧問中心");
$mail->Subject    = tos("落點分析結果");
$mail->IsHTML(true);

$mail->AddAddress($_SESSION['email'], tos($_SESSION['name']) );

$mail->Body = genHtml() ;

FinishByJson( array( 'result' => $mail->Send(), 'error' => $mail->ErrorInfo ) );

