<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Endereco extends Model
{
    protected $table = 'enderecos';
    protected $primaryKey = 'id';
    protected $fillable = [
        'logradouro', 'numero', 'bairro', 'cep', 'complemento', 'cidade', 'estado'
    ];
}
