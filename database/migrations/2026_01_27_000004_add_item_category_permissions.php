<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $groupId = DB::table('permission_groups')->where('name', 'General')->value('id') ?? 1;
        $now = now();

        $permissions = [
            // Item permissions
            ['title' => 'item_create', 'permission_group_id' => $groupId, 'created_at' => $now, 'updated_at' => $now],
            ['title' => 'item_edit', 'permission_group_id' => $groupId, 'created_at' => $now, 'updated_at' => $now],
            ['title' => 'item_show', 'permission_group_id' => $groupId, 'created_at' => $now, 'updated_at' => $now],
            ['title' => 'item_delete', 'permission_group_id' => $groupId, 'created_at' => $now, 'updated_at' => $now],
            ['title' => 'item_access', 'permission_group_id' => $groupId, 'created_at' => $now, 'updated_at' => $now],
            // Category permissions
            ['title' => 'category_create', 'permission_group_id' => $groupId, 'created_at' => $now, 'updated_at' => $now],
            ['title' => 'category_edit', 'permission_group_id' => $groupId, 'created_at' => $now, 'updated_at' => $now],
            ['title' => 'category_show', 'permission_group_id' => $groupId, 'created_at' => $now, 'updated_at' => $now],
            ['title' => 'category_delete', 'permission_group_id' => $groupId, 'created_at' => $now, 'updated_at' => $now],
            ['title' => 'category_access', 'permission_group_id' => $groupId, 'created_at' => $now, 'updated_at' => $now],
        ];

        foreach ($permissions as $permission) {
            DB::table('permissions')->insertOrIgnore($permission);
        }
    }

    public function down(): void
    {
        DB::table('permissions')->whereIn('title', [
            'item_create', 'item_edit', 'item_show', 'item_delete', 'item_access',
            'category_create', 'category_edit', 'category_show', 'category_delete', 'category_access',
        ])->delete();
    }
};
