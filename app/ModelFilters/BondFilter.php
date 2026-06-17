<?php

namespace App\ModelFilters;

class BondFilter extends DefaultModelFilter
{
    protected $sortable = ['id_usuario'];

    public function setup()
    {
        // Desabilita a ordenação padrão caso não seja especificada
        $this->blacklistMethod('isSortable');
    }

    public function name($name)
    {
        if (env('DB_CONNECTION') === 'pgsql') {
            $this->where('nome', 'ILIKE', "%$name%");
        } else {
            $this->whereRaw("LOWER(nome) LIKE ?", ['%' . strtolower($name) . '%']);
        }
    }

    public function email($email)
    {
        if (env('DB_CONNECTION') === 'pgsql') {
            $this->where('email', 'ILIKE', "%$email%");
        } else {
            $this->whereRaw("LOWER(email) LIKE ?", ['%' . strtolower($email) . '%']);
        }
    }
    public function login($login)
    {
        $login = trim($login);

        if (env('DB_CONNECTION') === 'pgsql') {
            $this->whereRaw('LOWER(login) = ?', [strtolower($login)]);
        } else {
            $this->whereRaw('LOWER(login) = ?', [strtolower($login)]);
        }
    }
    public function id($id)
    {
        $this->where('id_usuario', $id); // Ajuste para usar a chave correta
    }
}
