<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SolicitudResource;
use App\Models\Solicitud;
use Illuminate\Http\Request;

class SolicitudController extends Controller
{
    public function index(Request $request)
    {
        $query = Solicitud::with(['escenario', 'tecnico']);

        if ($request->filled('estado'))    $query->where('estado', $request->estado);
        if ($request->filled('prioridad')) $query->where('prioridad', $request->prioridad);
        if ($request->filled('tecnico_id')) $query->where('tecnico_id', $request->tecnico_id);
        if ($request->filled('escenario_id')) $query->where('escenario_id', $request->escenario_id);
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('actividad', 'like', '%' . $request->search . '%')
                  ->orWhere('solicita', 'like', '%' . $request->search . '%');
            });
        }

        return SolicitudResource::collection(
            $query->orderBy('fecha_solicitud', 'desc')->get()
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'fecha_solicitud'     => 'required|date',
            'actividad'           => 'required|string',
            'escenario_id'        => 'nullable|exists:escenarios,id',
            'escenario_texto'     => 'nullable|string|max:500',
            'solicita'            => 'required|string|max:255',
            'fecha_calendarizada' => 'nullable|date',
            'hora'                => 'nullable|date_format:H:i',
            'tecnico_id'          => 'nullable|exists:tecnicos,id',
            'seguimiento'         => 'nullable|string',
            'prioridad'           => 'required|in:alto,medio,bajo',
            'estado'              => 'required|in:pendiente,en_proceso,completado,cancelado',
            'notas'               => 'nullable|string',
            'emails_invitar'      => 'nullable|string|max:1000',
        ]);

        $sol = Solicitud::create($data);
        $sol->load(['escenario', 'tecnico']);
        return new SolicitudResource($sol);
    }

    public function show(Solicitud $solicitud)
    {
        $solicitud->load(['escenario', 'tecnico']);
        return new SolicitudResource($solicitud);
    }

    public function update(Request $request, Solicitud $solicitud)
    {
        $data = $request->validate([
            'fecha_solicitud'     => 'sometimes|required|date',
            'actividad'           => 'sometimes|required|string',
            'escenario_id'        => 'nullable|exists:escenarios,id',
            'escenario_texto'     => 'nullable|string|max:500',
            'solicita'            => 'sometimes|required|string|max:255',
            'fecha_calendarizada' => 'nullable|date',
            'hora'                => 'nullable|date_format:H:i',
            'tecnico_id'          => 'nullable|exists:tecnicos,id',
            'seguimiento'         => 'nullable|string',
            'prioridad'           => 'sometimes|required|in:alto,medio,bajo',
            'estado'              => 'sometimes|required|in:pendiente,en_proceso,completado,cancelado',
            'notas'               => 'nullable|string',
            'emails_invitar'      => 'nullable|string|max:1000',
        ]);

        $solicitud->update($data);
        $solicitud->load(['escenario', 'tecnico']);
        return new SolicitudResource($solicitud);
    }

    public function destroy(Solicitud $solicitud)
    {
        $solicitud->delete();
        return response()->json(['message' => 'Solicitud eliminada']);
    }
}
