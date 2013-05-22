<?php 
require('lib.php');
require('conf.php');
if( isset( $_POST['pass'] ) && $_POST['pass'] == $Log['password'] ) {
	$_SESSION['isAdmin'] = true;
}
?><!DOCTYPE HTML>
<!--[if lt IE 7 ]><html class="ie ie6" lang="zh-TW"> <![endif]-->
<!--[if IE 7 ]><html class="ie ie7" lang="zh-TW"> <![endif]-->
<!--[if IE 8 ]><html class="ie ie8" lang="zh-TW"> <![endif]-->
<!--[if (gte IE 9)|!(IE)]><!--><html lang="zh-TW"> <!--<![endif]-->
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
	<title>卓遠教育顧問 | 落點分析</title>
	<link rel="stylesheet" href="css/base.css">
	<link rel="stylesheet" href="css/skeleton.css">
	<link rel="stylesheet" href="css/layout.css">

	<!--[if lt IE 9]>
		<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
	<![endif]-->
</head>
<body>
<?php if ( !isset($_SESSION['isAdmin']) ) : ?>
	<div class="container" id="user-page">
		<div class="columns offset-by-four eight">
			<div class="wrapper row">
				<div class="columns offset-by-one six">
					<form action="log.php" method="POST">
					密碼<input name="pass" type="password" /><input type="submit" />
					</form>
				</div>
			</div>
		</div>
	</div>
<?php else : ?>
	<div id="contact">
<?php 
$r0 = $PredictDB->query('select count(*) from accounts');

$sql = 'select id, name from classes';
$cr = $PredictDB->prepare($sql);
$cr->execute();

$classes = array();
while( $qCr = $cr->fetch( PDO::FETCH_ASSOC ) ) {
	$classes[$qCr['id']] = $qCr['name'];
}

function className( $id ) {
	global $classes;

	if( isset( $classes[$id] ) ) {
		return $classes[$id];
	} else {
		return '--';
	}
}

$sql = 'select phone, name, email, city, ch, en,ma,s1,s2, classes from accounts';
$result = $PredictDB->prepare($sql);
$result->execute();
?>
	<p> 目前為止共有 <strong><?php echo $r0->fetchColumn(); ?></strong> 人使用 </p>
	<table>
		<thead>
		<tr style="">
			<th>姓名</th>
			<th>電話</th>
			<th>email</th>
			<th>居住地</th>
			<th>學群</th>
			<th>國</th>
			<th>英</th>
			<th>數</th>
			<th>專一</th>
			<th>專二</th>
		</tr>
		</thead>
		<tbody>
<?php while( $qRes = $result->fetch( PDO::FETCH_ASSOC ) ): ?>
		<tr>
			<td><?php echo $qRes['name'] ?></td>
			<td><?php echo $qRes['phone'] ?></td>
			<td><?php echo $qRes['email'] ?></td>
			<td><?php echo $qRes['city'] ?></td>
			<td><?php echo className($qRes['classes']) ?></td>
			<td><?php echo $qRes['ch'] ?></td>
			<td><?php echo $qRes['en'] ?></td>
			<td><?php echo $qRes['ma'] ?></td>
			<td><?php echo $qRes['s1'] ?></td>
			<td><?php echo $qRes['s2'] ?></td>
		</tr>
<?php endwhile; ?>
		</tbody>
	</table>
	</div>
<?php endif; ?>
</body>
</html>
