<h1 align="center">BeansClient</h1>
<p align="center">
    <a href="https://packagist.org/packages/xobotyi/beansclient">
        <img alt="License" src="https://poser.pugx.org/xobotyi/beansclient/license" />
    </a>
    <a href="https://packagist.org/packages/xobotyi/beansclient">
        <img alt="PHP 7 ready" src="http://php7ready.timesplinter.ch/xobotyi/beansclient/badge.svg" />
    </a>
    <a href="https://travis-ci.org/xobotyi/beansclient">
        <img alt="Build Status" src="https://travis-ci.org/xobotyi/beansclient.svg?branch=master" />
    </a>
    <a href="https://www.codacy.com/app/xobotyi/beansclient">
        <img alt="Codacy Grade" src="https://api.codacy.com/project/badge/Grade/0b787b1f74ce43828162298bef1d7868" />
    </a>
    <a href="https://scrutinizer-ci.com/g/xobotyi/beansclient/">
        <img alt="Scrutinizer Code Quality" src="https://scrutinizer-ci.com/g/xobotyi/beansclient/badges/quality-score.png?b=master" />
    </a>
    <a href="https://www.codacy.com/app/xobotyi/beansclient">
        <img alt="Codacy Coverage" src="https://api.codacy.com/project/badge/Coverage/0b787b1f74ce43828162298bef1d7868" />
    </a>
    <a href="https://packagist.org/packages/xobotyi/beansclient">
        <img alt="Latest Stable Version" src="https://poser.pugx.org/xobotyi/beansclient/v/stable" />
    </a>
    <a href="https://packagist.org/packages/xobotyi/beansclient">
        <img alt="Total Downloads" src="https://poser.pugx.org/xobotyi/beansclient/downloads" />
    </a>
</p>

## About
BeansClient is a pure 7.1+ dependency-free client for [beanstalkd work queue](https://github.com/kr/beanstalkd) with thorough unit-testing. Library uses PSR-4 autoloader standard and always has 100% tests coverage.    
Library gives you a simple way to provide your own Connection implementation, in cases when you need to log requests and responses or to proxy traffic to non-standard transport. 

BeansClient supports whole bunch of commands and responses specified in [protocol](https://github.com/kr/beanstalkd/blob/master/doc/protocol.txt) for version 1.10  
<br>

## Contents
1. [Requirements](#requirements)
2. [Installation](#installation)
3. [Usage](#usage)
4. [Docs](#docs)
    * [Classes](#classes)
    * [Jobs commands](#jobs-commands)
    * [Tubes commands](#tubes-commands)
    * [Stats commands](#stats-commands)
<br>

## Requirements
- [PHP](//php.net/) 7.1+
- [beanstalkd](//github.com/kr/beanstalkd/) 1.10+
<br>

## Installation
Install with composer
```bash
composer require xobotyi/beansclient
```
<br>

## Usage
```php
<?php
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

if ($job->id) {
    echo "Hey, i received first {$job->payload} of job with id {$job->id}\n";

    $job->delete();

    echo "And i've done it!\n";
}
else {
    echo "So sad, i have nothing to do";
}

echo "Am I still connected? \n" . ($beansClient->getConnection()->isActive() ? 'Yes' : 'No') . "\n";
```
<br>

## Docs
### Classes
---
#### `beansclient\Connection`
Connection class responsible for transport between client and beanstalkd server.

_**Parameters:**_  
* host`string` _[optional, default: 127.0.0.1]_ - can be a host, or the path to a unix domain socket
* port`int` _[optional, default: 11300]_ - port to connect, -1 for sockets
* connectionTimeout`int` _[optional, default: 2]_ - connection timeout in seconds, 0 means unlimited
* persistent`bool` _[optional, default: false]_ - whether to use persistent connection or not. If `true` - connection will not be closed with destruction of Connection instance

_**Throws:**_  
`xobotyi\beansclient\Exception\Connection` - on inability to open connection

_**Example:**_  
```php
use xobotyi\beansclient\Connection;

$socket = new Connection(); // defaults
$socket = new Connection('/tmp/beanstalkd.sock', -1); // unix domain socket.
```
<br>

#### `beansclient\BeansClient`
The main class of library. Puts everything together and makes the magic!
_**Parameters:**_  
* connection`xobotyi\beansclient\Exception\Connection` - Connection instance 
* serializer`xobotyi\beansclient\Serializer\Json` _[optional, default: null]_ - Serializer instance

_**Throws:**_  
`xobotyi\beansclient\Exception\Client` - if constructor got inactive connection

_**Example:**_  
```php
use xobotyi\beansclient\BeansClient;
use xobotyi\beansclient\Connection;

$client = new BeansClient(new Connection());
$client->getConnection()->isActive();   // true
$client->getConnection()->getHost();    // 127.0.0.1
```
<br>

#### `beansclient\Job`
This class provides handy way to manage a single job. Even if it havent been reserved by worker.  
Due to the fact that we cant get all the data in one request _(job's payload available through the `peek` command and all the other data through the `stats-job` command)_ and the desire to minimize the number of requests - needed data will bw requested only in case of it's usage, 4ex:
```php
$job = new Job($beansclientInstance, 13); // creating Job instance

$job->payload;  // here will be performed a single 'peek' request to the beanstalkd server
                // and vice-versa
$job->tube;     // will perform 'stats-job' request
```
There is a cool and useful thing about Job: if it has _delayed_ or _reserved_ state it's instance will have a non-zero property `releaseTime` and always-actual property `timeLeft`. When the time has come - will be performed extra `stats-job` request to synchronize it's data.  
_**But be careful!** Due to calculation of that value, it can have a deviation if range of 1 second_
```php
$job = new Job($beansclientInstance, 13, 'reserved');

$job->timeLeft; // for example it has 13 seconds to release
sleep(3);
$job->timeLeft; // 10
$job->sate;     // reserved
sleep(11);
$job->timeLeft; // 0
$job->sate;     // ready
```
<br>

#### `beansclient\Serializer`
Beanstalkd job's payload can be only a string, so if we want to use non-string payload we have to serialize it.
`Serializer` is an interface that requires only 2 methods: `serialize(mixed $data):string` and `unserialize(string $str):mixed`  

Beansclient provides JSON serializer out of the box, but you can use any serializer you want, just implement the `Serializer` interface.
```php
use xobotyi\beansclient\BeansClient;
use xobotyi\beansclient\Serializer\Json;

$client = new BeansClient(new Connection(), new Json());
$client->getSerializer(); //  instance of \xobotyi\beansclient\Serializer\Json

#or

$client = new BeansClient(new Connection());
$beansClient->setSerializer(new Json())
            ->getSerializer(); //  instance of \xobotyi\beansclient\Serializer\Json
```
If you will not provide serializer with second parameter of `BeansClient` constructor, payload in `put` command mist be string or stringable value.  
<br>

### Jobs commands
-----
#### `put($payload[, int $priority[, int $delay[, int $ttr]]])`
Inserts a job into the client's currently used tube (see the "useTube")  

_**Return value:**_  
`\xobotyi\beansclient\Job` instance  
 
_**Example:**_  
```php
$client->put('myAwesomePayload', 2048, 0, 60)->payload; // myAwesomePayload
# or, if we use payload encoder 
$client->put(['it'=>'can be any', 'thing'], 2048, 0, 60)->id; //2
```
    
#### `reserve([?int $timeout])`
Returns a newly-reserved job. Once a job is reserved for the client, the client has limited time to run (TTR) the job before the job times out. When the job times out, the server will put the job back into the ready queue. Both the TTR and the actual time left can be found in response to the statsJob command.
If more than one job is ready, beanstalkd will choose the one with the smallest priority value. Within each priority, it will choose the one that was received first.  

_**Return value:**_  
`\xobotyi\beansclient\Job` instance  
`NULL` if there is no ready jobs in queue  

_**Example:**_  
```php
$client->reserve()->id; // 1
$client->reserve()->id; // 2
$client->reserve()->id; // null
```

#### `delete(int $jobId)`
Removes a job from the server entirely. It is normally used by the client when the job has successfully run to completion. A client can delete jobs that it has reserved, ready jobs, delayed jobs, and jobs that are buried.  

_**Return value:**_  
`BOOLEAN`  
* `true` if job exists and been deleted;
* `false` if job not exists.

_**Example:**_  
```php
$client->delete(1); // true
$client->delete(3); // false
```

#### `release(int $jobId[, int $priority[, int $delay]])`
Puts a reserved job back into the ready queue (and marks its state as "ready") to be run by any client. It is normally used when the job fails because of a transitory error.  

_**Return value:**_  
`STRING`  
* 'RELEASED' if job been released;
* 'BURIED' if the server ran out of memory trying to grow the priority queue data structure.

`NULL` if job not exists

_**Example:**_  
```php
$client->delete(2, 2048, 0); // 'RELEASED'
$client->delete(3); // null
```

#### `bury(int $jobId[, int $priority])`
Puts a job into the "buried" state. Buried jobs are put into a FIFO linked list and will not be touched by the server again until a client kicks them with the kick or kickJob command.  

_**Return value:**_  
`BOOLEAN`  
* `true` if job exists and been buried;
* `false` if job not exists.

_**Example:**_  
```php
$client->bury(2, 2048, 0); // true
$client->bury(3); // false
```

#### `touch(int $jobId)`
Allows a worker to request more time to work on a job. This is useful for jobs that potentially take a long time, but you still want the benefits of a TTR pulling a job away from an unresponsive worker. A worker may periodically tell the server that it's still alive and processing a job (e.g. it may do this on DEADLINE_SOON). The command postpones the auto release of a reserved job until TTR seconds from when the command is issued.  

_**Return value:**_  
`BOOLEAN`  
* `true` if job exists and been touched;
* `false` if job not exists.

_**Example:**_  
```php
$client->touch(2); // true
$client->touch(3); // false
```

#### `kick(int $count)`
Applies only to the currently used tube. It moves jobs into the ready queue. If there are any buried jobs, it will only kick buried jobs. Otherwise it will kick delayed jobs.  

_**Return value:**_  
`INTEGER` number of jobs actually kicked.  

_**Example:**_  
```php
$client->kick(3); // 1
$client->kick(3); // 0
```

#### `kickJob(int $jobId)`
A variant of kick that operates with a single job identified by its job id. If the given job id exists and is in a buried or delayed state, it will be moved to the ready queue of the the same tube where it currently belongs.  

_**Return value:**_  
`BOOLEAN`  
* `true` if job exists and been kicked;
* `false` if job not exists.

_**Example:**_  
```php
$client->kickJob(2); // true
$client->kickJob(3); // false
```
<br>

### Tubes commands
-----
#### `listTubeUsed()`
Returns the tube currently being used by the client.  

_**Return value:**_  
`STRING`  

_**Example:**_  
```php
$client->listTubeUsed(); // 'default'
```

#### `useTube(string $tube)`
Subsequent put commands will put jobs into the tube specified by this command. If no use command has been issued, jobs will be put into the tube named "default"  

_**Return value:**_  
`xobotyi\beansclient\BeansClient` instance  

_**Example:**_  
```php
$client->useTube('awesomeTube')
       ->listTubeUsed(); // 'awesomeTube'
```

#### `listTubes()`
Returns a list of all existing tubes.  

_**Return value:**_  
`ARRAY`  

_**Example:**_  
```php
$client->listTubes(); // ['default', 'awesomeTube']
```

#### `listTubesWatched()`
Returns a list tubes currently being watched by the client.  

_**Return value:**_  
`ARRAY`  

_**Example:**_  
```php
$client->listTubesWatched(); // ['default']
```

#### `watchTube(string $tube)`
Command adds the named tube to the watch list for the current connection. A reserve command will take a job from any of the tubes in the watch list. For each new connection, the watch list initially consists of one tube, named "default".  

_**Return value:**_  
`xobotyi\beansclient\BeansClient` instance  

_**Example:**_  
```php
$client->listTubesWatched(); // ['default']

$client->watchTube('awesomeTube')
       ->listTubesWatched(); // ['default', 'awesomeTube']
```

#### `ignoreTube(string $tube)`
Removes the named tube from the watch list for the current connection.  

_**Return value:**_  
`xobotyi\beansclient\BeansClient` instance  

_**Example:**_  
```php
$client->listTubesWatched(); // ['default']

$client->watchTube('awesomeTube')
       ->listTubesWatched(); // ['default', 'awesomeTube']
       
$client->ignoreTube('awesomeTube')
       ->ignoreTube('myAwesomeTube2')
       ->listTubesWatched(); // ['default']
```
<br>

### Stats commands
-----
#### `stats()`
Gives statistical information about the system as a whole.  

_**Return value:**_  
`ARRAY`  

_**Example:**_  
```php
$client->stats();
/*[
    'current-jobs-urgent' => '0',
    'current-jobs-ready' => '0',
    'current-jobs-reserved' => '0',
    'current-jobs-delayed' => '0',
    'current-jobs-buried' => '0',
    'cmd-put' => '12',
    'cmd-peek' => '0',
    'cmd-peek-ready' => '0',
    'cmd-peek-delayed' => '0',
    'cmd-peek-buried' => '0',
    'cmd-reserve' => '0',
    'cmd-reserve-with-timeout' => '12',
    'cmd-delete' => '12',
    'cmd-release' => '0',
    'cmd-use' => '12',
    'cmd-watch' => '14',
    'cmd-ignore' => '0',
    'cmd-bury' => '0',
    'cmd-kick' => '0',
    'cmd-touch' => '0',
    'cmd-stats' => '1',
    'cmd-stats-job' => '0',
    'cmd-stats-tube' => '0',
    'cmd-list-tubes' => '6',
    'cmd-list-tube-used' => '0',
    'cmd-list-tubes-watched' => '15',
    'cmd-pause-tube' => '0',
    'job-timeouts' => '0',
    'total-jobs' => '12',
    'max-job-size' => '65535',
    'current-tubes' => '3',
    'current-connections' => '2',
    'current-producers' => '2',
    'current-workers' => '2',
    'current-waiting' => '0',
    'total-connections' => '6',
    'pid' => '1',
    'version' => '1.10' (length=4),
    'rusage-utime' => '0.040000',
    'rusage-stime' => '0.000000',
    'uptime' => '41384',
    'binlog-oldest-index' => '0',
    'binlog-current-index' => '0',
    'binlog-records-migrated' => '0',
    'binlog-records-written' => '0',
    'binlog-max-size' => '10485760',
    'id' => 'f7546f4280926fcd',
    'hostname' => '2feafb46e549'
]*/
```

#### `statsTube(string $tube)`
Gives statistical information about the specified tube if it exists.  

_**Return value:**_  
`ARRAY`  
`NULL` if tube not exists.  

_**Example:**_  
```php
$client->statsTube('myAwesomeTube');
/*[
    'name' => 'myAwesomeTube',
    'current-jobs-urgent' => '0',
    'current-jobs-ready' => '0',
    'current-jobs-reserved' => '0',
    'current-jobs-delayed' => '0',
    'current-jobs-buried' => '0',
    'total-jobs' => '0',
    'current-using' => '0',
    'current-watching' => '1',
    'current-waiting' => '0',
    'cmd-delete' => '0',
    'cmd-pause-tube' => '0',
    'pause' => '0',
    'pause-time-left' => '0'
]*/
```

#### `statsJob(int $jobId)`
Gives statistical information about the specified job if it exists.  

_**Return value:**_  
`ARRAY`  
`NULL` if job not exists.  

_**Example:**_  
```php
$client->statsJob(2);
/*[
    'id' => '2',
    'tube' => 'myAwesomeTube',
    'state' => 'reserved',
    'pri' => '2048',
    'age' => '0',
    'delay' => '0',
    'ttr' => '30',
    'time-left' => '29',
    'file' => '0',
    'reserves' => '1',
    'timeouts' => '0',
    'releases' => '0',
    'buries' => '0',
    'kicks' => '0'
]*/
```
