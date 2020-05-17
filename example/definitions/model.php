<?php
/**
 * Calling action demo/get/id
 * All functions receives a function as last parameter, to send response to client. 
 * $_sendResponse prints a json_encode($returnJson) and die();
 * 
 * @param [integer] $id
 * @param [function] $_sendResponse
 * @return void
 */
function get($id,$_sendResponse){
    $returnJson["received"] = json_decode($id);
    $_sendResponse($returnJson);
}
/**
 * Calling action demo/post
 * All functions receives a function as last parameter, to send response to client. 
 * $_sendResponse prints a json_encode($returnJson) and die();
 * 
 * @param [json] $json
 * @param [function] $_sendResponse
 * @return void
 */
function post($json,$_sendResponse){
    $returnJson["received"] = json_decode($json,$_sendResponse);
    $_sendResponse($returnJson);
}
/**
 * Calling action demo/put
 * All functions receives a function as last parameter, to send response to client. 
 * $_sendResponse prints a json_encode($returnJson) and die();
 * 
 * @param [json] $json
 * @param [function] $_sendResponse
 * @return void
 */
function put($json,$_sendResponse){
    $returnJson["received"] = json_decode($json,$_sendResponse);
    $_sendResponse($returnJson);
}
/**
 * Calling action demo/patch
 * All functions receives a function as last parameter, to send response to client. 
 * $_sendResponse prints a json_encode($returnJson) and die();
 * 
 * @param [json] $json
 * @param [function] $_sendResponse
 * @return void
 */
function patch($json,$_sendResponse){
    $returnJson["received"] = json_decode($json,$_sendResponse);
    $_sendResponse($returnJson);
}

/**
 * Calling action demo/delete/id
 * All functions receives a function as last parameter, to send response to client. 
 * $_sendResponse prints a json_encode($returnJson) and die();
 * 
 * @param [type] $id
 * @param [function] $_sendResponse
 * @return void
 */
function delete($id,$_sendResponse){
    $returnJson["received"] = json_decode($json,$_sendResponse);
    $_sendResponse($returnJson);
}

