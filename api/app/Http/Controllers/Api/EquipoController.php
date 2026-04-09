<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EquipoResource;
use App\Models\Equipo;
use Illuminate\Http\Request;

class EquipoController extends Controller
{
    public function index(Request $request)
    {
        $query = Equipo::with('escenario');
        if ($request->filled('escenario_id')) {
            $query->where('escenario_id', $request->escenario_id);
        }
        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nombre', 'like', '%' . $request->search . '%')
                  ->orWhere('modelo', 'like', '%' . $request->search . '%')
                  ->orWhere('serie', 'like', '%' . $request->search . '%');
            });
        }
        return EquipoResource::collection($query->latest()->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'escenario_id' => 'nullable|exists:escenarios,id',
            'nombre' => 'required|string|max:255',
            'tipo' => 'required|in:pantalla,bocina,consola,servidor,otro',
            'modelo' => 'nullable|string|max:255',
            'serie' => 'nullable|string|max:255',
            'estado' => 'required|in:operativo,mantenimiento,falla,baja',
            'fecha_instalacion' => 'nullable|date',
            'notas' => 'nullable|string',
        ]);
        $equipo = Equipo::create($data);
        $equipo->load('escenario');
        return new EquipoResource($equipo);
    }

    public function show(Equipo $equipo)
    {
        $equipo->load('escenario');
        return new EquipoResource($equipo);
    }

    public function update(Request $request, Equipo $equipo)
    {
        $data = $request->validate([
            'escenario_id' => 'nullable|exists:escenarios,id',
            'nombre' => 'sometimes|required|string|max:255',
            'tipo' => 'sometimes|required|in:pantalla,bocina,consola,servidor,otro',
            'modelo' => 'nullable|string|max:255',
            'serie' => 'nullable|string|max:255',
            'estado' => 'sometimes|required|in:operativo,mantenimiento,falla,baja',
            'fecha_instalacion' => 'nullable|date',
            'notas' => 'nullable|string',
        ]);
        $equipo->update($data);
        $equipo->load('escenario');
        return new EquipoResource($equipo);
    }

    public function destroy(Equipo $equipo)
    {
        $equipo->delete();
        return response()->json(['message' => 'Equipo eliminado']);
    }
}
