<?php
/*
 *
 *  *  Copyright (c) 2024 Divaa Solutions. All rights reserved.
 *  *
 *  *  This software is the confidential and proprietary information of Divaa Solutions
 *  *  ("Confidential Information"). You shall not disclose such Confidential Information and
 *  *  shall use it only in accordance with the terms of the license agreement you entered into
 *  *  with Divaa Solutions.
 *  *
 *  *  Unauthorized copying of this file, via any medium is strictly prohibited.
 *  *  Proprietary and confidential.
 *  *
 *  *  Last modified: 17/10/24, 6:48 pm
 *  *  Written by Chintan Bagdawala, 2024.
 *
 */

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ControllerService
{
    /*public static function updateChild($request, $modelObj, $field, $model, $relationField, $updateField, $syncFields = [], $subItems = []): void
    {
        if (gettype($request) == 'array') {
            $request = new Request($request);
        }
        $records = $request->input($field, []);
        if ($records && !is_array($records)) {
            $records = json_decode($records, true);
        }

        $ignoreIds = [];
        if (!empty($records)) {
            foreach ($records as $s) {
                $newItem = $s['id'] ? $model::find($s['id']) : null;
                if (empty($newItem)) {
                    $newItem = new $model();
                }
                $fillValues = $s;
                $newItem->fill($fillValues);
                $newItem->{$updateField} = $modelObj->id;
                $newItem->save();
                $ignoreIds[] = !in_array($newItem->id, $ignoreIds) ? $newItem->id : null;
                if ($syncFields) {
                    foreach ($syncFields as $syncField) {
                        if (!isset($s[$syncField['field']])) {
                            continue;
                        }
                        $newItem->{$syncField['relation']}()->sync($s[$syncField['field']]);
                    }
                }

                // Update sub-items if available
                if ($subItems) {
                    foreach ($subItems as $subItem) {
                        if (!isset($s[$subItem['field']])) {
                            continue;
                        }
                        self::updateChild($s, $newItem, $subItem['field'], $subItem['model'], $subItem['relation'], $subItem['update_field']);
                    }
                }
            }
        }
        $modelObj->{$relationField}()->whereNotIn('id', $ignoreIds)->delete();
    }*/

    /**
     * Update child records for a model
     *
     * @param Request|array $request The request object or array containing child data
     * @param object $modelObj The parent model object
     * @param string $field The field name in the request containing child records
     * @param string $model The child model class name
     * @param string $relationField The relation method name in parent model
     * @param string $updateField The field to update with parent ID
     * @param array $syncFields Fields to sync in child models
     * @param array $subItems Nested child items to update
     * @return void
     */
    public static function updateChild($request, $modelObj, string $field, string $model, string $relationField, string $updateField, array $syncFields = [], array $subItems = []): void
    {
        try {
            // Normalize request to Request object
            if (is_array($request)) {
                $request = new Request($request);
            }

            // Get records from request
            $records = $request->input($field, []);
            if ($records && !is_array($records)) {
                $records = json_decode($records, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \InvalidArgumentException("Invalid JSON in field {$field}");
                }
            }

            $ignoreIds = [];
            if (!empty($records)) {
                foreach ($records as $s) {
                    if (!is_array($s)) {
                        continue;
                    }

                    // Find or create model
                    $newItem = null;
                    if (isset($s['id']) && !empty($s['id'])) {
                        $newItem = $model::find($s['id']);
                    }

                    if (empty($newItem)) {
                        $newItem = new $model();
                    }

                    // Fill only necessary values and avoid mass assignment
                    $fillValues = $s;
                    $newItem->fill($fillValues);
                    $newItem->{$updateField} = $modelObj->id;
                    $newItem->save();

                    // Add to ignore IDs if not already present
                    if (!in_array($newItem->id, $ignoreIds)) {
                        $ignoreIds[] = $newItem->id;
                    }

                    // Process sync fields
                    if ($syncFields) {
                        foreach ($syncFields as $syncField) {
                            if (!isset($s[$syncField['field']]) ||
                                !isset($syncField['relation']) ||
                                !method_exists($newItem, $syncField['relation'])) {
                                continue;
                            }
                            $newItem->{$syncField['relation']}()->sync($s[$syncField['field']]);
                        }
                    }

                    // Update sub-items if available
                    if ($subItems) {
                        foreach ($subItems as $subItem) {
                            if (!isset($s[$subItem['field']]) ||
                                !isset($subItem['model']) ||
                                !isset($subItem['relation']) ||
                                !isset($subItem['update_field'])) {
                                continue;
                            }
                            self::updateChild(
                                $s,
                                $newItem,
                                $subItem['field'],
                                $subItem['model'],
                                $subItem['relation'],
                                $subItem['update_field'],
                                $syncFields,
                                $subItems
                            );
                        }
                    }
                }
            }

            // Delete items not in the ignore list
            if (!empty($ignoreIds)) {
                $modelObj->{$relationField}()->whereNotIn('id', $ignoreIds)->delete();
            } else {
                // If no valid records were processed, delete all related records
                $modelObj->{$relationField}()->delete();
            }
        } catch (\Exception $e) {
            // Log the exception
            Log::error("Error updating child records: " . $e->getMessage(), [
                'model'    => get_class($modelObj),
                'field'    => $field,
                'relation' => $relationField
            ]);

            // Re-throw as a more specific exception if needed
            throw $e;
        }
    }
}
