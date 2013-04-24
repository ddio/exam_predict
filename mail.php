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
<?php $in = $result['info']; ?>
	<div class="row" style="margin: 10px;0px;">
		<div class="title">
			<span style="font-size:1.2em;color:#f90;"><?php echo $in['schoolName']?></span> 
			<span style="font-size:1.2em;color:#f90;"><?php echo $in['departmentName']?></span>
			<?php if ( $result['dist']['min'] < 1 ): ?>
				<strong style="font-size:0.9em;color:red;"><span class="long-name">(錄取</span>邊緣<span class="long-name">)</span></strong>
			<?php endif; ?>
		</div>
		<div class="quota" style="float:left;width:13%;">
			<strong><span class="long-name">名額</span>異動</strong><br />
			<span><?php echo '??'?></span>-<span><? echo '??' ?></span>
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

