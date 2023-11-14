<?php

namespace App\Http\Controllers;

use App\Models\Material;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MaterialController extends Controller {

    private $path = "fotos/materiais";

    public function index() {
        $data = Material::orderBy('nome')->get();
        return view('material.index', compact('data'));
    }

    public function create() {
        return view('material.create');
    }

    public function store(Request $request) {

        $regras = [
            'nome' => 'required|max:100|min:10',
            'descricao' => 'required|max:1000|min:20',
            'foto' => 'required'
        ];

        $msgs = [
            "required" => "O preenchimento do campo [:attribute] é obrigatório!",
            "max" => "O campo [:attribute] possui tamanho máximo de [:max] caracteres!",
            "min" => "O campo [:attribute] possui tamanho mínimo de [:min] caracteres!",
        ];

        $request->validate($regras, $msgs);

        if($request->hasFile('foto')) {

            // Insert no Banco
            $reg = new Material();
            $reg->nome = $request->nome;
            $reg->descricao = $request->descricao;
            $reg->save();    

            // Upload da Foto
            $id = $reg->id;
            $extensao_arq = $request->file('foto')->getClientOriginalExtension();
            $nome_arq = $id.'_'.time().'.'.$extensao_arq;
            $request->file('foto')->storeAs("public/$this->path", $nome_arq);
            $reg->foto = $this->path."/".$nome_arq;
            $reg->save();
        }
        
        return redirect()->route('material.index');
        
    }

    public function show($id) {
        
    }

    public function edit($id) {
        $material = Material::find($id);
        return view('material.edit', compact('material'));
    }

    public function update(Request $request, $id) {
        $regras = [
            'nome' => 'required|max:100|min:10',
            'descricao' => 'required|max:1000|min:20',
        ];
    
        $msgs = [
            "required" => "O preenchimento do campo [:attribute] é obrigatório!",
            "max" => "O campo [:attribute] possui tamanho máximo de [:max] caracteres!",
            "min" => "O campo [:attribute] possui tamanho mínimo de [:min] caracteres!",
        ];
    
        $request->validate($regras, $msgs);
    
        $material = Material::findOrFail($id);
        $material->nome = $request->nome;
        $material->descricao = $request->descricao;
    
        if ($request->hasFile('foto')) {
            // Upload da Nova Foto
            $extensao_arq = $request->file('foto')->getClientOriginalExtension();
            $nome_arq = $material->id . '_' . time() . '.' . $extensao_arq;
            $request->file('foto')->storeAs("public/$this->path", $nome_arq);
            $material->foto = $this->path . "/" . $nome_arq;
        }
    
        $material->save();
    
        return redirect()->route('material.index');
    }

    public function destroy($id) {
        $material = Material::findOrFail($id);

        // Remove a foto do integrante do armazenamento
        if ($material->foto) {
            $caminhoFoto = str_replace('storage', 'public', $material->foto);
            Storage::delete($caminhoFoto);
        }

        $material->delete();

        return redirect()->route('material.index');
    }
}
