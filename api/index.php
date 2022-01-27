<?php
// +------------------------------------------------------------------------+
// | @author        : Michael Arawole (HENT Technologies)
// | @author_url    : https://logad.net
// | @author_email  : henttech@gmail.com
// | @date 			: 2021-05-06 21:12:45
// +------------------------------------------------------------------------+
// | Copyright (c) 2021 HENT Technologies. All rights reserved.
// +------------------------------------------------------------------------+

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../sources/slim/autoload.php';
require __DIR__ . '/../sources/psr-7/autoload.php';
require __DIR__ . '/../core/functions.php';

header('Access-Control-Allow-Origin: *');

// Instantiate App
$app = new app();
$api = AppFactory::create();

$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
if($scriptDir == "/") $scriptDir = "";
$api->setBasePath($scriptDir);
// $api->setBasePath((function () {
//     $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
//     $uri = (string) parse_url('http://a' . $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
//     if (stripos($uri, $_SERVER['SCRIPT_NAME']) === 0) {
//         return $_SERVER['SCRIPT_NAME'];
//     }
//     if ($scriptDir !== '/' && stripos($uri, $scriptDir) === 0) {
//         return $scriptDir."/";
//     }
//     return '';
// })());

// Add body parsing
$api->addBodyParsingMiddleware();
// Add error middleware
$api->addErrorMiddleware(true, true, true);

// Add routes
$api->get('/', function (Request $request, Response $response) {
    $response->getBody()->write("<h1>API Endpoint</h1>");
    return $response;
});

// User login
$api->post('/join-waitlist', function (Request $request, Response $response) {
	global $app;
	if (!haveEmptyParameter(array('fullName', 'email', 'type'), $request, $response)) {
	    $request_data = $request->getParsedBody();
	    $post_data = cleanPostData($request_data);

	    $result = $app->joinWaitList($post_data);
	    if ($result['status'] === true) {
            $response_data = array();
            $response_data['error'] = false;
            $response_data['message'] = $result['msg'];
            $response->getBody()->write(json_encode($response_data));
	    }
	    else {
	        $response_data = array();
	        $response_data['error'] = true;
	        $response_data['message'] = $result['msg'];
	        $response->getBody()->write(json_encode($response_data));
	    }
	}

    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(200);
});

function cleanPostData($post_data) {
	global $app;
	$post = new stdClass();
	foreach ($post_data as $key => $value) {
		$post->$key = $app->clean($value);
	}
	return $post;
}

function haveEmptyParameter($required_params, $request, $response){ 
    $error = false;
    $error_params = '';
    $request_params = $request->getParsedBody();
    foreach ($required_params as $param) {
        if (!isset($request_params[$param]) || strlen($request_params[$param]) <= 0) {
            $error = true;
            $error_params .= $param . ', ';
        }
    }
    if ($error) {
        $error_detail = array();
        $error_detail['error'] = true;
        $error_detail['message'] = 'Required Parameters ' . substr($error_params, 0, -2) . ' are missing or empty';
        $response->getBody()->write(json_encode($error_detail));
        return $response
            ->withHeader('Content-type', 'application/json')
            ->withStatus(200);
    }
    return $error;
}
$api->run();