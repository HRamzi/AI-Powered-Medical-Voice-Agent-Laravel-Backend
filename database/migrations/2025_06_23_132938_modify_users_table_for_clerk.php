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
        Schema::table('users', function (Blueprint $table) {
            // Add Clerk-specific fields
            $table->string('clerk_id')->unique()->after('id');
            $table->integer('credits')->default(0)->after('email');
            
            // Remove Laravel Auth fields we don't need
            $table->dropColumn(['email_verified_at', 'password', 'remember_token']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Remove Clerk fields
            $table->dropColumn(['clerk_id', 'credits']);
            
            // Restore Laravel Auth fields
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
        });
    }
};
