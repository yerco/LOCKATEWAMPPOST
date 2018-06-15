<?php
/**
 * WampPostClient/overrides/LockateWampPost.php
 * Overrides WampPost to include our logging
 *
 * @license Nubelum
 * @version 0.1
 * @author  Calamandes yerco@hotmail.com
 * @updated 2018-06-15
 * @link    http://www.nubelum.com
 */

require __DIR__ . '/../../vendor/voryx/wamppost/src/WampPost/WampPost.php';

use WampPost\WampPost;
use Psr\Http\Message\ServerRequestInterface;
use Thruway\Common\Utils;
use React\Http\Response;
use React\Promise\Deferred;
use Thruway\CallResult;
use Thruway\Message\ErrorMessage;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class LockateWampPost extends WampPost
{
    public function handleRequest(ServerRequestInterface $request)
    {
        if ($request->getMethod() === 'POST' && $request->getUri()->getPath() === '/pub') {
            return $this->handlePublishHttpPost($request);
        }

        if ($request->getMethod() === 'POST' && $request->getUri()->getPath() === '/call') {
            return $this->handleCallHttpRequest($request);
        }

        return new Response(404, [], 'Not found');
    }

    private function handlePublishHttpPost(ServerRequestInterface $request)
    {
        // create a log to register the POSTs (the purpose of this override!!!)
        try {
            $logger = new Logger('name');
            $logger->pushHandler(new StreamHandler('lockate_wamppost_posts.log',
                Logger::DEBUG));
        }
        catch (\Exception $e) {
            echo "Logger creation problem at LockateWampPost: " . $e->getMessage();
        }

        try {
            //{"topic": "com.myapp.topic1", "args": ["Hello, world"]}
            $json = json_decode($request->getBody());

            $logger->info('POST received', array(
                'Time'      => gmdate("Y-m-d\TH:i:s\Z", time()),
                'Content'   => $json
            ));

            if ($json === null) {
                throw new \Exception('JSON decoding failed: ' . json_last_error_msg());
            }

            if (isset($json->topic)
                && is_scalar($json->topic)
                && isset($json->args)
                && is_array($json->args)
                && ($this->getPublisher() !== null)
            ) {
                $json->topic = strtolower($json->topic);
                if (!Utils::uriIsValid($json->topic)) {
                    throw new \Exception('Invalid URI: ' . $json->topic);
                }

                $argsKw  = isset($json->argsKw) && is_object($json->argsKw) ? $json->argsKw : null;
                $options = isset($json->options) && is_object($json->options) ? $json->options : null;
                $this->getSession()->publish($json->topic, $json->args, $argsKw, $options);
            } else {
                throw new \Exception('Invalid request: ' . json_encode($json));
            }
        } catch (\Exception $e) {
            return new Response(400, [], 'Bad Request: ' . $e->getMessage());
        }

        return new Response(200, [], 'pub');
    }

    /* I'm including this for completeness, haven't checked its work yet */
    private function handleCallHttpRequest(ServerRequestInterface $request)
    {
        $deferred = new Deferred();
        try {
            //{"procedure": "com.myapp.procedure1", "args": ["Hello, world"], "argsKw": {}, "options": {} }
            $json = json_decode($request->getBody());

            if (isset($json->procedure)
                && Utils::uriIsValid($json->procedure)
                && ($this->getCaller() !== null)
            ) {
                $args    = isset($json->args) && is_array($json->args) ? $json->args : null;
                $argsKw  = isset($json->argsKw) && is_object($json->argsKw) ? $json->argsKw : null;
                $options = isset($json->options) && is_object($json->options) ? $json->options : null;

                $this->getSession()->call($json->procedure, $args, $argsKw, $options)->then(
                /** @param CallResult $result */
                    function (CallResult $result) use ($deferred) {
                        $responseObj          = new \stdClass();
                        $responseObj->result  = 'SUCCESS';
                        $responseObj->args    = $result->getArguments();
                        $responseObj->argsKw  = $result->getArgumentsKw();
                        $responseObj->details = $result->getDetails();

                        $deferred->resolve(new Response(200, ['Content-Type' => 'application/json'], json_encode($responseObj)));
                    },
                    function (ErrorMessage $msg) use ($deferred) {
                        $responseObj                = new \stdClass();
                        $responseObj->result        = 'ERROR';
                        $responseObj->error_uri     = $msg->getErrorURI();
                        $responseObj->error_args    = $msg->getArguments();
                        $responseObj->error_argskw  = $msg->getArgumentsKw();
                        $responseObj->error_details = $msg->getDetails();

                        // maybe return an error code here
                        $deferred->resolve(new Response(200, ['Content-Type' => 'application/json'], json_encode($responseObj)));
                    }
                );
            } else {
                // maybe return an error code here
                $deferred->resolve(new Response(200, [], 'No procedure set'));
            }
        } catch (\Exception $e) {
            // maybe return an error code here
            $deferred->resolve(new Response(200, [], 'Problem'));
        }

        return $deferred->promise();
    }
}