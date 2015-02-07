Auth Server API, PHP Auth Server client
================================================

[![Build Status](https://secure.travis-ci.org/guzzle/guzzle.png?branch=master)](http://travis-ci.org/guzzle/guzzle)

Auth Server API is a PHP HTTP client for Auth Server that makes it easy to send HTTP requests and
trivial to integrate with web services.

- Manages things like persistent connections,  simplifies sending streaming POST requests with fields ,and abstracts away the underlying HTTP transport layer.
- Set the cookie on your server by the token issued by the Auth Server
- Easy login and logout function
- Auth Server API makes it so that you no longer need to fool around with cURL options,
  stream contexts, or sockets for connecting with Auth Server.

### Usage

```php

$auth = new Auth\ApiClient(array(
	'KEY' =>  AUTH_KEY,  // YOUR AUTH KEY 
	'SECRET' =>  AUTH_SECRET,  // YOUR AUTH SECRET
	'LOGIN_URI' =>   '/login.php', // YOUR APP LOGIN URL
));
```

- For Testing use 

```php
define('AUTH_KEY','4783924789374897238947923');
define('AUTH_SECRET','$2y$10$qT8gRka/U4A.2hm5cXVtkuBcKLCV0Impo72DNOQl6Rz55Z2rYWwTa');
```
### For Check Login Status

- If the user is logged in, it returns the user information array.
- If it is not logged in, it redirects to the auth server login page

```php
if($user = $auth->isLoggedIn()){
	echo 'Welcome ' . $user['first_name'] . ' ' . $user['last_name'];
// do your code
}
```

### For Login

```php
$auth->doLogin();
```

### For Logout

```php
$auth->doLogout();
```

### Installing via Composer

The recommended way to install Auth Server API is through
[Composer](http://getcomposer.org).

```bash
# Install Composer
curl -sS https://getcomposer.org/installer | php
```

Next, run the Composer command to install the latest stable version of Auth Server API:

```bash
composer require spandansingh/auth-server-api
```

After installing, you need to require Composer's autoloader:

```php
require 'vendor/autoload.php';
```
Consultation
---------
- Spandan Singh
- Looking for PHP web development solutions or consultation? [Drop me a mail](mailto:developer.spandan@gmail.com).
