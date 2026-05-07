<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MantenimientoResource;
use App\Models\Mantenimiento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MantenimientoController extends Controller
{
    public function index(Request $request)
    {
        $query = Mantenimiento::with(['escenario','tecnicoRel']);
        if ($request->filled('escenario_id')) {
            $query->where('escenario_id', $request->escenario_id);
        }
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }
        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }
        if ($request->filled('mes') && $request->filled('anio')) {
            $query->whereMonth('fecha', $request->mes)->whereYear('fecha', $request->anio);
        }
        if ($request->filled('tecnico_id')) {
            $query->where('tecnico_id', $request->tecnico_id);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('tecnico', 'like', '%' . $request->search . '%')
                  ->orWhere('actividades', 'like', '%' . $request->search . '%');
            });
        }
        return MantenimientoResource::collection($query->orderBy('fecha', 'desc')->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'escenario_id' => 'required|exists:escenarios,id',
            'fecha' => 'required|date',
            'hora' => 'nullable|date_format:H:i,H:i:s',
            'tecnico_id' => 'nullable|exists:tecnicos,id',
            'tecnico' => 'nullable|string|max:255',
            'tipo' => 'required|in:preventivo,correctivo,operativo',
            'actividades' => 'nullable|string',
            'observaciones' => 'nullable|string',
            'estado' => 'required|in:pendiente,en_proceso,completado',
            'horas' => 'nullable|numeric|min:0',
            'personal' => 'nullable|string',
        ]);
        $mant = Mantenimiento::create($data);
        $mant->load(['escenario','tecnicoRel']);
        return new MantenimientoResource($mant);
    }

    public function show(Mantenimiento $mantenimiento)
    {
        $mantenimiento->load(['escenario','tecnicoRel']);
        return new MantenimientoResource($mantenimiento);
    }

    public function update(Request $request, Mantenimiento $mantenimiento)
    {
        $data = $request->validate([
            'escenario_id' => 'sometimes|required|exists:escenarios,id',
            'fecha' => 'sometimes|required|date',
            'hora' => 'nullable|date_format:H:i,H:i:s',
            'tecnico_id' => 'nullable|exists:tecnicos,id',
            'tecnico' => 'nullable|string|max:255',
            'tipo' => 'sometimes|required|in:preventivo,correctivo,operativo',
            'actividades' => 'nullable|string',
            'observaciones' => 'nullable|string',
            'estado' => 'sometimes|required|in:pendiente,en_proceso,completado',
            'horas' => 'nullable|numeric|min:0',
            'personal' => 'nullable|string',
        ]);
        $mantenimiento->update($data);
        $mantenimiento->load(['escenario','tecnicoRel']);
        return new MantenimientoResource($mantenimiento);
    }

    public function destroy(Mantenimiento $mantenimiento)
    {
        // Delete associated photos from storage
        foreach ($mantenimiento->fotos ?? [] as $foto) {
            $path = is_array($foto) ? ($foto['path'] ?? null) : $foto;
            if ($path) Storage::disk('public')->delete($path);
        }
        $mantenimiento->delete();
        return response()->json(['message' => 'Registro eliminado']);
    }

    public function uploadFoto(Request $request, Mantenimiento $mantenimiento)
    {
        $request->validate([
            'foto' => 'required|image|max:10240|mimes:jpeg,jpg,png,webp',
            'tipo' => 'nullable|in:antes,despues',
        ]);

        $tipo = $request->input('tipo', 'despues');
        $path = $request->file('foto')->store('mantenimientos/' . $mantenimiento->id, 'public');

        $fotos   = $mantenimiento->fotos ?? [];
        $fotos[] = ['path' => $path, 'tipo' => $tipo];
        $mantenimiento->update(['fotos' => $fotos]);

        return response()->json([
            'url'   => asset('storage/' . $path),
            'path'  => $path,
            'tipo'  => $tipo,
            'fotos' => $this->normalizeFotos($mantenimiento->fresh()->fotos),
        ]);
    }

    public function removeFoto(Request $request, Mantenimiento $mantenimiento)
    {
        $request->validate(['path' => 'required|string']);
        $fotos = $mantenimiento->fotos ?? [];
        $path  = $request->path;

        $found = false;
        $remaining = [];
        foreach ($fotos as $f) {
            $fPath = is_array($f) ? ($f['path'] ?? null) : $f;
            if ($fPath === $path) {
                $found = true;
                continue;
            }
            $remaining[] = $f;
        }

        if ($found) {
            Storage::disk('public')->delete($path);
            $mantenimiento->update(['fotos' => $remaining]);
        }

        return response()->json([
            'message' => 'Foto eliminada',
            'fotos'   => $this->normalizeFotos($mantenimiento->fresh()->fotos),
        ]);
    }

    private function normalizeFotos($fotos): array
    {
        return collect($fotos ?? [])->map(function ($f) {
            if (is_string($f)) {
                return ['path' => $f, 'tipo' => 'despues', 'url' => asset('storage/' . $f)];
            }
            $path = $f['path'] ?? '';
            return [
                'path' => $path,
                'tipo' => in_array($f['tipo'] ?? null, ['antes', 'despues'], true) ? $f['tipo'] : 'despues',
                'url'  => asset('storage/' . $path),
            ];
        })->values()->all();
    }
}
