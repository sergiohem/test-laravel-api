<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use App\Models\Categoria;
use Illuminate\Support\Facades\DB;
use JWTAuth;
use Exception;

class CategoriaController extends Controller
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
        $categorias = Categoria::all();

        return response()->json([
            'success' => true,
            'data' => $categorias
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
            'nome' => 'required|unique:categorias'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        $categoria = new Categoria;

        try {
            $categoria->nome = $request->nome;
            $categoria->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Categoria cadastrada com sucesso!',
                'data' => $categoria->toArray()
            ], 201);
        } catch (Exception $ex) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erro ao cadastrar categoria!'
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
        $categoria = Categoria::find($id);

        if (!isset($categoria)) {
            return response()->json([
                'success' => false,
                'message' => 'Categoria não encontrada!'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'data' => $categoria->toArray()
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
            'nome' => 'required|unique:categorias,nome,' . $id,
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $categoria = Categoria::find($id);

        if (!isset($categoria)) {
            return response()->json([
                'success' => false,
                'message' => 'Categoria não encontrada!'
            ], 400);
        }

        DB::beginTransaction();

        try {

            $categoria->nome = $request->nome;
            $categoria->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Categoria atualizada com sucesso!',
                'data' => $categoria->toArray()
            ]);
        } catch (Exception $ex) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar categoria!'
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
        $categoria = Categoria::find($id);

        if (!isset($categoria)) {
            return response()->json([
                'success' => false,
                'message' => 'Categoria não encontrada!'
            ], 400);
        }

        DB::beginTransaction();

        try {
            $jsonRetorno = [
                'success' => true,
                'message' => 'Categoria excluída com sucesso!'
            ];

            if ($categoria->produtos->count() == 0) {
                $categoria->delete();
            } else {
                $jsonRetorno = [
                    'success' => false,
                    'message' => 'Erro! Categoria possui produtos relacionados!'
                ];
            }

            DB::commit();

            return response()->json($jsonRetorno);

        } catch (Exception $ex) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error ao excluir categoria!'
            ], 500);
        }
    }
}
