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
        Schema::table('products', function (Blueprint $table) {
            // Check and add brand_id if it doesn't exist
            if (!Schema::hasColumn('products', 'brand_id')) {
                $table->foreignId('brand_id')->nullable()->constrained()->cascadeOnDelete();
            }

            // Check and add category_id if it doesn't exist
            if (!Schema::hasColumn('products', 'category_id')) {
                $table->foreignId('category_id')->nullable()->constrained()->cascadeOnDelete();
            }

            // Check and add subcategory_id if it doesn't exist
            if (!Schema::hasColumn('products', 'subcategory_id')) {
                $table->foreignId('subcategory_id')->nullable()->constrained('sub_categories')->cascadeOnDelete();
            }

            // Check and add is_active if it doesn't exist
            if (!Schema::hasColumn('products', 'is_active')) {
                $table->boolean('is_active')->default(true);
            }

            // Check and add is_stock if it doesn't exist
            if (!Schema::hasColumn('products', 'is_stock')) {
                $table->boolean('is_stock')->default(true);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Drop foreign keys and columns if they exist
            if (Schema::hasColumn('products', 'subcategory_id')) {
                $table->dropForeign(['subcategory_id']);
                $table->dropColumn('subcategory_id');
            }

            if (Schema::hasColumn('products', 'category_id')) {
                $table->dropForeign(['category_id']);
                $table->dropColumn('category_id');
            }

            if (Schema::hasColumn('products', 'brand_id')) {
                $table->dropForeign(['brand_id']);
                $table->dropColumn('brand_id');
            }

            if (Schema::hasColumn('products', 'is_active')) {
                $table->dropColumn('is_active');
            }

            if (Schema::hasColumn('products', 'is_stock')) {
                $table->dropColumn('is_stock');
            }
        });
    }
};
