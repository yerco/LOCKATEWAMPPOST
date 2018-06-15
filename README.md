# README

WampPost Client for lockate project.
Attention the WampPost class is overriden for logging POSTs purposes.
To use the original (in case composer update causes conflict for example)
Uncomment and delete the override.
```bash
//$wp = new WampPost\WampPost(
```

Reference: https://github.com/voryx/WampPost

## Ports

This client: 5051
Associated router: 8051

## Running Router-Client
```bash
$ php WampPostClient/WampPostClient.php
```

## Simple usage for POSTING
```bash
$ curl -v  -H "lockate.gateways", "args": ["Jaco"]}' http://<host>:5051/pub 
```
