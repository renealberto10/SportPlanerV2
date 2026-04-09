<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventoResource;
use App\Models\Evento;
use Illuminate\Http\Request;

class EventoController extends Controller
{
    public function index(Request $request)
    {
        $query = Evento::with('escenario');
        if ($request->filled('escenario_id')) {
            $query->where('escenario_id', $request->escenario_id);
        }
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }
        if ($request->filled('mes') && $request->filled('anio')) {
            $query->whereMonth('fecha', $request->mes)->whereYear('fecha', $request->anio);
        }
        return EventoResource::collection($query->orderBy('fecha', 'desc')->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'escenario_id' => 'required|exists:escenarios,id',
            'nombre' => 'required|string|max:255',
            'fecha' => 'required|date',
            'hora' => 'nullable|date_format:H:i',
            'tipo' => 'required|in:deportivo,cultural,produccion,otro',
            'estado' => 'required|in:programado,en_curso,realizado,cancelado',
            'descripcion' => 'nullable|string',
            'personal' => 'nullable|string',
            'equipos_notas' => 'nullable|string',
        ]);
        $evento = Evento::create($data);
        $evento->load('escenario');
        return new EventoResource($evento);
    }

    public function show(Evento $evento)
    {
        $evento->load('escenario');
        return new EventoResource($evento);
    }

    public function update(Request $request, Evento $evento)
    {
        $data = $request->validate([
            'escenario_id' => 'sometimes|required|exists:escenarios,id',
            'nombre' => 'sometimes|required|string|max:255',
            'fecha' => 'sometimes|required|date',
            'hora' => 'nullable|date_format:H:i',
            'tipo' => 'sometimes|required|in:deportivo,cultural,produccion,otro',
            'estado' => 'sometimes|required|in:programado,en_curso,realizado,cancelado',
            'descripcion' => 'nullable|string',
            'personal' => 'nullable|string',
            'equipos_notas' => 'nullable|string',
        ]);
        $evento->update($data);
        $evento->load('escenario');
        return new EventoResource($evento);
    }

    public function destroy(Evento $evento)
    {
        $evento->delete();
        return response()->json(['message' => 'Evento eliminado']);
    }
}
