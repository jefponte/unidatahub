<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Model;

class Bond extends Model
{
    protected $connection = 'sigaa';
    use HasFactory, Filterable;
    protected $table = 'vw_usuarios_catraca';

    public $incrementing = false;
    public $timestamps = false;

    protected $primaryKey = null;
    protected $hidden = [
        'senha'
    ];
    protected $fillable = [
        'id_usuario',
        'nome',
        'cpf_cnpj',
        'email',
        'telefone_celular',
        'descricao_unidade',
        'sigla_unidade',
    ];
}
