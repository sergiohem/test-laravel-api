<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TelefoneFornecedor extends Model
{
    protected $table = 'telefones_fornecedores';
    protected $primaryKey = 'id';
    protected $fillable = [
        'idFornecedor', 'telefone'
    ];

    public function fornecedor()
    {
        return $this->belongsTo('App\Models\Fornecedor', 'idFornecedor');
    }
}
