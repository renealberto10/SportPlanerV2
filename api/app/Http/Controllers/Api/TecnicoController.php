<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TecnicoResource;
use App\Models\Tecnico;
use Illuminate\Http\Request;

class TecnicoController extends Controller {

    public function index(Request $request) {
        $query = Tecnico::withCount('mantenimientos');
        if ($request->filled('activo')) $query->where('activo', $request->boolean('activo'));
        if ($request->filled('search')) $query->where(function($q) use ($request) {
            $q->where('nombre',   'like', '%'.$request->search.'%')
              ->orWhere('apellido','like', '%'.$request->search.'%')
              ->orWhere('email',   'like', '%'.$request->search.'%');
        });
        return TecnicoResource::collection($query->orderBy('nombre')->get());
    }

    public function store(Request $request) {
        $data = $request->validate([
            'nombre'       => 'required|string|max:100',
            'apellido'     => 'required|string|max:100',
            'telefono'     => 'nullable|string|max:20',
            'email'        => 'nullable|email|max:150',
            'especialidad' => 'required|in:audio,video,electronica,redes,general',
            'activo'       => 'boolean',
            'notas'        => 'nullable|string',
        ]);
        $t = Tecnico::create($data);
        $t->loadCount('mantenimientos');
        return new TecnicoResource($t);
    }

    public function show(Tecnico $tecnico) {
        $tecnico->loadCount('mantenimientos');
        return new TecnicoResource($tecnico);
    }

    public function update(Request $request, Tecnico $tecnico) {
        $data = $request->validate([
            'nombre'       => 'sometimes|required|string|max:100',
            'apellido'     => 'sometimes|required|string|max:100',
            'telefono'     => 'nullable|string|max:20',
            'email'        => 'nullable|email|max:150',
            'especialidad' => 'sometimes|required|in:audio,video,electronica,redes,general',
            'activo'       => 'boolean',
            'notas'        => 'nullable|string',
        ]);
        $tecnico->update($data);
        return new TecnicoResource($tecnico);
    }

    public function destroy(Tecnico $tecnico) {
        $tecnico->delete();
        return response()->json(['message' => 'Técnico eliminado']);
    }
}
