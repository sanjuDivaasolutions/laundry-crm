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
 *  *  Last modified: 04/02/25, 8:22 am
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Traits;

trait CustomFormRequest
{
    private function isUpdateRequest(): bool
    {
        return in_array($this->requestType, ['PATCH', 'PUT']);
    }

    private function isCreateRequest(): bool
    {
        return $this->requestType === 'POST';
    }

    public function setUser($field = 'user_id', $force = false): void
    {
        $userId = adminAuth()->id();
        if (!$force && $this->{$field}) return;
        $this->set($field, $userId);
    }

    public function set($key, $value): void
    {
        $this->merge([
            $key => $value,
        ]);
    }

    public function unset($key): void
    {
        $this->offsetUnset($key);
    }

    public function setActive($field = 'active', $value = 1, $force = false): void
    {
        if (!$force && $this->{$field}) return;
        $this->set($field, $value);
    }

    public function setIfNull($key, $value): void
    {
        if (!is_null($this->{$key})) return;
        $this->merge([
            $key => $value,
        ]);
    }

    public function prepareObjects(): void
    {
        $this->setObjectIds($this->idObjects);
        $this->setObjectValues($this->valueObjects);
        $this->convertBulkToArray($this->stringArrays);
    }

    public function setObjectId($key, $defaultValue = null): void
    {
        $value = $this->{$key} && isset($this->{$key}['id']) ? $this->{$key}['id'] : $defaultValue;
        $this->set($key . '_id', $value);
    }

    public function setObjectIds($keys, $unset = true): void
    {
        if (!is_array($keys) || empty($keys)) return;
        foreach ($keys as $key) {
            $this->setObjectId($key);
            if ($unset) {
                $this->unset($key);
            }
        }
    }

    public function setObjectValue($key): void
    {
        if ($this->{$key} && isset($this->{$key}['value'])) {
            $this->set($key, $this->{$key}['value']);
        }
    }

    public function setObjectValues($keys): void
    {
        if (!is_array($keys) || empty($keys)) return;
        foreach ($keys as $key) {
            $this->setObjectValue($key);
        }
    }

    public function convertToArray($key): void
    {
        if (!$this->{$key}) return;
        if (is_array($this->{$key})) return;
        $this->set($key, stringToArray($this->{$key}));
    }

    public function convertBulkToArray($arrayFields): void
    {
        if (!is_array($arrayFields) || empty($arrayFields)) return;
        foreach ($arrayFields as $field) {
            $this->convertToArray($field);
        }
    }
}
