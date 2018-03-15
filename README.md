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

<h1 align="center">BeansClient</h1>
here should be some description

<h2 align="center">Contents</h2>
<div align="center">

1. [Requirements](#requirements)
2. [Installation](#installation)
3. [Usage](#usage)
4. [Docs](#docs)

</div>

<h2 align="center">Requirements</h2>


- [PHP](//php.net/) 7.1+

<h2 align="center">Installation</h2>

Install with composer
```bash
composer require xobotyi/beansclient
```

<h2 align="center">Usage</h2>
```php
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
