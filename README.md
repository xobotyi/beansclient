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

<h2 align="center">About</h2>

BeansClient is a pure 7.1+ dependency-free client for [beanstalkd work queue](https://github.com/kr/beanstalkd) with thorough unit-testing. Library uses PSR-4 autoloader standard and always has 100% tests coverage.    
Library gives you a simple way to provide your own Connection implementation, in cases when you need to log requests and responses or to proxy traffic to non-standard transport. 

BeansClient supports whole bunch of commands and responses specified in [protocol](https://github.com/kr/beanstalkd/blob/master/doc/protocol.txt) for version 1.10


<h2 align="center">Contents</h2>

1. [Requirements](#requirements)
2. [Installation](#installation)
3. [Usage](#usage)
4. [Docs](#docs)
    * [Jobs](#jobs)
    * [Tubes](#tubes)
    * [Stats](#stats)

<h2 align="center">Requirements</h2>

- [PHP](//php.net/) 7.1+

<h2 align="center">Installation</h2>

Install with composer
```bash
composer require xobotyi/beansclient
```

<h2 align="center">Usage</h2>

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

if ($job) {
    echo "Hey, i received first {$job['payload']} of job with id {$job['id']}\n";

    $beansClient->delete($job['id']);

    echo "And i've done it!\n";
}
else {
    echo "So sad, i have nothing to do";
}

echo "Am I still connected? \n" . ($beansClient->getConnection()->isActive() ? 'Yes' : 'No') . "\n";
```

<h2 align="center">Docs</h2>

### Jobs
-----
#### `put($payload[, int $priority[, int $delay[, int $ttr]]])`
_**Description:**_ inserts a job into the client's currently used tube (see the "useTube")  
_**Return value:**_  
_**Example:**_  
    
#### `reserve([?int $timeout])`
_**Description:**_ returns a newly-reserved job. Once a job is reserved for the client, the client has limited time to run (TTR) the job before the job times out. When the job times out, the server will put the job back into the ready queue. Both the TTR and the actual time left can be found in response to the statsJob command.
If more than one job is ready, beanstalkd will choose the one with the smallest priority value. Within each priority, it will choose the one that was received first.  
_**Return value:**_  
_**Example:**_  

#### `delete(int $jobId)`
_**Description:**_ removes a job from the server entirely. It is normally used by the client when the job has successfully run to completion. A client can delete jobs that it has reserved, ready jobs, delayed jobs, and jobs that are buried.  
_**Return value:**_  
_**Example:**_  

#### `release(int $jobId[, int $priority[, int $delay]])`
_**Description:**_ puts a reserved job back into the ready queue (and marks its state as "ready") to be run by any client. It is normally used when the job fails because of a transitory error.  
_**Return value:**_  
_**Example:**_  

#### `bury(int $jobId[, int $priority])`
_**Description:**_ puts a job into the "buried" state. Buried jobs are put into a FIFO linked list and will not be touched by the server again until a client kicks them with the kick or kickJob command.  
_**Return value:**_  
_**Example:**_  

#### `touch(int $jobId)`
_**Description:**_ allows a worker to request more time to work on a job. This is useful for jobs that potentially take a long time, but you still want the benefits of a TTR pulling a job away from an unresponsive worker. A worker may periodically tell the server that it's still alive and processing a job (e.g. it may do this on DEADLINE_SOON). The command postpones the auto release of a reserved job until TTR seconds from when the command is issued.  
_**Return value:**_  
_**Example:**_  

#### `kick(int $count)`
_**Description:**_ applies only to the currently used tube. It moves jobs into the ready queue. If there are any buried jobs, it will only kick buried jobs. Otherwise it will kick delayed jobs.  
_**Return value:**_  
_**Example:**_  

#### `kickJob(int $jobId)`
_**Description:**_  a variant of kick that operates with a single job identified by its job id. If the given job id exists and is in a buried or delayed state, it will be moved to the ready queue of the the same tube where it currently belongs.  
_**Return value:**_  
_**Example:**_  


### Tubes
-----
#### `useTube(string $tube)`
_**Description:**_ Subsequent put commands will put jobs into the tube specified by this command. If no use command has been issued, jobs will be put into the tube named "default"  
_**Return value:**_  
_**Example:**_  

#### `watchTube(string $tube)`
_**Description:**_ command adds the named tube to the watch list for the current connection. A reserve command will take a job from any of the tubes in the watch list. For each new connection, the watch list initially consists of one tube, named "default".  
_**Return value:**_  
_**Example:**_  

#### `ignoreTube(string $tube)`
_**Description:**_ removes the named tube from the watch list for the current connection.  
_**Return value:**_  
_**Example:**_  

#### `listTubeUsed()`
_**Description:**_ returns the tube currently being used by the client.  
_**Return value:**_  
_**Example:**_  

#### `listTubes()`
_**Description:**_ returns a list of all existing tubes.  
_**Return value:**_  
_**Example:**_  

#### `listTubesWatched()`
_**Description:**_ returns a list tubes currently being watched by the client.  
_**Return value:**_  
_**Example:**_  


### Stats
-----
#### `stats()`
_**Description:**_ gives statistical information about the system as a whole.  
_**Return value:**_  
_**Example:**_  

#### `statsTube(string $tube)`
_**Description:**_ gives statistical information about the specified tube if it exists.  
_**Return value:**_  
_**Example:**_  

#### `statsJob(int $jobId)`
_**Description:**_ gives statistical information about the specified job if it exists.  
_**Return value:**_  
_**Example:**_  
