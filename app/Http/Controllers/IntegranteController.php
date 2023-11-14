<?php

namespace App\Http\Controllers;

use App\Models\Integrante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class IntegranteController extends Controller {
    
    private $path = "fotos/integrantes";

    public function index()
    {
        $data = Integrante::orderBy('nome')->get();
        return view('integrante.index', compact('data'));
    }

    public function create() {
        return view('integrante.create');
    }

    public function store(Request $request) {
        
        // php artisan storage:link
        // Colocar os arquivos de imagem dentro da pasta "/storage/app/public"

        $regras = [
            'nome' => 'required|max:100|min:10',
            'biografia' => 'required|max:1000|min:20',
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
            $reg = new Integrante();
            $reg->nome = $request->nome;
            $reg->biografia = $request->biografia;
            $reg->save();    

            // Upload da Foto
            $id = $reg->id;
            $extensao_arq = $request->file('foto')->getClientOriginalExtension();
            $nome_arq = $id.'_'.time().'.'.$extensao_arq;
            $request->file('foto')->storeAs("public/$this->path", $nome_arq);
            $reg->foto = $this->path."/".$nome_arq;
            $reg->save();
        }
        
        return redirect()->route('integrante.index');
    }

    public function show($id) {
        
    }

    public function edit($id) {
        $integrante = Integrante::find($id);
        return view('integrante.edit', compact('integrante'));
    }

    public function update(Request $request, $id) {

        $regras = [
            'nome' => 'required|max:100|min:10',
            'biografia' => 'required|max:1000|min:20',
        ];
    
        $msgs = [
            "required" => "O preenchimento do campo [:attribute] é obrigatório!",
            "max" => "O campo [:attribute] possui tamanho máximo de [:max] caracteres!",
            "min" => "O campo [:attribute] possui tamanho mínimo de [:min] caracteres!",
        ];
    
        $request->validate($regras, $msgs);
    
        $integrante = Integrante::findOrFail($id);
        $integrante->nome = $request->nome;
        $integrante->biografia = $request->biografia;
    
        if ($request->hasFile('foto')) {
            // Upload da Nova Foto
            $extensao_arq = $request->file('foto')->getClientOriginalExtension();
            $nome_arq = $integrante->id . '_' . time() . '.' . $extensao_arq;
            $request->file('foto')->storeAs("public/$this->path", $nome_arq);
            $integrante->foto = $this->path . "/" . $nome_arq;
        }
    
        $integrante->save();
    
        return redirect()->route('integrante.index');

    }

    public function destroy($id) {
        $integrante = Integrante::findOrFail($id);

        // Remove a foto do integrante do armazenamento
        if ($integrante->foto) {
            $caminhoFoto = str_replace('storage', 'public', $integrante->foto);
            Storage::delete($caminhoFoto);
        }

        $integrante->delete();

        return redirect()->route('integrante.index');
    }
}
