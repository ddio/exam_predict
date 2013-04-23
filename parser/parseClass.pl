#!/usr/bin/perl

$class = "classes (id,name)";
$pre = 'INSERT OR REPLACE INTO '.$class.' VALUES (';
$suf = ');';

print "BEGIN TRANSACTION;\n";

%classes = (  );

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

		my $classId = $class1;
		my $className = $class1;

		$classId =~ s/(\d+).+$/$1/;
		$className =~ s/\d+.//;

		$school =~ /^([0-9A-Z]{3})([0-9A-Z]{3})(.*大學|.*學院|.*學校)(.*)$/;

		my $schoolId = $1;
		my $depId = $2;
		my $schoolName = $3;
		my $depName = $4;
		my $type = 2;

		if( $schoolName =~ /國立/ ) {
			$type = 1;
		}

		if( $schoolName =~ /^$/ || $depName =~ /^$/ ) {
			print STDERR "mismatch [$ln] $l in file $ARGV[$ai]\n"
		} elsif( !exists $classes{$classId} ) {

			$classes{$classId} = $className;

			print $pre
					."$classId,'$className'"
				.$suf."\n";
		}
	}
}

print "COMMIT TRANSACTION;\n";
