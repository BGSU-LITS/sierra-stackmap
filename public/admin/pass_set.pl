#!/usr/bin/perl

print "\n---Jared's .htpasswd generator v1.337---";
 
print "\nPlease enter the desired username: ";
chomp(my $user = <STDIN>);

print "Please enter the desired password: ";
chomp(my $pass = <STDIN>);

print "\nNow you need to enter a random 'salt'...";
print "\nA salt is a set of two random alphanumerics.";
print "\nPlease enter two characters [0-9], [a-z], or [A-Z]: ";
chomp(my $salt = <STDIN>);

my $crypted = crypt($pass, $salt); #encrypt the password 

$htpasswd_string = $user . ":" . $crypted;

open (HTPASSWD, ">.htpasswd") || die ("Cannot open .htpasswd file!\n");
print HTPASSWD $htpasswd_string;
close (HTPASSWD);

print "\nYou entered $user! The htpasswd file will have " . $user . 
":" . $crypted . "\n";
