<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('sparepart_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('ref_type')->nullable();
            $table->unsignedBigInteger('ref_id')->nullable();
            $table->string('type');
            $table->integer('qty');
            $table->integer('before_stock');
            $table->integer('after_stock');
            $table->decimal('unit_price', 12, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['ref_type', 'ref_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_logs');
    }
};
