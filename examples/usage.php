<?php


use xobotyi\beansclient\Beanstalkd;
use xobotyi\beansclient\Client;
use xobotyi\beansclient\Socket\SocketsSocket;

include_once __DIR__ . '/../vendor/autoload.php';

$sock   = new SocketsSocket(host: 'localhost', port: 11300, connectionTimeout: 2);
$client = new Client(socket: $sock, defaultTube: 'myAwesomeTube');


##            ##
#   PRODUCER   #
##            ##

$job = $client->put("job's payload", delay: 2);
if($job['state'] === Beanstalkd::JOB_STATE_DELAYED) {
  echo "Job {$job['id']} is ready to be reserved within 2 seconds\n";
}

##            ##
#    WORKER    #
##            ##

$client->watchTube('myAwesomeTube2');

$job = $client->reserve();

if ($job) {
  echo "Hey, i received first {$job['payload']} of job with id {$job['id']}\n";

  $jobStats = $client->statsJob($job['id']);
  echo sprintf(
    'It will be released by server in %d seconds, , it\'s ttr is %d',
    $jobStats['time-left'],
    $jobStats['ttr']
  );
  echo "\n*sleeping for 2 seconds*\n\n";
  sleep(2);

  $jobStats = $client->statsJob($job['id']);
  echo "And now job will be released in {$jobStats['time-left']} seconds\n";

  $client->release($job['id'], delay: 5);
  $jobStats = $client->statsJob($job['id']);
  echo "Job is released and delayed for {$jobStats['delay']} seconds\n";
  echo "\n*sleeping for {$jobStats['delay']}+1 seconds*\n\n";
  sleep($jobStats['delay'] + 1);

  $jobStats = $client->statsJob($job['id']);
  echo "Current job state is {$jobStats['state']}\n";

  echo "Job is done, deleting it\n";
  $client->delete($job['id']);
} else {
  echo "So sad, i have nothing to do\n";
}

echo "\nAm I still connected? " . ($client->socket()->isConnected() ? 'Yes' : 'No') . "\n";
echo "\nHave i anything to do? ";
echo ($client->reserveWithTimeout(5) ? 'Yes' : 'No') . "\n";
