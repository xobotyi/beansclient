<div align="center">

# beansclient

[![NPM Version](https://flat.badgen.net/packagist/v/xobotyi/beansclient)](https://packagist.org/packages/xobotyi/beansclient)
[![NPM Downloads](https://flat.badgen.net/packagist/dt/xobotyi/beansclient)](https://packagist.org/packages/xobotyi/beansclient)
[![NPM Dependents](https://flat.badgen.net/packagist/dependents/xobotyi/beansclient)](https://packagist.org/packages/xobotyi/beansclient)
[![Build](https://img.shields.io/github/workflow/status/xobotyi/beansclient/CI?style=flat-square)](https://github.com/xobotyi/beansclient/actions)
[![Coverage](https://flat.badgen.net/codecov/c/github/xobotyi/beansclient)](https://app.codecov.io/gh/xobotyi/beansclient)
[![NPM Dependents](https://flat.badgen.net/packagist/php/xobotyi/beansclient)](https://packagist.org/packages/xobotyi/beansclient)
[![NPM Dependents](https://flat.badgen.net/packagist/license/xobotyi/beansclient)](https://packagist.org/packages/xobotyi/beansclient)

</div>

## About

BeansClient is a PHP8 client for [beanstalkd work queue](https://github.com/kr/beanstalkd) with thorough unit-testing.
Library uses PSR-4 autoloader standard and always has 100% tests coverage.    
Library gives you a simple way to provide your own Socket implementation, in cases when you need to log requests and
responses or to proxy traffic to non-standard transport.

BeansClient supports whole bunch of commands and responses specified
in [protocol](https://github.com/kr/beanstalkd/blob/master/doc/protocol.txt) for version 1.12  
<br>

## Why BeansClient?

1. Well tested.
2. Supports UNIX sockets.
3. Actively maintained.
4. Predictable (does not throw exception in any situation, hello `pheanstalk`🤪).
5. PHP8 support.

## Contents

1. [Requirements](#requirements)
2. [Installation](#installation)
3. [Usage](#usage)
4. [Docs](#docs)
    * TBD

## Requirements

- [PHP](//php.net/) 8.0+
- [beanstalkd](//github.com/kr/beanstalkd/) 1.12+

## Installation

Install with composer

```bash
composer require xobotyi/beansclient
```

## Usage

```php
<?php
use xobotyi\beansclient\Beanstalkd;
use xobotyi\beansclient\Client;
use xobotyi\beansclient\Socket\SocketsSocket;

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

    $client->delete($job['id']);

    echo "And i've done it!\n";
}
else {
    echo "So sad, i have nothing to do";
}

echo "Am I still connected? \n" . ($client->socket()->isConnected() ? 'Yes' : 'No') . "\n";
```
