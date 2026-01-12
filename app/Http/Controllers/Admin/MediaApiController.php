<?php

namespace App\Http\Controllers\Admin;

use App\Services\MediaService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MediaApiController
{
    public static function upload(Request $request)
    {
        $size = $request->input('size', 0) * 1024;
        $validated = $request->validate([
            'file' => "required|file|max:$size",
            'model_id' => 'nullable|integer',
            'collection_name' => 'required|string',
        ]);

        $collection = $validated['collection_name'];
        $class = config("media.collection.$collection");
        if(!$class) {
            return errorResponse('Invalid collection name', Response::HTTP_NOT_FOUND);
        }

        $media = MediaService::store($validated, $class);

        return okResponse([$media], Response::HTTP_CREATED);
    }
}
