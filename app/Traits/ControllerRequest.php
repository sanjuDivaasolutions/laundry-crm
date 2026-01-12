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
 *  *  Last modified: 13/01/25, 9:19 pm
 *  *  Written by Chintan Bagdawala, 2025.
 *
 */

namespace App\Traits;

use Gate;
use Illuminate\Http\Response;

trait ControllerRequest
{
    private function getList()
    {
        $class = $this->className;
        $obj = $class::query();
        foreach ($this->scopes as $s) {
            $obj = $obj->$s();
        }

        return $obj->{$this->fetcher}();
    }

    private function updateChildOld($request, $modelObj, $field, $model, $relationField, $updateField, $syncFields = [])
    {
        $records = $request->input($field, []);
        if ($records && ! is_array($records)) {
            $records = json_decode($records, true);
        }

        $existing = $modelObj->$relationField()->get();
        if (! empty($existing)) {
            foreach ($existing as $s) {
                $found = false;
                foreach ($records as $r) {
                    if ($r['id'] == $s->id) {
                        $found = true;
                        break;
                    }
                }
                if (! $found) {
                    $s->delete();
                }
            }
        }

        if (! empty($records)) {
            foreach ($records as $s) {
                $newItem = $model::find($s['id']);
                if (empty($newItem)) {
                    $newItem = new $model;
                }
                $fillValues = $s;
                $newItem->fill($fillValues);
                $newItem->$updateField = $modelObj->id;
                $newItem->save();
                if ($syncFields) {
                    foreach ($syncFields as $syncField) {
                        if (! isset($s[$syncField['field']])) {
                            continue;
                        }
                        $newItem->{$syncField['relation']}()->sync($s[$syncField['field']]);
                    }
                }
            }
        }
    }

    private function updateChild($request, $modelObj, $field, $model, $relationField, $updateField, $syncFields = [], $subItems = [])
    {
        if (gettype($request) == 'array') {
            $request = new \Illuminate\Http\Request($request);
        }
        $records = $request->input($field, []);
        if ($records && ! is_array($records)) {
            $records = json_decode($records, true);
        }

        // $existing = $modelObj->$relationField()->get();
        $ignoreIds = [];
        /*if (!empty($existing)) {
            foreach ($existing as $s) {
                //$found = false;
                foreach ($records as $r) {
                    if ($r['id'] == $s->id) {
                        $ignoreIds[] = $r['id'];
                        //$found = true;
                        break;
                    }
                }
                if (!$found) {
                    if($subItems) {
                        foreach ($subItems as $subItem) {
                            $s->{$subItem['relation']}()->delete();
                        }
                    }
                    $s->delete();
                }
            }
        }*/

        if (! empty($records)) {
            foreach ($records as $s) {
                $newItem = $model::find($s['id']);
                if (empty($newItem)) {
                    $newItem = new $model;
                }
                $fillValues = $s;
                $newItem->fill($fillValues);
                $newItem->{$updateField} = $modelObj->id;
                $newItem->save();
                $ignoreIds[] = ! in_array($newItem->id, $ignoreIds) ? $newItem->id : null;
                if ($syncFields) {
                    foreach ($syncFields as $syncField) {
                        if (! isset($s[$syncField['field']])) {
                            continue;
                        }
                        $newItem->{$syncField['relation']}()->sync($s[$syncField['field']]);
                    }
                }

                // Update sub-items if available or delete if not available
                if ($subItems) {
                    foreach ($subItems as $subItem) {
                        if (! isset($s[$subItem['field']])) {
                            continue;
                        }
                        $this->updateChild($s, $newItem, $subItem['field'], $subItem['model'], $subItem['relation'], $subItem['update_field']);
                    }
                }
            }
            $modelObj->{$relationField}()->whereNotIn('id', $ignoreIds)->delete();
        } else {
            $modelObj->{$relationField}()->delete();
        }
    }

    public function bulkDestroy()
    {
        abort_if(Gate::denies($this->permissionKey.'_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $ids = request('ids');
        if (! $ids) {
            return false;
        }
        if (! is_array($ids)) {
            $ids = json_decode($ids);
        }
        $class = $this->serviceClassName ?: null;

        if (! $class) {
            return false;
        }

        if (! method_exists($class, 'removeBulk')) {
            return false;
        }

        $class::removeBulk($ids);

        $count = count($ids);

        return okResponse("$count item(s) were deleted successfully");
    }
}
