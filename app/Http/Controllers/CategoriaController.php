<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Http\Resources\ProductoResource;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{

    public function index()
    {
        //
    }


    public function store(Request $request)
    {
        //
    }


    public function show(string $id)
    {
        //
    }


    public function update(Request $request, string $id)
    {
        //
    }

 
    public function destroy(string $id)
    {
        //
    }


    public function productos($id)
    {
        $categoria = Categoria::with('productos')->findOrFail($id);
        return ProductoResource::collection($categoria->productos);
    }
}