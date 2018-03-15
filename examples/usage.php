<?php
    /**
     * @Author : a.zinovyev
     * @Package: beansclient
     * @License: http://www.opensource.org/licenses/mit-license.php
     */

    include_once __DIR__ . '/../vendor/autoload.php';

    use xobotyi\beansclient\BeansClient;
    use xobotyi\beansclient\Connection;

    $connection  = new Connection('127.0.0.1', 11300, 2, true);
    $beansClient = new BeansClient($connection);

    ##            ##
    #   PRODUCER   #
    ##            ##

    $beansClient->useTube('myAwesomeTube')
                ->put("job's payload");

    ##            ##
    #    WORKER    #
    ##            ##

    $job = $beansClient->watchTube('myAwesomeTube')
                       ->reserve();

    if ($job) {
        echo "Hey, i received first {$job['payload']} of job with id {$job['id']}\n";

        $beansClient->delete($job['id']);

        echo "And i've done it!\n";
    }
    else {
        echo "So sad, i have nothing to do";
    }

    echo "Am I still connected? \n" . ($beansClient->getConnection()->isActive() ? 'Yes' : 'No') . "\n";