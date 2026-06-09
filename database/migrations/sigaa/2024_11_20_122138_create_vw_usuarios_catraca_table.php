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
        Schema::create('vw_usuarios_catraca', function (Blueprint $table) {
            $table->integer('id_usuario')->primary();
            $table->string('nome')->nullable();
            $table->string('identidade')->nullable();
            $table->string('cpf_cnpj')->nullable();
            $table->string('passaporte')->nullable();
            $table->string('email')->nullable();
            $table->string('login')->nullable();
            $table->string('senha')->nullable();
            $table->string('matricula_disc')->nullable();
            $table->string('nivel_discente')->nullable();
            $table->integer('id_status_discente')->nullable();
            $table->string('status_discente')->nullable();
            $table->integer('id_curso')->nullable();
            $table->string('nome_curso')->nullable();
            $table->integer('id_turno')->nullable();
            $table->string('turno')->nullable();
            $table->integer('id_servidor')->nullable();
            $table->string('siape')->nullable();
            $table->integer('id_status_servidor')->nullable();
            $table->string('status_servidor')->nullable();
            $table->integer('id_tipo_usuario')->nullable();
            $table->string('tipo_usuario')->nullable();
            $table->integer('id_categoria')->nullable();
            $table->string('categoria')->nullable();
            $table->integer('status_sistema')->nullable();
            $table->integer('id_unidade')->nullable();
            $table->string('descricao_unidade')->nullable();
            $table->string('sigla_unidade')->nullable();
            $table->date('data_nascimento')->nullable();
            $table->string('genero')->nullable();
            $table->string('ingresso')->nullable();
            $table->string('codigo_area_nacional_telefone_fixo')->nullable();
            $table->string('telefone_fixo')->nullable();
            $table->string('codigo_area_nacional_telefone_celular')->nullable();
            $table->string('telefone_celular')->nullable();
            $table->string('nacionalidade')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vw_usuarios_catraca');
    }
};
