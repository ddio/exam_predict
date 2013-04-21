#!/usr/bin/perl

use File::Basename;
use DBI;
use Data::Dumper;

$class = "criteria (sex,departmentId,year,".
					"p2ExpectedPass,p1ExpectedPass,p1RealPass," .
					"lbCh,lbEn,lbMa,lbSo,lbNa,lbTo,".
					"p1Subject,p1Ratio,p1Lb,".
					"p2Subject,p2Ratio,p2Lb,".
					"p3Subject,p3Ratio,p3Lb,".
					"p4Subject,p4Ratio,p4Lb,".
					"p5Subject,p5Ratio,p5Lb,".
					"p0Lb,p2RealPass,p2SubPass,p2LbCount,toeic)";
$pre = 'INSERT OR REPLACE INTO '.$class.' VALUES (';
$suf = ');';

%std = ( "頂" => 0, "前"=>1, "均"=>2, "後"=>3, "底"=>4, "" => 5 );

%departments = ();

$dbh = DBI->connect( "dbi:SQLite:dbname=../../db/examPredict.sqlite" ) || die "Cannot connect: $DBI::errstr";
$scQuery = $dbh->prepare( 'select * from departments' );
$scQuery->execute();

while( @result = $scQuery->fetchrow_array() ) {
	$departments{ $result[1].$result[2] } = $result[0];
}

print "BEGIN TRANSACTION;\n";
#print Dumper(\%departments);
for( $ai = 0; $ai <= $#ARGV; $ai++ ) {

	unless ( -e $ARGV[$ai] ) { 
		print STDERR "no such file $ARGV[$ai]\n";
		next;
	}
	
	$schoolName = basename($ARGV[$ai]);
	$schoolName =~ s/.csv$//;

	open FILE, $ARGV[$ai];

	while(<FILE>) {
		chomp;
		s/ |\r//g;
		s/}/)/g;
		s/{/(/g;
		if ( /^ *$/ ) { next; }
		if ( /^,*$/ ) { next; }
		@column = split /,/;

		if ( $column[1] =~ /100/ ) { next; }

		if ( $column[1] =~ /$ *^/ || $column[2] =~ /$ *^/ ) { 
#			print STDERR "skipping empty $schoolName/$column[0]: $column[1]\n";
			next; 
		}

		if ( $column[0] =~ /^(.*)（(男|女)）/ ) {
			$dep = $1;
#			print STDERR "dectect sex $schoolName $dep\n";
			$column[0] = "1,'$dep'" if $2 =~ /男/;
			$column[0] = "2,'$dep'" if $2 =~ /女/;
		} else {
#			$column[0] =~ s/^([^（]+)[（]?.*$/3,'$1'/;
			$column[0] = "3,'$column[0]'";
		}
		$column[0] =~ /'(.*)'/;
		$dep = $schoolName.$1;
		unless ( exists( $departments{$dep} ) ) {
#			print STDERR "no such dept $dep\n";
			next;
		}
		$column[0] =~ s/'.*'/$departments{$dep}/;
	
		$column[2] =~ s/^[^0-9]*(\d+)[^0-9]*$/$1/;	#招生名額

		# lb *
		$column[5] = $std{ $column[5] };
		$column[6] = $std{ $column[6] };
		$column[7] = $std{ $column[7] };
		$column[8] = $std{ $column[8] };
		$column[9] = $std{ $column[9] };
		$column[10] = $std{ $column[10] };

		$column[11] =~ s/\(([.0-9]+)\)(.*)/'$2',$1/;
#		$column[11] =~ s/總/國+英+數+自+社/;
		$column[13] =~ s/\(([.0-9]+)\)(.*)/'$2',$1/;
#		$column[13] =~ s/總/國+英+數+自+社/;
		$column[15] =~ s/\(([.0-9]+)\)(.*)/'$2',$1/;
#		$column[15] =~ s/總/國+英+數+自+社/;
		$column[17] =~ s/\(([.0-9]+)\)(.*)/'$2',$1/;
#		$column[17] =~ s/總/國+英+數+自+社/;
		$column[19] =~ s/\(([.0-9]+)\)(.*)/'$2',$1/;
#		$column[19] =~ s/總/國+英+數+自+社/;

		for( $i = 11; $i <= 20; $i++ ) {
			if( $column[$i] =~ /^$/ ) {
				$column[$i] = "NULL,NULL" if $i % 2 == 1;
				$column[$i] = "NULL" if $i % 2 == 0;
			}
		}

		$column[21] =~ s/-|－|—|^0$|不足額/NULL/; #超額篩選
		$column[21] =~ s/<|u//;	#超額篩選
		$column[23] =~ s/^$/0/;	#備取人數
		$column[24] =~ s/備.*(\d+)/$1/;	# 分發最低標準
		$column[24] =~ s/正取|IE取|無/0/;

		$column[25] =~ s/V/1/;	#toeic
		$column[25] =~ s/^$/0/;

		print $pre.join(',',@column).$suf."\n";
	}

	close FILE
}

print "COMMIT TRANSACTION;\n";
