#!/usr/bin/perl

$class = "departments (id,schoolId,type,schoolName,departmentName,class1,class2)";
$pre = 'INSERT OR REPLACE INTO '.$class.' VALUES (';
$suf = ');';

print "BEGIN TRANSACTION;\n";

while(<>) {
	chomp;
	if ( /^$/ ) { next; }
	@column = split /\t/;
	$column[0] =~ s/^(\d+)(\d\d\d)$/$1$2,$1/;
	$column[1] =~ s/^(.*)$/'$1'/;
	if( $column[1] =~ /^'國立/ ) {
		$column[1] = '1,'.$column[1];
	} else {
		$column[1] = '2,'.$column[1];
	}
	$column[2] =~ s/^(.*)$/'$1'/;
	$column[3] =~ s/\((\d+)\).*$/$1/;
	$column[4] =~ s/^$/null/;
	$column[4] =~ s/\((\d+)\).*$/$1/;
	print $pre.join(',',@column).$suf."\n";
}


print "COMMIT TRANSACTION;\n";
