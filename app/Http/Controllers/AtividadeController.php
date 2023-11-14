<?php

namespace App\Http\Controllers;

use App\Models\Atividade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AtividadeController extends Controller {

    private $path = "fotos/atividades";

    public function index() {
        $data = Atividade::orderBy('data')->get();
        return view('atividade.index', compact('data'));

    }

    public function create() {
        return view('atividade.create');
    }

    public function store(Request $request) {
        
        $regras = [
            'nome' => 'required|max:100|min:10',
            'descricao' => 'required|max:1000|min:20',
            'foto' => 'required',
            'data' => 'required|date',
        ];

        $msgs = [
            "required" => "O preenchimento do campo [:attribute] é obrigatório!",
            "max" => "O campo [:attribute] possui tamanho máximo de [:max] caracteres!",
            "min" => "O campo [:attribute] possui tamanho mínimo de [:min] caracteres!",
        ];

        $request->validate($regras, $msgs);

        if($request->hasFile('foto')) {

            // Insert no Banco
            $reg = new Atividade();
            $reg->nome = $request->nome;
            $reg->descricao = $request->descricao;
            $reg->data = $request->data;
            $reg->save();    

            // Upload da Foto
            $id = $reg->id;
            $extensao_arq = $request->file('foto')->getClientOriginalExtension();
            $nome_arq = $id.'_'.time().'.'.$extensao_arq;
            $request->file('foto')->storeAs("public/$this->path", $nome_arq);
            $reg->foto = "$this->path/$nome_arq";
            $reg->save();
        }
        
        return redirect()->route('atividade.index');
    }

    public function show($id) {
        
    }

    public function edit($id) {
        $atividade = Atividade::find($id);
        return view('atividade.edit', compact('atividade'));
    }

    public function update(Request $request, $id) {
        
        $regras = [
            'nome' => 'required|max:100|min:10',
            'descricao' => 'required|max:1000|min:20',
            'foto' => 'required',
            'data' => 'required|date',
        ];
    
        $msgs = [
            "required" => "O preenchimento do campo [:attribute] é obrigatório!",
            "max" => "O campo [:attribute] possui tamanho máximo de [:max] caracteres!",
            "min" => "O campo [:attribute] possui tamanho mínimo de [:min] caracteres!",
        ];
    
        $request->validate($regras, $msgs);
    
        $atividade = Atividade::findOrFail($id);
        $atividade->nome = $request->nome;
        $atividade->descricao = $request->descricao;
        $atividade->data = $request->data;
    
        if ($request->hasFile('foto')) {
            // Upload da Nova Foto
            $extensao_arq = $request->file('foto')->getClientOriginalExtension();
            $nome_arq = $atividade->id . '_' . time() . '.' . $extensao_arq;
            $request->file('foto')->storeAs("public/$this->path", $nome_arq);
            $atividade->foto = "$this->path/$nome_arq";
        }
    
        $atividade->save();
    
        return redirect()->route('atividade.index');
    }

    public function destroy($id) {
        $atividade = Atividade::findOrFail($id);

        // Remove a foto da atividade do armazenamento
        if ($atividade->foto) {
            $caminhoFoto = str_replace('storage', 'public', $atividade->foto);
            Storage::delete($caminhoFoto);
        }

        $atividade->delete();

        return redirect()->route('atividade.index');
    }
}
