<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->unsignedBigInteger('tenant_id')->nullable()->after('id');
            $table->unsignedBigInteger('created_by')->nullable()->after('remember_token');
            $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
            $table->unsignedBigInteger('deleted_by')->nullable()->after('updated_by');
            $table->unsignedInteger('version')->default(1)->after('deleted_by');
            $table->softDeletes()->after('version');

            $table->index('tenant_id', 'users_tenant_id_index');

            $table->foreign('created_by', 'users_created_by_foreign')
                ->references('id')->on('users')
                ->restrictOnDelete();
            $table->foreign('updated_by', 'users_updated_by_foreign')
                ->references('id')->on('users')
                ->restrictOnDelete();
            $table->foreign('deleted_by', 'users_deleted_by_foreign')
                ->references('id')->on('users')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropForeign('users_created_by_foreign');
            $table->dropForeign('users_updated_by_foreign');
            $table->dropForeign('users_deleted_by_foreign');
            $table->dropIndex('users_tenant_id_index');
            $table->dropSoftDeletes();
            $table->dropColumn(['tenant_id', 'created_by', 'updated_by', 'deleted_by', 'version']);
        });
    }
};
