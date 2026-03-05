<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kiss_datafeed_data_mappings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('credential_id');
            $table->string('sku', 50);
            $table->string('entity_type', 50)->default('product');
            $table->boolean('external_exists')->default(false);
            $table->timestamp('last_exported_at')->nullable();
            $table->string('last_export_status', 20)->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();

            $table->foreign('credential_id')
                ->references('id')
                ->on('kiss_datafeed_credentials')
                ->onDelete('cascade');

            $table->unique(['credential_id', 'sku', 'entity_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kiss_datafeed_data_mappings');
    }
};
