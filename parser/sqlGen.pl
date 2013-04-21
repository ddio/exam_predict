#!/usr/bin/perl

$class = "departments (schoolId,name,class1)";
$pre = 'INSERT OR REPLACE INTO '.$class.' VALUES (';
$suf = ');';

while(<>) {
	chomp;
	s/\t/,/g;
	print $pre.$_.$suf."\n";
}
