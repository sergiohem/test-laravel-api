<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use App\Models\Fornecedor;
use App\Models\Endereco;
use App\Models\TelefoneFornecedor;
use Illuminate\Support\Facades\DB;
use JWTAuth;
use Exception;
use App\Helpers\Utilities;

class FornecedorController extends Controller
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
        $fornecedores = Fornecedor::with('telefones', 'produtos', 'endereco')->get();

        return response()->json([
            'success' => true,
            'data' => $fornecedores
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
            'nomeFantasia' => 'required',
            'razaoSocial' => 'required',
            'cnpj' => 'required|min:14|max:18',
            'cep' => 'required|min:8|max:10',
            'numero' => 'required',
            'bairro' => 'required',
            'logradouro' => 'required',
            'cidade' => 'required',
            'estado' => 'required',
            'telefones' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        //Chamada de função do helper Utilities para validação de CNPJ
        if (!Utilities::validarCnpj($request->cnpj)) {
            return response()->json([
                'success' => false,
                'message' => 'CNPJ inválido!'
            ], 422);
        }

        //Verificações de telefones do fornecedor
        $telefones = $request->telefones;
        if (!is_array($telefones)) {
            return response()->json([
                'success' => false,
                'message' => 'É necessário enviar um array de telefones!'
            ], 500);
        } else if (count($telefones) == 0) {
            return response()->json([
                'success' => false,
                'message' => 'Nenhum telefone inserido para o fornecedor!'
            ], 422);
        }

        DB::beginTransaction();

        $fornecedor = new Fornecedor;
        $idEndereco = 0;

        try {

            $parametrosEndereco = [
                'id' => null,
                'cep' => $request->cep,
                'logradouro' => $request->logradouro,
                'numero' => $request->numero,
                'bairro' => $request->bairro,
                'complemento' => $request->complemento,
                'cidade' => $request->cidade,
                'estado' => $request->estado,
            ];

            //Chamada de função geral para salvar endereço. O ID recebido é salvo no fornecedor
            $idEndereco = Utilities::saveEndereco($parametrosEndereco);

            if (!isset($idEndereco) || $idEndereco == 0 || $idEndereco == null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao salvar endereço do fornecedor!'
                ], 500);
            }

            $fornecedor->nomeFantasia = $request->nomeFantasia;
            $fornecedor->razaoSocial = $request->razaoSocial;
            $fornecedor->cnpj = $request->cnpj;
            $fornecedor->idEndereco = $idEndereco;
            $fornecedor->save();

            //Inserção de telefones do fornecedor
            foreach ($telefones as $telefone) {
                $telFornecedor = new TelefoneFornecedor;
                $telFornecedor->idFornecedor = $fornecedor->id;
                $telFornecedor->telefone = $telefone;
                $telFornecedor->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Fornecedor cadastrado com sucesso!',
                'data' => $fornecedor->toArray()
            ], 201);
        } catch (Exception $ex) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erro ao cadastrar fornecedor!'
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
        $fornecedor = Fornecedor::with('telefones', 'produtos', 'endereco')->find($id);

        if (!isset($fornecedor)) {
            return response()->json([
                'success' => false,
                'message' => 'Fornecedor não encontrado!'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'data' => $fornecedor->toArray()
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
            'nomeFantasia' => 'required',
            'razaoSocial' => 'required',
            'cnpj' => 'required|min:14|max:18',
            'cep' => 'required|min:8|max:10',
            'numero' => 'required',
            'bairro' => 'required',
            'logradouro' => 'required',
            'cidade' => 'required',
            'estado' => 'required',
            'telefones' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        //Chamada de função do helper Utilities para validação de CNPJ
        if (!Utilities::validarCnpj($request->cnpj)) {
            return response()->json([
                'success' => false,
                'message' => 'CNPJ inválido!'
            ], 422);
        }

        //Verificações de telefones do fornecedor
        $telefones = $request->telefones;
        if (!is_array($telefones)) {
            return response()->json([
                'success' => false,
                'message' => 'É necessário enviar um array de telefones!'
            ], 500);
        } else if (count($telefones) == 0) {
            return response()->json([
                'success' => false,
                'message' => 'Nenhum telefone inserido para o fornecedor!'
            ], 422);
        }

        DB::beginTransaction();

        $fornecedor = Fornecedor::find($id);

        if (!isset($fornecedor)) {
            return response()->json([
                'success' => false,
                'message' => 'Fornecedor não encontrado!'
            ], 400);
        }

        try {

            $parametrosEndereco = [
                'id' => $fornecedor->idEndereco,
                'cep' => $request->cep,
                'logradouro' => $request->logradouro,
                'numero' => $request->numero,
                'bairro' => $request->bairro,
                'complemento' => $request->complemento,
                'cidade' => $request->cidade,
                'estado' => $request->estado,
            ];

            //Chamada de função geral para salvar endereço. O ID recebido é salvo no fornecedor
            $idEndereco = Utilities::saveEndereco($parametrosEndereco);

            if (!isset($idEndereco) || $idEndereco == 0 || $idEndereco == null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao salvar endereço do fornecedor!'
                ], 500);
            }

            $fornecedor->nomeFantasia = $request->nomeFantasia;
            $fornecedor->razaoSocial = $request->razaoSocial;
            $fornecedor->cnpj = $request->cnpj;
            $fornecedor->idEndereco = $idEndereco;
            $fornecedor->save();

            //Para fazer a atualização dos telefones do fornecedor, é necessário excluir os telefones atuais e inserir os novos,
            //pois o array de telefones vindo do request é composto por telefones em string, e não por objetos do tipo TelefoneFornecedor
            $fornecedor->telefones()->delete();

            foreach ($telefones as $telefone) {

                $telFornecedor = new TelefoneFornecedor;
                $telFornecedor->idFornecedor = $fornecedor->id;
                $telFornecedor->telefone = $telefone;
                $telFornecedor->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Fornecedor atualizado com sucesso!',
                'data' => Fornecedor::with('telefones', 'produtos', 'endereco')->find($fornecedor->id)->toArray()
            ], 201);
        } catch (Exception $ex) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar fornecedor!'
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
        $fornecedor = Fornecedor::find($id);

        if (!isset($fornecedor)) {
            return response()->json([
                'success' => false,
                'message' => 'Fornecedor não encontrado!'
            ], 400);
        }

        DB::beginTransaction();

        try {
            $jsonRetorno = [
                'success' => true,
                'message' => 'Fornecedor excluído com sucesso!'
            ];

            if ($fornecedor->produtos->count() == 0) {
                $fornecedor->telefones()->delete();
                $fornecedor->delete();
                $fornecedor->endereco->delete();
            } else {
                $jsonRetorno = [
                    'success' => false,
                    'message' => 'Erro! Fornecedor possui produtos relacionados!'
                ];
            }

            DB::commit();

            return response()->json($jsonRetorno);

        } catch (Exception $ex) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir fornecedor!'
            ], 500);
        }
    }
}
