<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('chats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->boolean('is_closed')->default(false);
            $table->timestamp('closed_at')->nullable();
            $table->unsignedBigInteger('posted_by');
            $table->timestamp('close_to')->nullable();
            $table->timestamps();

            //une clé unique pour éviter les doublons entre user et annonce
            $table -> unique(['created_by', 'posted_by']); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chats');
    }
};
