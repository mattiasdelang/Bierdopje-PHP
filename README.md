# bierdopje-php [![Build Status](https://travis-ci.org/mattiasdelang/bierdopje-php.svg?branch=master)](https://travis-ci.org/mattiasdelang/bierdopje-php)

PHP wrapper for Bierdopje API


## Install
Simple:
`composer require mattiasdelang/bierdopje-php`

## How to use
### Environment variable
Make sure you have a `BD_APIKEY` environment variable set.

If your project:
 - uses a `.env` file, add it there.
 - uses Apache you can add `SetEnv BD_APIKEY apikeystring` to your vhost.
 - runs on Linux you can execute `export BD_APIKEY=apikeystring`
 - runs on Windows [add it to your environment](http://www.computerhope.com/issues/ch000549.htm).

### HTTP User-Agent
Bierdopje requires all automated tools to specify a custom User-Agent when performing HTTP requests.
So give your Http client a default User-Agent before passing it to the constructor:

```PHP
use mattiasdelang\Bierdopje as BierdopjeApi;

$httpClient = new \GuzzleHttp\Client([
  'headers' => [
    'User-Agent' => 'projectname/0.0.1'
  ]
]);

$bierdopje = new BierdopjeApi($httpClient);
```

