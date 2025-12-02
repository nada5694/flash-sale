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
        Schema::create('products', function (Blueprint $table) {
            $table->id(); // Auto-increment
            $table->string('name');
            $table->decimal('price', 10, 2);
            $table->integer('stock');
            $table->timestamps();
        });

        DB::statement("ALTER TABLE products AUTO_INCREMENT = 55820;");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
