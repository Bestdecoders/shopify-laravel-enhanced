<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('shopify_products', function (Blueprint $table) {
            $table->id();
            $table->string('product_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('shop_domain')->index();

            // Core product fields
            $table->string('title');
            $table->string('handle');
            $table->string('vendor')->nullable();
            $table->string('product_type')->nullable();
            $table->enum('status', ['active', 'draft', 'archived'])->default('active');

            // Collection and tagging
            $table->json('collection_ids')->nullable();
            $table->json('queried_collection_ids')->nullable(); // Collections specifically queried for
            $table->json('tags')->nullable();

            // Cache management
            $table->timestamp('last_accessed_at')->nullable();
            $table->timestamp('shopify_updated_at')->nullable();

            // Optional inventory fields (only if read_inventory scope enabled)
            $table->integer('price')->nullable()->comment('Price in cents');
            $table->integer('compare_at_price')->nullable()->comment('Compare at price in cents');
            $table->integer('inventory_quantity')->nullable();
            $table->integer('weight')->nullable()->comment('Weight in grams');

            $table->timestamps();

            // Indexes for efficient queries
            $table->index(['shop_domain', 'product_id']);
            $table->index(['shop_domain', 'vendor']);
            $table->index(['shop_domain', 'product_type']);
            $table->index(['shop_domain', 'status']);
            $table->index(['last_accessed_at']);

            // Unique constraint to prevent duplicate caching
            $table->unique(['shop_domain', 'product_id']);

            // Foreign key relationship
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('shopify_products');
    }
};