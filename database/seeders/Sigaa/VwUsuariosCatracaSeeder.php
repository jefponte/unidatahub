<?php

namespace Database\Seeders\Sigaa;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VwUsuariosCatracaSeeder extends Seeder
{
    public function run(): void
    {
        DB::connection('sigaa')->table('vw_usuarios_autenticacao_catraca')->truncate();
        DB::connection('sigaa')->table('vw_usuarios_catraca')->truncate();

        DB::connection('sigaa')->table('vw_usuarios_catraca')->insert([
            [
                'id_usuario' => 7072406,
                'nome' => 'Servidor de Teste 1',
                'cpf_cnpj' => '12312312336',
                'email' => 'servidor1@unilab.edu.br',
                'login' => 'servidor1',
                'id_servidor' => 427,
                'siape' => '2164372',
                'id_status_servidor' => 1,
                'status_servidor' => 'Ativo',
                'id_tipo_usuario' => 1,
                'tipo_usuario' => 'Servidor',
                'id_categoria' => 2,
                'categoria' => 'Técnico Administrativo',
                'status_sistema' => 1,
                'id_unidade' => 103,
                'descricao_unidade' => 'DIRETORIA DE TECNOLOGIA DA INFORMAÇÃO',
                'sigla_unidade' => 'DTI',
                'data_nascimento' => '1989-01-08',
                'genero' => 'Masculino',
                'matricula_disc' => null,
                'nivel_discente' => null,
                'id_status_discente' => null,
                'status_discente' => null,
                'id_curso' => null,
                'nome_curso' => null,
                'id_turno' => null,
                'turno' => null,
                'passaporte' => null,
                'ingresso' => null,
                'codigo_area_nacional_telefone_fixo' => null,
                'telefone_fixo' => null,
                'codigo_area_nacional_telefone_celular' => null,
                'telefone_celular' => null,
                'nacionalidade' => null,
                'senha' => null,
            ],
            [
                'id_usuario' => 1358284,
                'nome' => 'ALUNO TESTE 1',
                'cpf_cnpj' => '123456789',
                'passaporte' => '123456789',
                'email' => 'aluno1@unilab.edu.br',
                'login' => 'aluno1',
                'matricula_disc' => '2022106402',
                'nivel_discente' => 'GRADUACAO',
                'id_status_discente' => 1,
                'status_discente' => 'ATIVO',
                'id_curso' => 4598906,
                'nome_curso' => 'FARMÁCIA',
                'id_turno' => 3,
                'turno' => 'Matutino e Vespertino',
                'id_tipo_usuario' => 2,
                'tipo_usuario' => 'Aluno',
                'status_sistema' => 1,
                'id_unidade' => 26,
                'descricao_unidade' => 'INSTITUTO DE CIÊNCIAS DA SAÚDE',
                'sigla_unidade' => 'ICS',
                'data_nascimento' => '1997-08-18',
                'genero' => 'Masculino',
                'ingresso' => '2022.1',
                'codigo_area_nacional_telefone_fixo' => '85',
                'telefone_fixo' => '988888888',
                'codigo_area_nacional_telefone_celular' => '85',
                'telefone_celular' => '988888888',
                'nacionalidade' => 'GUINEENSE',
                'id_servidor' => null,
                'siape' => null,
                'id_status_servidor' => null,
                'status_servidor' => null,
                'id_categoria' => null,
                'categoria' => null,
                'senha' => null,
            ],
        ]);

        DB::connection('sigaa')->table('vw_usuarios_autenticacao_catraca')->insert([
            [
                'id_usuario' => 7072406,
                'nome' => 'Servidor de Teste 1',
                'cpf_cnpj' => '12312312336',
                'passaporte' => null,
                'email' => 'servidor1@unilab.edu.br',
                'login' => 'servidor1',
                'senha' => '7470cb44a46c70275908d37c2680d454',
                'id_servidor' => 427,
                'siape' => '2164372',
                'id_status_servidor' => 1,
                'status_servidor' => 'Ativo',
                'id_tipo_usuario' => 1,
                'tipo_usuario' => 'Servidor',
                'id_categoria' => 2,
                'categoria' => 'Técnico Administrativo',
            ],
            [
                'id_usuario' => 1358284,
                'nome' => 'ALUNO TESTE 1',
                'cpf_cnpj' => '123456789',
                'passaporte' => '123456789',
                'email' => 'aluno1@unilab.edu.br',
                'login' => 'aluno1',
                'senha' => 'a9f598a223a555dd4acaaa5a5a46a9e8',
                'id_servidor' => null,
                'siape' => null,
                'id_status_servidor' => null,
                'status_servidor' => null,
                'id_tipo_usuario' => 2,
                'tipo_usuario' => 'Aluno',
                'id_categoria' => null,
                'categoria' => null,
            ],
        ]);
    }
}
