<?php
/** RestEnApi functions */
function user_post($json){
    $returnJson["received"] = json_decode($json);
    print json_encode($returnJson);
}

function user_get($id){
    $returnJson["received"] = json_decode($id);
    print json_encode($returnJson);
}

function user_delete($json){
    $returnJson["received"] = json_decode($json);
    print json_encode($returnJson);
}

function user_put($json){
    $returnJson["received"] = json_decode($json);
    print json_encode($returnJson);
}