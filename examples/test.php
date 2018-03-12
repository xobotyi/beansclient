<?php
    /**
     * @Author : a.zinoviev
     * @Package: beansclient
     * @License: http://www.opensource.org/licenses/mit-license.php
     */

    include_once __DIR__ . '/../vendor/autoload.php';

    use xobotyi\beansclient;

    $client = new beansclient\BeansClient(new beansclient\Connection('127.0.0.1', 11300), new xobotyi\beansclient\Encoder\Json());


    var_dump($client->put(['a' => [1, 2, 3]]));
    var_dump($job = $client->reserve());

    if ($job) {
        var_dump($client->delete($job['id']));
    }