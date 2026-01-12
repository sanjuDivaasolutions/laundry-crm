<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class FormRequestService
{
    public static function getManualRequestObject($data, $requestObject, $config = [])
    {
        $dummyRoute = $config['dummyRoute'] ?? '/dummy-route';
        $method = $config['method'] ?? 'POST';

        $request = Request::create($dummyRoute, $method, $data);
        $obj = $requestObject::createFrom($request, new $requestObject());

        // Check if the prepareForValidation method exists and is callable
        if (method_exists($obj, 'prepareForValidation')) {
            $reflectionMethod = new \ReflectionMethod($obj, 'prepareForValidation');
            if ($reflectionMethod->isPublic()) {
                $obj->prepareForValidation(); // Call the method
            }
        }

        // Validate manually
        $validator = Validator::make($obj->all(), $obj->rules());
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // Manually set validator
        $obj->setValidator($validator);

        return $obj;
    }
}
