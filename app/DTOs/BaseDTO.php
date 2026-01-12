<?php

declare(strict_types=1);

namespace App\DTOs;

use Illuminate\Contracts\Support\Arrayable;
use ReflectionClass;
use ReflectionProperty;

abstract class BaseDTO implements Arrayable
{
    public function toArray(): array
    {
        $reflection = new ReflectionClass($this);
        $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC);

        $data = [];
        foreach ($properties as $property) {
            if ($property->isInitialized($this)) {
                $data[$property->getName()] = $property->getValue($this);
            }
        }

        return $data;
    }

    public static function fromRequest($request): static
    {
        // This should be implemented by child classes or use a mapper
        throw new \Exception('Method fromRequest not implemented');
    }
}
