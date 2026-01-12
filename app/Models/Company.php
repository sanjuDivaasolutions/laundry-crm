<?php

/*
 *
 *  *  Copyright (c) 2025 Divaa Solutions. All rights reserved.
 *  *
 *  *  This software is the confidential and proprietary information of Divaa Solutions
 *  *  ("Confidential Information"). You shall not disclose such Confidential Information and
 *  *  shall use it only in accordance with the terms of the license agreement you entered into
 *  *  with Divaa Solutions.
 *  *
 *  *  Unauthorized copying of this file, via any medium is strictly prohibited.
 *  *  Proprietary and confidential.
 *  *
 *  *  Last modified: 22/01/25, 4:51 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Models;

use App\Support\HasAdvancedFilter;
use App\Traits\BelongsToTenant;
use App\Traits\Searchable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Company extends Model implements HasMedia
{
    use BelongsToTenant, HasAdvancedFilter, HasFactory, InteractsWithMedia, Searchable;

    protected $appends = [
        'image',
    ];

    protected $fillable = [
        'code',
        'name',
        'address_1',
        'address_2',
        'active',
        'user_id',
        'tenant_id',
    ];

    protected $orderable = [
        'id',
        'code',
        'name',
        'address_1',
        'address_2',
        'active',
    ];

    protected $filterable = [
        'id',
        'code',
        'name',
        'address_1',
        'address_2',
        'active',
        'user.name',
        'user.name',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $thumbnailWidth = 70;
        $thumbnailHeight = 70;

        $thumbnailPreviewWidth = 400;
        $thumbnailPreviewHeight = 400;

        $pdfPreviewWidth = 200;
        $pdfPreviewHeight = 200;

        $this->addMediaConversion('thumbnail')
            ->format('webp')
            ->width($thumbnailWidth)
            ->height($thumbnailHeight)
            ->fit('crop', $thumbnailWidth, $thumbnailHeight);
        $this->addMediaConversion('preview_thumbnail')
            ->format('webp')
            ->width($thumbnailPreviewWidth)
            ->height($thumbnailPreviewHeight)
            ->fit('crop', $thumbnailPreviewWidth, $thumbnailPreviewHeight);
        $this->addMediaConversion('pdf_preview')
            ->format('webp')
            ->width($pdfPreviewWidth)
            ->height($pdfPreviewHeight);
    }

    public function getImageAttribute()
    {
        $images = $this->getMedia('company_image');

        return $images->map(function ($media) {
            return [
                'id' => $media->id,
                'original_url' => $media->getFullUrl(),
                'preview_thumbnail' => $media->getFullUrl('preview_thumbnail'),
                'thumbnail' => $media->getFullUrl('thumbnail'),
                'mime_type' => $media->mime_type,
            ];
        });
    }

    public function getImageBase64Attribute()
    {
        $media = $this->getFirstMedia('company_image');
        if ($media === null) {
            return null;
        }
        $mediaPath = $media->getPath('pdf_preview');
        if (! file_exists($mediaPath)) {
            return null;
        }
        $fileContent = file_get_contents($mediaPath);
        if ($fileContent === false) {
            return null;
        }

        return 'data:'.$media->mime_type.';base64,'.base64_encode($fileContent);
    }
}
