<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('permissions', function (Blueprint $table): void {
            $table->string('description')->nullable()->after('guard_name');
            $table->string('group')->nullable()->after('description');

            $table->index('group', 'permissions_group_index');
        });

        Schema::table('roles', function (Blueprint $table): void {
            $table->string('description')->nullable()->after('guard_name');
            $table->boolean('is_system')->default(false)->after('description');
            $table->unsignedBigInteger('created_by')->nullable()->after('updated_at');
            $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
            $table->unsignedBigInteger('deleted_by')->nullable()->after('updated_by');
            $table->softDeletes()->after('deleted_by');

            $table->index('is_system', 'roles_is_system_index');

            $table->foreign('created_by', 'roles_created_by_foreign')
                ->references('id')->on('users')
                ->nullOnDelete();
            $table->foreign('updated_by', 'roles_updated_by_foreign')
                ->references('id')->on('users')
                ->nullOnDelete();
            $table->foreign('deleted_by', 'roles_deleted_by_foreign')
                ->references('id')->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table): void {
            $table->dropForeign('roles_created_by_foreign');
            $table->dropForeign('roles_updated_by_foreign');
            $table->dropForeign('roles_deleted_by_foreign');
            $table->dropIndex('roles_is_system_index');
            $table->dropSoftDeletes();
            $table->dropColumn(['description', 'is_system', 'created_by', 'updated_by', 'deleted_by']);
        });

        Schema::table('permissions', function (Blueprint $table): void {
            $table->dropIndex('permissions_group_index');
            $table->dropColumn(['description', 'group']);
        });
    }
};
