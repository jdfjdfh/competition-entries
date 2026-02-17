<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contest_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->enum('status', ['draft', 'submitted', 'needs_fix', 'accepted', 'rejected'])->default('draft');
            $table->timestamps();

            $table->index(['contest_id', 'user_id']);
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('submissions');
    }
};
