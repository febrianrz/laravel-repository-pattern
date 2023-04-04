<?php
use Illuminate\Support\Facades\Route;

Route::prefix('/api')->group(function(){
    Route::get('/ping',function(){
        return response()->json(['message'=>'OK']);
    });
    Route::get('/time',function(){
        return response()->json([
            'timestamp'=>time(),
            'datetime'=>date('Y-m-d H:i:s')
        ]);
    });
});
