<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EscenarioResource;
use App\Models\Escenario;
use Illuminate\Http\Request;

class EscenarioController extends Controller
{
    public function index()
    {
        $escenarios = Escenario::withCount(['equipos', 'mantenimientos', 'eventos'])->get();
        return EscenarioResource::collection($escenarios);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'direccion' => 'nullable|string|max:255',
            'activo' => 'boolean',
            'tiene_colosseo' => 'boolean',
        ]);
        $escenario = Escenario::create($data);
        return new EscenarioResource($escenario);
    }

    public function show(Escenario $escenario)
    {
        $escenario->loadCount(['equipos', 'mantenimientos', 'eventos']);
        return new EscenarioResource($escenario);
    }

    public function update(Request $request, Escenario $escenario)
    {
        $data = $request->validate([
            'nombre' => 'sometimes|required|string|max:255',
            'descripcion' => 'nullable|string',
            'direccion' => 'nullable|string|max:255',
            'activo' => 'boolean',
            'tiene_colosseo' => 'boolean',
        ]);
        $escenario->update($data);
        return new EscenarioResource($escenario);
    }

    public function destroy(Escenario $escenario)
    {
        $escenario->delete();
        return response()->json(['message' => 'Escenario eliminado']);
    }
}
