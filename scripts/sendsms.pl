#!/usr/bin/perl

use DBI;
use Device::Gsm;

my $dbuser   =  'ycppquiz';
my $dbpasswd = 'ycppquiz';
my $db       = 'ycppquiz';

$recipient = shift @ARGV;
$message = shift @ARGV;

if($recipient !~ /^[\+0-9]+$/) {
   print "Invalid recipient\n";
   exit();
}
if(!length($message)) {
   print "Message not specified\n";
   exit();
}

$dbh = DBI->connect("DBI:mysql:host=localhost:database=$db", $dbuser, $dbpasswd) or die 'Cant connect. '.$!;
# send SMS

if(get_modem_lock() != 1) {
        print "Aborting -- failed to acquire modem lock\n";
        exit();
}

my $gsm = new Device::Gsm( port => '/dev/ttyS0', log => 'file,/usr/local/grameen/logs/send.log', loglevel => 'info');
if(!$gsm->connect(baudrate => 19200)) {
   print "cant connect!\n";
   release_modem_lock();
   exit();
}
print "connected..\n";

# XXX It appears the line below is necessary otherwise messages are sent
# in an unreliable fashion
$gsm->mode('pdu');
$gsm->register();
$gsm->send_sms(
    recipient => $recipient,
    content   => $message,
    class     => 'normal',
# XXX It appears the line below is necessary otherwise messages are sent
# in an unreliable fashion
    mode      => 'pdu'
);
# Log SMS
$message =~ s/'/\\'/g;
 
$sql = "INSERT INTO smslog(date, userId, direction, message, recipient, status) ";
$sql .= "VALUES(CURRENT_TIMESTAMP(), 0, 'OUTGOING', '$message', '$recipient', NULL)";
$dbh->do($sql) or die 'Error '.$!;

print "$recipient\n$message\n";  
sleep(1);
release_modem_lock();

exit(); 

sub get_modem_lock {
        my $sqlstmt, $sths, $rows;

        $sqlstmt = "SELECT GET_LOCK('GSM_MODEM_SEND_LOCK', 600) AS mylock";
        $sths = $dbh->prepare($sqlstmt);
        if(!$sths) {
                die "Query failed";
        }
        $sths->execute();

        $rows = $sths->fetchrow_hashref();

        return $rows->{'mylock'};
}

sub release_modem_lock {
        my $sqlstmt;

        $sqlstmt = "SELECT RELEASE_LOCK('GSM_MODEM_SEND_LOCK')";
        $dbh->do($sqlstmt);
}

