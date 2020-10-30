<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produto extends Model
{
    protected $table = 'produtos';
    protected $primaryKey = 'id';
    protected $fillable = [
        'nome', 'descricao', 'idCategoria'
    ];

    public function fornecedores()
    {
        return $this->belongsToMany('App\Models\Fornecedor', 'fornecedor_has_produto', 'idProduto', 'idFornecedor')->withPivot('custoProduto')->withTimestamps();
    }

    public function categoria()
    {
        return $this->belongsTo('App\Models\Categoria', 'idCategoria');
    }
}
