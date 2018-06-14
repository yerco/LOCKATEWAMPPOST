<?php
/**
 * WampPostClient/WampPostClient.php
 * WampPost Client Internal
 * @link https://github.com/voryx/WampPost
 *
 * @license Nubelum
 * @version 0.1
 * @author  Calamandes yerco@hotmail.com
 * @updated 2018-06-14
 * @link    http://www.nubelum.com
 */

require __DIR__ . '/../vendor/autoload.php';

use Thruway\Peer\Router;
use Thruway\Transport\RatchetTransportProvider;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

define("WAMPPOST_CLIENT_IP", "192.168.178.157");
define("WAMPPOST_CLIENT_PORT", "5051");
define("ROUTER_IP", "192.168.178.157");
define("ROUTER_PORT", "8051");

// create a log channel
try {
    $logger = new Logger('name');
    $logger->pushHandler(new StreamHandler('wamppost_client_at_router.log',
        Logger::DEBUG));
}
catch (\Exception $e) {
    echo "Logger creation problem at WampPostInternalClient: " . $e->getMessage();
}

$router = new Router();

//////// WampPost part
// The WampPost client
// create an HTTP server on port 5051 - notice that we have to
// send in the same loop that the router is running on
$wp = new WampPost\WampPost(
    'nubelum.lockate',
    $router->getLoop(),
    'tcp://' . WAMPPOST_CLIENT_IP . ':' . WAMPPOST_CLIENT_PORT
);

// add a transport to connect to the WAMP router
$router->addTransportProvider(new Thruway\Transport\InternalClientTransportProvider($wp));
//////////////////////

// The websocket transport provider for the router
$transportProvider = new RatchetTransportProvider(ROUTER_IP, ROUTER_PORT);
$router->addTransportProvider($transportProvider);
try {
    $router->start();
}
catch (\Exception $e) {
    $logger->error(
        "WampPostInternalClient Exception ",
        array(
            'Time' => gmdate("Y-m-d\TH:i:s\Z", time()),
            'Exception Message' => $e->getMessage()
        )
    );
}
