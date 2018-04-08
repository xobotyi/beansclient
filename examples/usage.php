<?php
/**
 * @Author : a.zinovyev
 * @Package: beansclient
 * @License: http://www.opensource.org/licenses/mit-license.php
 */

include_once __DIR__ . '/../vendor/autoload.php';

use xobotyi\beansclient\BeansClient;
use xobotyi\beansclient\Connection;

$connection  = new Connection('127.0.0.1', 11300, 2, false);
$beansClient = new BeansClient($connection);

##            ##
#   PRODUCER   #
##            ##

$job = $beansClient->useTube('myAwesomeTube')
                   ->put("job's payload");

##            ##
#    WORKER    #
##            ##

$job = $beansClient->watchTube('myAwesomeTube')
                   ->watchTube('myAwesomeTube2')
                   ->reserve();

$job = new \xobotyi\beansclient\Job($beansClient, $job['id']);
if ($job) {
    echo "Hey, i received first {$job->payload} of job with id {$job->id}\n<br/>";

    echo "It will be released automatically at " . date('Y/m/d H:i:s', $job->releaseTime) . " (in {$job->timeLeft} seconds)\n<br/>";
    echo "\n<br/>*sleeping for 2 seconds*\n<br/>\n<br/>";
    sleep(2);
    echo "And now job will be released in {$job->timeLeft} seconds\n<br/>";

    $job->release(BeansClient::DEFAULT_PRIORITY, 5);
    echo "Job is released and delayed for {$job->delay} seconds\n<br/>";
    echo "\n<br/>*sleeping for 2 seconds*\n<br/>\n<br/>";
    sleep(2);
    echo "{$job->timeLeft} seconds left till release\n<br/>*sleeping for {$job->timeLeft} seconds\n<br/>\n<br/>";
    sleep($job->timeLeft+1);
    echo "Current job state is {$job->state}\n<br/>";

    $beansClient->delete($job->id);

    //echo "And i've done it!\n<br/>";
}
else {
    echo "So sad, i have nothing to do\n<br/>";
}

echo "\n<br/>Am I still connected? " . ($beansClient->getConnection()->isActive() ? 'Yes' : 'No') . "\n<br/>";