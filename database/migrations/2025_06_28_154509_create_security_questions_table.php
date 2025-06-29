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
        // Verifica se la tabella esiste già, se no la crea
        if (!Schema::hasTable('security_questions')) {
            Schema::create('security_questions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('question');
                $table->string('answer_hash');
                $table->timestamps();
                
                // Indici per performance
                $table->index('user_id');
            });
        } else {
            // Se la tabella esiste, aggiungi eventuali colonne mancanti
            Schema::table('security_questions', function (Blueprint $table) {
                // Aggiungi colonne se non esistono
                if (!Schema::hasColumn('security_questions', 'created_at')) {
                    $table->timestamps();
                }
                
                // Aggiungi indici se non esistono
                if (!Schema::hasIndex('security_questions', ['user_id'])) {
                    $table->index('user_id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('security_questions');
    }
};