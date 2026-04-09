<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CambioPiezaResource;
use App\Models\CambioPieza;
use Illuminate\Http\Request;

class CambioPiezaController extends Controller
{
    public function index(Request $request)
    {
        $query = CambioPieza::with(['escenario', 'equipo', 'tecnico']);

        if ($request->filled('estado_bodega'))  $query->where('estado_bodega', $request->estado_bodega);
        if ($request->filled('escenario_id'))   $query->where('escenario_id', $request->escenario_id);
        if ($request->filled('mantenimiento_id')) $query->where('mantenimiento_id', $request->mantenimiento_id);
        if ($request->filled('evento_id'))      $query->where('evento_id', $request->evento_id);
        if ($request->filled('tecnico_id'))     $query->where('tecnico_id', $request->tecnico_id);

        return CambioPiezaResource::collection(
            $query->orderBy('fecha', 'desc')->get()
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'mantenimiento_id' => 'nullable|exists:mantenimientos,id',
            'evento_id'        => 'nullable|exists:eventos,id',
            'escenario_id'     => 'required|exists:escenarios,id',
            'equipo_id'        => 'nullable|exists:equipos,id',
            'descripcion_pieza'=> 'required|string|max:500',
            'tipo_pieza'       => 'required|in:led,tarjeta_señal,ventilador,cable,fuente_poder,conector,bocina,modulo,otro',
            'serie_instalada'  => 'nullable|string|max:255',
            'serie_retirada'   => 'nullable|string|max:255',
            'tecnico_id'       => 'nullable|exists:tecnicos,id',
            'fecha'            => 'required|date',
            'notas'            => 'nullable|string',
        ]);

        $cp = CambioPieza::create($data);
        $cp->load(['escenario', 'equipo', 'tecnico']);
        return new CambioPiezaResource($cp);
    }

    public function show(CambioPieza $cambioPieza)
    {
        $cambioPieza->load(['escenario', 'equipo', 'tecnico']);
        return new CambioPiezaResource($cambioPieza);
    }

    public function update(Request $request, CambioPieza $cambioPieza)
    {
        $data = $request->validate([
            'descripcion_pieza'=> 'sometimes|required|string|max:500',
            'tipo_pieza'       => 'sometimes|required|in:led,tarjeta_señal,ventilador,cable,fuente_poder,conector,bocina,modulo,otro',
            'serie_instalada'  => 'nullable|string|max:255',
            'serie_retirada'   => 'nullable|string|max:255',
            'tecnico_id'       => 'nullable|exists:tecnicos,id',
            'equipo_id'        => 'nullable|exists:equipos,id',
            'fecha'            => 'sometimes|required|date',
            'notas'            => 'nullable|string',
        ]);

        $cambioPieza->update($data);
        $cambioPieza->load(['escenario', 'equipo', 'tecnico']);
        return new CambioPiezaResource($cambioPieza);
    }

    // Bodega confirms receipt of a returned part
    public function confirmarRecepcion(Request $request, CambioPieza $cambioPieza)
    {
        $data = $request->validate([
            'recibido_por'   => 'required|string|max:255',
            'fecha_recepcion'=> 'required|date',
            'notas'          => 'nullable|string',
        ]);

        $cambioPieza->update([
            'estado_bodega'   => 'recibido',
            'recibido_por'    => $data['recibido_por'],
            'fecha_recepcion' => $data['fecha_recepcion'],
            'notas'           => $data['notas'] ?? $cambioPieza->notas,
        ]);

        $cambioPieza->load(['escenario', 'equipo', 'tecnico']);
        return new CambioPiezaResource($cambioPieza);
    }

    public function destroy(CambioPieza $cambioPieza)
    {
        $cambioPieza->delete();
        return response()->json(['message' => 'Registro eliminado']);
    }
}
