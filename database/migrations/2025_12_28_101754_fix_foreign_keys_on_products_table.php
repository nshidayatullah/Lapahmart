<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Step 1: Make columns nullable first
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('brand_id')->nullable()->change();
            $table->unsignedBigInteger('category_id')->nullable()->change();
        });

        // Step 2: Update invalid data (0 values) to NULL
        DB::table('products')->where('brand_id', 0)->update(['brand_id' => null]);
        DB::table('products')->where('category_id', 0)->update(['category_id' => null]);

        // Step 3: Add foreign key constraints
        Schema::table('products', function (Blueprint $table) {
            // Add foreign keys with try-catch to handle if they already exist
            try {
                $table->foreign('brand_id')->references('id')->on('brands')->cascadeOnDelete();
            } catch (\Exception $e) {
                // Foreign key already exists
            }

            try {
                $table->foreign('category_id')->references('id')->on('categories')->cascadeOnDelete();
            } catch (\Exception $e) {
                // Foreign key already exists
            }

            try {
                $table->foreign('subcategory_id')->references('id')->on('sub_categories')->cascadeOnDelete();
            } catch (\Exception $e) {
                // Foreign key already exists
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Drop foreign keys if they exist
            try {
                $table->dropForeign(['brand_id']);
            } catch (\Exception $e) {}

            try {
                $table->dropForeign(['category_id']);
            } catch (\Exception $e) {}

            try {
                $table->dropForeign(['subcategory_id']);
            } catch (\Exception $e) {}

            // Make columns NOT NULL again
            $table->unsignedBigInteger('brand_id')->nullable(false)->change();
            $table->unsignedBigInteger('category_id')->nullable(false)->change();
        });
    }
};
