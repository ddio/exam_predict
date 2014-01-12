<?php

$StdMap = array( 
	'ch' => array( 13,12,11, 9, 7, 0 ),
	'en' => array( 14,13,10, 6, 4, 0 ),
	'ma' => array( 12,10, 7, 4, 3, 0 ),
	'na' => array( 14,13,11, 9, 7, 0 ),
	'so' => array( 13,11, 9, 6, 5, 0 ),
	'to' => array( 63,57,47,36,27, 0 )
);

$SumMap = array(
	'ch' => array(
		'pre' => array( 152800, 152793, 152756, 152143, 150677, 148172, 144600, 139401, 132725, 123580, 111082, 93260, 69432, 42100, 18498, 4749 ),
		'cur' => array( 148060, 148058, 147995, 147421, 145840, 143109, 138812, 132692, 124246, 113178, 98525, 79792, 56881, 32726, 13697, 3777 )
	),
	'en' => array(
		'pre' => array( 152340, 152336, 152274, 150019, 138716, 128008, 120028, 111607, 102279, 91539, 80246, 66451, 51858, 36671, 22565, 8372 ),
		'cur' => array( 147750, 147742, 147676, 145554, 135552, 123551, 113925, 105462, 96064, 87200, 76912, 65495, 53294, 38100, 21319, 7713 )
	),
	'ma' => array(
		'pre' => array( 152640, 152590, 150655, 141467, 128783, 112663, 99671, 86159, 75430, 63305, 51859, 42048, 31164, 22781, 13282, 5867 ),
		'cur' => array( 147985, 147878, 144374, 134691, 120469, 108753, 94500, 81176, 67649, 51792, 39522, 27188, 18778, 11137, 6450, 2881 )
	),
	'so' => array(
		'pre' => array( 152600, 152597, 152596, 152593, 152542, 151769, 149472, 144852, 137870, 126347, 114092, 97579, 68467, 41942, 19368, 5902 ),
		'cur' => array( 147935, 147934, 147932, 147927, 147856, 147238, 143617, 137443, 126658, 116436, 104162, 83946, 64351, 38463, 20235, 5054 )
	),
	'na' => array(
		'pre' => array( 152294, 152289, 152278, 152020, 149098, 138917, 125423, 111193, 97497, 82711, 68901, 55296, 43156, 30840, 19272, 7299 ),
		'cur' => array( 147643, 147637, 147620, 147399, 145140, 136041, 121251, 106510, 91583, 76467, 61083, 46219, 32698, 21036, 11563, 4566 )
	),
	'to' => array(
		'pre' => array( 152932, 152930, 152928, 152910, 152883, 152854, 152823, 152807, 152771, 152745, 152709, 152676, 152640, 152595, 152521, 152429, 152265, 151938, 151430, 150743, 149751, 148578, 147106, 145558, 143859, 141939, 139942, 137902, 135813, 133733, 131697, 129609, 127547, 125419, 123406, 121212, 118983, 116636, 114258, 111613, 108816, 105999, 102856, 99642, 96325, 92991, 89434, 85712, 81964, 78149, 74285, 70372, 66453, 62477, 58548, 54626, 50743, 46856, 43029, 39289, 35666, 32132, 28707, 25418, 22249, 19164, 16262, 13599, 11027, 8624, 6521, 4668, 3092, 1826, 872, 288 ),
		'cur' => array( 148208, 148207, 148207, 148195, 148167, 148141, 148114, 148089, 148061, 148037, 148007, 147973, 147937, 147893, 147826, 147698, 147488, 147139, 146574, 145755, 144662, 143260, 141581, 139645, 137728, 135678, 133497, 131402, 129292, 127289, 125169, 123175, 121106, 119009, 116872, 114608, 112207, 109663, 106927, 104223, 101406, 98346, 95086, 91722, 88223, 84491, 80863, 77103, 73321, 69412, 65432, 61530, 57597, 53753, 49727, 45792, 41860, 37986, 34272, 30753, 27403, 24232, 21170, 18387, 15680, 13149, 10904, 8864, 7006, 5342, 3947, 2747, 1813, 1102, 526, 163 )
	)
);

$GradeProbCache = array();
$GradeMapCache = array();

$SubjectMap = array( '國'=>'ch', '英'=>'en', '數'=>'ma', '自'=>'na', '社'=>'so', '總'=>'to' );
$ClassMap = array( 
	1 => '大眾傳播學群',
	2 => '工程學群',
	3 => '文史哲學群',
	4 => '外語學群',
	5 => '生命科學學群',	//5
	6 => '地球與環境學群',
	7 => '法政學群',
	8 => '社會與心理學群',
	9 => '建築與設計學群',
	10 => '財經學群',	//10
	11 => '教育學群',
	12 => '資訊學群',
	13 => '生物資源學群',
	14 => '管理學群',
	15 => '數理化學群',	//15
	16 => '醫藥衛生學群',
	17 => '藝術學群',
	18 => '遊憩與運動學群',
	19 => '不分系學群' );

