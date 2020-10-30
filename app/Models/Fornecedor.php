<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fornecedor extends Model
{
    protected $table = 'fornecedores';
    protected $primaryKey = 'id';
    protected $fillable = [
        'nomeFantasia', 'razaoSocial', 'cnpj', 'idEndereco'
    ];

    public function telefones()
    {
        return $this->hasMany('App\Models\TelefoneFornecedor', 'idFornecedor');
    }

    public function produtos()
    {
        return $this->belongsToMany('App\Models\Produto', 'fornecedor_has_produto', 'idFornecedor', 'idProduto')->withTimestamps();
    }

    public function endereco()
    {
        return $this->belongsTo('App\Models\Endereco', 'idEndereco');
    }
}
