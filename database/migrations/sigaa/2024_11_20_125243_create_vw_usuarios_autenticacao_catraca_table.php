<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'sigaa';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vw_usuarios_autenticacao_catraca', function (Blueprint $table) {
            $table->integer('id_usuario')->primary();
            $table->string('nome')->nullable();
            $table->string('cpf_cnpj')->nullable();
            $table->string('passaporte')->nullable();
            $table->string('email')->unique();
            $table->string('login')->unique();
            $table->string('senha');
            $table->integer('id_servidor')->nullable();
            $table->string('siape')->nullable();
            $table->integer('id_status_servidor')->nullable();
            $table->string('status_servidor')->nullable();
            $table->integer('id_tipo_usuario')->nullable();
            $table->string('tipo_usuario')->nullable();
            $table->integer('id_categoria')->nullable();
            $table->string('categoria')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vw_usuarios_autenticacao_catraca');
    }
};
