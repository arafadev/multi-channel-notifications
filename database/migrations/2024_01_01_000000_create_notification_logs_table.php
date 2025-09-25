<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->string('recipient')->index();
            $table->string('channel')->nullable()->index();
            $table->string('title');
            $table->text('body')->nullable();
            $table->json('data')->nullable();
            $table->enum('status', ['sent', 'failed', 'pending'])->default('pending')->index();
            $table->string('message_id')->nullable()->index();
            $table->text('error')->nullable();
            $table->json('provider_response')->nullable();
            $table->integer('attempts')->default(1);
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();
            
            $table->index(['status', 'created_at']);
            $table->index(['channel', 'status']);
            $table->index(['recipient', 'created_at']);
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('notification_logs');
    }
};
