<?php

use Hyperf\Database\Schema\Schema;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Migrations\Migration;

class CreateResultFilesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('result_files', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('filename')->index();
            $table->string('path')->nullable();
            $table->string('download_path')->nullable();
            $table->boolean('processed')->default(false);
            $table->integer('filesize')->default(0);
            $table->integer('modified_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('result_files');
    }
}
