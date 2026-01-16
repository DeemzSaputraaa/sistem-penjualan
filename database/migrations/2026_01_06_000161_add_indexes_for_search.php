<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table): void {
            $table->index('name');
        });

        Schema::table('spareparts', function (Blueprint $table): void {
            $table->index('name');
        });

        Schema::table('suppliers', function (Blueprint $table): void {
            $table->index('name');
        });

        Schema::table('customers', function (Blueprint $table): void {
            $table->index('name');
        });

        Schema::table('sales', function (Blueprint $table): void {
            $table->index('sold_at');
        });

        Schema::table('purchases', function (Blueprint $table): void {
            $table->index('purchased_at');
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table): void {
            $table->dropIndex(['name']);
        });

        Schema::table('spareparts', function (Blueprint $table): void {
            $table->dropIndex(['name']);
        });

        Schema::table('suppliers', function (Blueprint $table): void {
            $table->dropIndex(['name']);
        });

        Schema::table('customers', function (Blueprint $table): void {
            $table->dropIndex(['name']);
        });

        Schema::table('sales', function (Blueprint $table): void {
            $table->dropIndex(['sold_at']);
        });

        Schema::table('purchases', function (Blueprint $table): void {
            $table->dropIndex(['purchased_at']);
        });
    }
};
