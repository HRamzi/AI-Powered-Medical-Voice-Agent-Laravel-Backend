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
        Schema::table('consultation_sessions', function (Blueprint $table) {
            // Remove old columns that don't match frontend
            $table->dropColumn(['symptoms', 'doctor_type', 'voice_profile_id']);
            
            // Add new columns to match frontend SessionChatTable schema
            $table->uuid('session_id')->unique()->after('id');
            $table->text('notes')->nullable()->change();
            $table->json('selected_doctor')->nullable()->after('notes');
            $table->json('conversation')->nullable()->after('selected_doctor');
            $table->json('report')->nullable()->after('conversation');
            $table->string('created_by')->nullable()->after('report'); // email reference
            $table->string('created_on')->nullable()->after('created_by'); // string timestamp to match frontend
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('consultation_sessions', function (Blueprint $table) {
            // Restore original columns
            $table->dropColumn(['session_id', 'selected_doctor', 'conversation', 'report', 'created_by', 'created_on']);
            $table->text('symptoms')->after('user_id');
            $table->string('doctor_type')->after('notes');
            $table->string('voice_profile_id')->after('doctor_type');
        });
    }
}; 