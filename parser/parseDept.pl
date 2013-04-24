#!/usr/bin/perl

$class = "departments (id,schoolId,type,schoolName,departmentName,class1)";
$pre = 'INSERT OR REPLACE INTO '.$class.' VALUES (';
$suf = ');';

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
		my ($school,$class1) = split(/,/, $l);

		$class1 =~ s/(\d+).*$/$1/;

		$school =~ /^([0-9A-Z]{3})([0-9A-Z]{3})(.*大學|.*學院(）)?|.*學校)(.*)$/;

		my $schoolId = $1;
		my $depId = $2;
		my $schoolName = $3;
		my $depName = $5;
		my $type = 2;

		if( $schoolName =~ /國立/ ) {
			$type = 1;
		}

		if( $schoolName =~ /^$/ || $depName =~ /^$/ ) {
			print STDERR "mismatch [$ln] $l in file $ARGV[$ai]\n"
		} else {

			print $pre
					."'$schoolId$depId','$schoolId',$type,"
					."'$schoolName','$depName',$class1"
				.$suf."\n";
		}
	}
}

print "COMMIT TRANSACTION;\n";
