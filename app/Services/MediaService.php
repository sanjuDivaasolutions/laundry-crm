<?php

namespace App\Services;

use Carbon\Carbon;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaService
{
    public static function store($validated, $model)
    {
        $obj            = new $model();
        $obj->id        = $validated['model_id'];
        $obj->exists    = true;

        return $obj->addMediaFromRequest('file')->toMediaCollection($validated['collection_name']);
    }

    public static function updateRelations($obj,$media,$collection)
    {
        if($media) {
            Media::whereIn('id', data_get($media, '*.id'))
                ->where('model_id', 0)
                ->update(['model_id' => $obj->id]);
        }
        $obj->updateMedia($media, $collection);
    }

    public static function removeOlderMedia(Carbon $olderThan = null)
    {
        if(!$olderThan) {
            $olderThan = now()->subDay();
        }
        Media::query()
            ->where('model_id', 0)
            ->where('created_at', '<', $olderThan)
            ->delete();
    }
}
