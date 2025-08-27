<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            // relation utilisateur
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // infos paiement
            $table->string('description');
            $table->decimal('amount', 12, 2);

            // statut process mock (PENDING par défaut)
            $table->enum('status', ['PENDING','SUCCESS','FAILED'])->default('PENDING');

            // catégorie (optionnel, pour ton dashboard/filtre)
            $table->enum('category', ['electricity','internet','water','rent','other'])->default('other');

            // justificatif (PDF / image) stocké en public
            $table->string('receipt_path')->nullable();

            // date de paiement effective si SUCCESS
            $table->timestamp('paid_at')->nullable();

            $table->timestamps();

            // index utiles
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
