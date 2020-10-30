<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use App\Models\Fornecedor;
use App\Models\Produto;
use App\Models\Categoria;
use Illuminate\Support\Facades\DB;
use JWTAuth;
use Exception;
use App\Helpers\Utilities;

class ProdutoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    protected $user;

    public function __construct()
    {
        //Verificando se usuário está autenticado
        $this->user = JWTAuth::parseToken()->authenticate();
    }

    public function index()
    {
        $produtos = Produto::with('fornecedores', 'categoria')->get();

        return response()->json([
            'success' => true,
            'data' => $produtos
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nome' => 'required|unique:produtos|max:200',
            'idCategoria' => 'required|integer',
            'custosFornecedores' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        //Validações de FKs (fornecedores e categoria)
        $retornoValidacao = json_decode($this->validarRequestFks($request));

        if ($retornoValidacao->validated == false) {
            return response()->json([
                'success' => false,
                'message' => $retornoValidacao->message
            ], 500);
        }

        DB::beginTransaction();

        $produto = new Produto;

        try {
            $produto->nome = $request->nome;
            //Protegendo contra inserção de scripts maliciosos em um possível textarea
            $produto->descricao = htmlentities($request->descricao);
            $produto->idCategoria = $request->idCategoria;
            $produto->save();

            foreach ($request->custosFornecedores as $custoFornecedor) {
                $fornecedor = Fornecedor::find($custoFornecedor['idFornecedor']);
                $produto->fornecedores()->save($fornecedor, ['custoProduto' => $custoFornecedor['custo']]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Produto cadastrado com sucesso!',
                'data' => Produto::with('fornecedores', 'categoria')->find($produto->id)->toArray()
            ], 201);
        } catch (Exception $ex) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erro ao cadastrar produto!'
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $produto = Produto::with('fornecedores', 'categoria')->find($id);

        if (!isset($produto)) {
            return response()->json([
                'success' => false,
                'message' => 'Produto não encontrado!'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'data' => $produto->toArray()
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'nome' => 'required|max:200|unique:produtos,nome,' . $id,
            'idCategoria' => 'required|integer',
            'custosFornecedores' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        //Validações de FKs (fornecedores e categoria)
        $retornoValidacao = json_decode($this->validarRequestFks($request));

        if ($retornoValidacao->validated == false) {
            return response()->json([
                'success' => false,
                'message' => $retornoValidacao->message
            ], 500);
        }

        DB::beginTransaction();

        $produto = Produto::find($id);
        if (!isset($produto)) {
            return response()->json([
                'success' => false,
                'message' => 'Produto não encontrado!'
            ], 400);
        }

        try {
            $produto->nome = $request->nome;
            //Protegendo contra inserção de scripts maliciosos em um possível textarea
            $produto->descricao = htmlentities($request->descricao);
            $produto->idCategoria = $request->idCategoria;
            $produto->save();

            //Sincronizando N pra N com fornecedores
            $custos = [];
            $idsFornecedores = [];

            foreach ($request->custosFornecedores as $custoFornecedor) {
                array_push($custos, $custoFornecedor['custo']);
                array_push($idsFornecedores, $custoFornecedor['idFornecedor']);
            }

            $custosFornecedores = array_map(function($custo){
                return ['custoProduto' => $custo];
            }, $custos);

            $data = array_combine($idsFornecedores, $custosFornecedores);

            $produto->fornecedores()->sync($data);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Produto atualizado com sucesso!',
                'data' => Produto::with('fornecedores', 'categoria')->find($produto->id)->toArray()
            ], 201);
        } catch (Exception $ex) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar produto!'
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $produto = Produto::find($id);

        if (!isset($produto)) {
            return response()->json([
                'success' => false,
                'message' => 'Produto não encontrado!'
            ], 400);
        }

        DB::beginTransaction();

        try {

            $produto->fornecedores()->sync([]);
            $produto->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Produto excluído com sucesso!'
            ]);

        } catch (Exception $ex) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error ao excluir produto!'
            ], 500);
        }
    }

    public function validarRequestFks($request) {

        $result = [
            'validated' => true
        ];

        //Verificações de fornecedores do produto
        $custosFornecedores = $request->custosFornecedores;
        if (!is_array($custosFornecedores)) {
            $result = [
                'validated' => false,
                'message' => 'É necessário enviar um array de custos do produto e seus respectivos fornecedores!'
            ];
        } else if (count($custosFornecedores) == 0) {
            $result = [
                'validated' => false,
                'message' => 'Nenhum fornecedor informado para o produto!'
            ];
        } else {
            foreach ($custosFornecedores as $custoFornecedor) {
                $fornecedor = Fornecedor::find($custoFornecedor['idFornecedor']);
                if (!isset($fornecedor)) {
                    $result = [
                        'validated' => false,
                        'message' => 'O fornecedor ' . $custoFornecedor['idFornecedor'] . ' é inválido!'
                    ];
                    break;
                } else if (!isset($custoFornecedor['custo']) || $custoFornecedor['custo'] == '' || $custoFornecedor['custo'] < 0) {
                    $result = [
                        'validated' => false,
                        'message' => 'O custo do produto pelo fornecedor #' . $custoFornecedor['idFornecedor'] . ' é inválido!'
                    ];
                    break;
                }
            }
        }

        //Verificação de categoria do produto
        $categoria = Categoria::find($request->idCategoria);
        if (!isset($categoria)) {
            $result = [
                'validated' => false,
                'message' => 'Categoria do produto inválida!'
            ];
        }

        return json_encode($result);
    }
}
