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
            $table->id();
            $table->foreignId('upload_id')
                ->constrained()
                ->cascadeOnDelete();

            // CSV Data
            $table->string('handle')->nullable();
            $table->string('title');
            $table->longText('body_html')->nullable();
            $table->string('vendor')->nullable();
            $table->string('product_type')->nullable();
            $table->text('tags')->nullable();
            $table->boolean('published')->default(false);

            // Variant
            $table->string('sku')->nullable()->index();
            $table->decimal('price', 10, 2)->nullable();
            $table->decimal('compare_at_price', 10, 2)->nullable();
            $table->boolean('requires_shipping')->default(true);
            $table->boolean('taxable')->default(true);
            $table->string('inventory_tracker')->nullable();
            $table->integer('inventory_qty')->default(0);
            $table->string('inventory_policy')->nullable();
            $table->string('fulfillment_service')->nullable();
            $table->decimal('weight', 10, 2)->nullable();
            $table->string('weight_unit')->nullable();

            // Image
            $table->text('image_src')->nullable();
            $table->integer('image_position')->nullable();
            $table->string('image_alt_text')->nullable();

            // Shopify
            $table->string('shopify_product_id')->nullable();
            $table->string('shopify_variant_id')->nullable();

            // Status
            $table->enum('status', [
                'pending',
                'processing',
                'success',
                'failed'
            ])->default('pending');

            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
