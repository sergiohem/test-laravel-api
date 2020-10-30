<?php
namespace App\Helpers;
use App\Models\Endereco;

class Utilities
{

    //Referência: https://gist.github.com/guisehn/3276302
    public static function validarCnpj($cnpj)
    {
        $cnpj = preg_replace('/[^0-9]/', '', (string) $cnpj);

        // Valida tamanho
        if (strlen($cnpj) != 14)
            return false;

        // Verifica se todos os digitos são iguais
        if (preg_match('/(\d)\1{13}/', $cnpj))
            return false;

        // Valida primeiro dígito verificador
        for ($i = 0, $j = 5, $soma = 0; $i < 12; $i++)
        {
            $soma += $cnpj[$i] * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }

        $resto = $soma % 11;

        if ($cnpj[12] != ($resto < 2 ? 0 : 11 - $resto))
            return false;

        // Valida segundo dígito verificador
        for ($i = 0, $j = 6, $soma = 0; $i < 13; $i++)
        {
            $soma += $cnpj[$i] * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }

        $resto = $soma % 11;

        return $cnpj[13] == ($resto < 2 ? 0 : 11 - $resto);
    }

    public static function saveEndereco($parametros) {
        $endereco = Endereco::find($parametros['id']);
        if (!isset($endereco)) {
            $endereco = new Endereco;
        }

        $endereco->cep = $parametros['cep'];
        $endereco->logradouro = $parametros['logradouro'];
        $endereco->numero = $parametros['numero'];
        $endereco->bairro = $parametros['bairro'];
        $endereco->complemento = $parametros['complemento'] != null && $parametros['complemento'] != '' ? $parametros['complemento'] : null;
        $endereco->cidade = $parametros['cidade'];
        $endereco->estado = $parametros['estado'];

        $endereco->save();

        return $endereco->id;
    }
}
