<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFornecedorHasProdutoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fornecedor_has_produto', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('idFornecedor')->unsigned();
            $table->foreign('idFornecedor')
                    ->references('id')
                    ->on('fornecedores');
            $table->bigInteger('idProduto')->unsigned();
            $table->foreign('idProduto')
                    ->references('id')
                    ->on('produtos');
            $table->decimal('custoProduto', 10, 2);
            $table->timestamps();
            $table->engine = 'InnoDB';
            $table->charset = 'utf8';
            $table->collation = 'utf8_unicode_ci';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fornecedor_has_produto');
    }
}
