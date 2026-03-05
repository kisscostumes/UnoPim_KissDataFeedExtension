<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kiss_datafeed_credentials', function (Blueprint $table) {
            $table->id();
            $table->string('api_url', 500);
            $table->string('client_id', 100);
            $table->text('client_secret');
            $table->text('access_token')->nullable();
            $table->timestamp('token_expires_at')->nullable();
            $table->boolean('active')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kiss_datafeed_credentials');
    }
};
