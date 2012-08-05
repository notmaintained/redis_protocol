# redis_protocol

Simple PHP client Library for the [Redis Protocol](http://redis.io/topics/protocol).


## Requirements

* PHP 5.3+


## Getting Started

### Download via [Composer](http://getcomposer.org/)

Create a `composer.json` file if you don't already have one in your projects root directory and require redis_protocol:

```
{
	"require": {
		"sandeepshetty/redis_protocol": "dev-master"
	}
}
```

Install Composer:
```
$ curl -s http://getcomposer.org/installer | php
```

Run the install command:
```
$ php composer.phar install
```

This will download redis_protocol into the `vendor/sandeepshetty/redis_protocol` directory.

To learn more about Composer visit http://getcomposer.org/


### Description

callable __redis_protocol\client__( [string _$host = '127.0.0.1'_ [, int _$port = 6379_]] )

The returned function accepts a Redis command as a string.


### Usage

```php
<?php

	require 'vendor/sandeepshetty/redis_protocol/client.php';
	use sandeepshetty\redis_protocol;


	$redis = redis_protocol\client();

	var_dump($redis('SET foo "bar baz"')); // bool(true)
	var_dump($redis('GET foo'));           // string(7) "bar baz"

	$redis('QUIT');


?>
```