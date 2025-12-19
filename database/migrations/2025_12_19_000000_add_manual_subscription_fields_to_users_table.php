<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('manual_plan_price_id')->nullable()->after('stripe_id');
            $table->string('manual_plan_name')->nullable()->after('manual_plan_price_id');
            $table->timestamp('manual_plan_expires_at')->nullable()->after('manual_plan_name');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'manual_plan_price_id',
                'manual_plan_name',
                'manual_plan_expires_at',
            ]);
        });
    }
};
