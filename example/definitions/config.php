<?php
/**
 * Calling action api/describe
 * All functions receives a function as last parameter, to send response to client. 
 * $_sendResponse prints a json_encode($returnJson) and die();
 *
 * @param [type] $_sendResponse
 * @return void
 */
function describe($_sendResponse){
    $pathToConfig = realpath(dirname(__FILE__,2)).'/api.config.json';
    $configJson = json_decode(file_get_contents($pathToConfig));
    $_sendResponse($configJson);
}