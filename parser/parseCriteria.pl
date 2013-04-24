#!/usr/bin/perl

use File::Basename;
use DBI;
use Data::Dumper;

$class = "criteria (departmentId,year,".
					"lbCh,lbEn,lbMa,lbS1,lbS2,lbTo,".
					"p1Subject,p1Ratio,p1Lb,".
					"p2Subject,p2Ratio,p2Lb,".
					"p3Subject,p3Ratio,p3Lb,".
					"p4Subject,p4Ratio,p4Lb,".
					"p5Subject,p5Ratio,p5Lb,".
					"p0Lb)";

$pre = 'INSERT OR REPLACE INTO '.$class.' VALUES (';
$suf = ');';

%std = ( "全" => 0, "後"=>1, "--"=>2 );

%departments = ();

print "BEGIN TRANSACTION;\n";

for( $ai = 0; $ai <= $#ARGV; $ai++ ) {

	unless ( -e $ARGV[$ai] ) { 
		print STDERR "no such file $ARGV[$ai]\n";
		next;
	}
	
	open FILE, $ARGV[$ai];
	<FILE>;
	<FILE>;

	my $ln = 3;

	while(my $l = <FILE>) {
	
		$ln++;

		chomp $l;
		if ( $l =~ /^$/ ) { next; }
		$l =~ s/"//g;
		@c = split(/,/, $l);

		$c[0] =~ /^([0-9A-Z]{6})/;
		my $depId = $1;
		my $acceptNum = $c[2];
		my $lbCh = $std{ $c[4] };
		my $lbEn = $std{ $c[5] };
		my $lbMa = $std{ $c[6] };
		my $lbS1 = $std{ $c[7] };
		my $lbS2 = $std{ $c[8] };
		my $lbTo = $std{ $c[9] };

		my %ratio = (
			'國' => $c[10],
			'英' => $c[11],
			'數' => $c[12],
			'一' => $c[13],
			'二' => $c[14],
			'總' => $c[15],
		);

		my $p1Subject = 'NULL';
		my $p1Ratio = 'NULL';
		my $p1Lb = 'NULL';
		my $p2Subject = 'NULL';
		my $p2Ratio = 'NULL';
		my $p2Lb = 'NULL';
		my $p3Subject = 'NULL';
		my $p3Ratio = 'NULL';
		my $p3Lb = 'NULL';
		my $p4Subject = 'NULL';
		my $p4Ratio = 'NULL';
		my $p4Lb = 'NULL';
		my $p5Subject = 'NULL';
		my $p5Ratio = 'NULL';
		my $p5Lb = 'NULL';
		my $p0Lb = 'NULL';

		if( $c[16] =~ /^(.*)\((\d+)\)/ ) {
			$p1Subject = $1;
			$p1Lb = $2;
			$p1Subject =~ s/英文/英+/g;
			$p1Subject =~ s/數學/數+/g;
			$p1Subject =~ s/國文/國+/g;
			$p1Subject =~ s/專一/一+/g;
			$p1Subject =~ s/專二/二+/g;

			$p1Subject =~ s/總級分/總+/g;
			$p1Subject =~ /^([^+]+)/;
			$p1Ratio = $ratio{$1};
			$p1Ratio =~ s/^(\d+)/$1/;

			$p1Subject =~ s/(.*)/'$1'/;
		}

		if( $c[17] =~ /^(.*)\((\d+)\)/ ) {
			$p2Subject = $1;
			$p2Lb = $2;
			$p2Subject =~ s/英文/英+/g;
			$p2Subject =~ s/數學/數+/g;
			$p2Subject =~ s/國文/國+/g;
			$p2Subject =~ s/專一/一+/g;
			$p2Subject =~ s/專二/二+/g;

			$p2Subject =~ s/總級分/總+/g;
			$p2Subject =~ /^([^+]+)/;
			$p2Ratio = $ratio{$1};
			$p2Ratio =~ s/^(\d+)/$1/;

			$p2Subject =~ s/(.*)/'$1'/;
		}
		if( $c[18] =~ /^(.*)\((\d+)\)/ ) {
			$p3Subject = $1;
			$p3Lb = $2;
			$p3Subject =~ s/英文/英+/g;
			$p3Subject =~ s/數學/數+/g;
			$p3Subject =~ s/國文/國+/g;
			$p3Subject =~ s/專一/一+/g;
			$p3Subject =~ s/專二/二+/g;

			$p3Subject =~ s/總級分/總+/g;
			$p3Subject =~ /^([^+]+)/;
			$p3Ratio = $ratio{$1};
			$p3Ratio =~ s/^(\d+)/$1/;

			$p3Subject =~ s/(.*)/'$1'/;
		}
		if( $c[19] =~ /^(.*)\((\d+)\)/ ) {
			$p4Subject = $1;
			$p4Lb = $2;
			$p4Subject =~ s/英文/英+/g;
			$p4Subject =~ s/數學/數+/g;
			$p4Subject =~ s/國文/國+/g;
			$p4Subject =~ s/專一/一+/g;
			$p4Subject =~ s/專二/二+/g;

			$p4Subject =~ s/總級分/總+/g;
			$p4Subject =~ /^([^+]+)/;
			$p4Ratio = $ratio{$1};
			$p4Ratio =~ s/^(\d+)/$1/;

			$p4Subject =~ s/(.*)/'$1'/;
		}
		if( $c[20] =~ /^(.*)\((\d+)\)/ ) {
			$p5Subject = $1;
			$p5Lb = $2;
			$p5Subject =~ s/英文/英+/g;
			$p5Subject =~ s/數學/數+/g;
			$p5Subject =~ s/國文/國+/g;
			$p5Subject =~ s/專一/一+/g;
			$p5Subject =~ s/專二/二+/g;

			$p5Subject =~ s/總級分/總+/g;
			$p5Subject =~ /^([^+]+)/;
			$p5Ratio = $ratio{$1};
			$p5Ratio =~ s/^(\d+)/$1/;

			$p5Subject =~ s/(.*)/'$1'/;
		}
		if( $c[21] =~ /^(.*)\((\d+)\)/ ) {
			$p0Lb = $2;
		}

		if( $p1Subject =~ /NULL/ && $p0Lb =~ /NULL/ ) {
			print STDERR "mismatch $l in file $ARGV[$ai]\n"
		} else {

			print 	$pre.
					"'$depId', 101, ".
					"$lbCh, $lbEn, $lbMa, $lbS1, $lbS2, $lbTo, ".
					"$p1Subject, $p1Ratio, $p1Lb,". 
					"$p2Subject, $p2Ratio, $p2Lb,". 
					"$p3Subject, $p3Ratio, $p3Lb,". 
					"$p4Subject, $p4Ratio, $p4Lb,". 
					"$p5Subject, $p5Ratio, $p5Lb,". 
					"$p0Lb". 
					$suf."\n";
		}
	}

	close FILE
}

print "COMMIT TRANSACTION;\n";
