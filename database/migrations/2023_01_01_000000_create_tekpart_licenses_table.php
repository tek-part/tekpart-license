<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTekpartLicensesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tekpart_licenses', function (Blueprint $table) {
            $table->id();
            $table->string('license_key')->unique();
            $table->string('product_name');
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('domain')->nullable();
            $table->string('ip_address')->nullable();
            $table->date('issued_at');
            $table->date('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('features')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tekpart_licenses');
    }
}
