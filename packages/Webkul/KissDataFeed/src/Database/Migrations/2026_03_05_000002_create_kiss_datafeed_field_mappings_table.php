<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kiss_datafeed_field_mappings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('credential_id');
            $table->json('mapping');
            $table->json('defaults')->nullable();
            $table->timestamps();

            $table->foreign('credential_id')
                ->references('id')
                ->on('kiss_datafeed_credentials')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kiss_datafeed_field_mappings');
    }
};
