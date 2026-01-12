<?php

use Illuminate\Http\Response;

if (! function_exists('getResponse')) {
    function getResponse($content,$code) {
        return response($content,$code);
    }
}
if (! function_exists('okResponse')) {
    function okResponse($content,$code=Response::HTTP_OK,$array='data') {
        return getResponse([$array=>$content,'status'=>'success'],$code);
    }
}
if (! function_exists('errorResponse')) {
    function errorResponse($content,$code=Response::HTTP_BAD_REQUEST,$array='data') {
        return getResponse([$array=>$content,'status'=>'failed'],$code);
    }
}
