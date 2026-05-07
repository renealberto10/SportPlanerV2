<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventoResource;
use App\Http\Resources\MantenimientoResource;
use App\Models\CambioPieza;
use App\Models\Escenario;
use App\Models\Equipo;
use App\Models\Evento;
use App\Models\Mantenimiento;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $now = Carbon::now();
        $mes = $request->get('mes', $now->month);
        $anio = $request->get('anio', $now->year);

        $mantsTotal = Mantenimiento::count();
        $mantsMes = Mantenimiento::whereMonth('fecha', $mes)->whereYear('fecha', $anio)->count();
        $eventosTotal = Evento::count();
        $eventosMes = Evento::whereMonth('fecha', $mes)->whereYear('fecha', $anio)->count();
        $equiposTotal = Equipo::count();
        $equiposOperativos = Equipo::where('estado', 'operativo')->count();
        $escenariosActivos = Escenario::where('activo', true)->count();

        $proxMants = Mantenimiento::with('escenario')
            ->where('fecha', '>=', $now->toDateString())
            ->orderBy('fecha')
            ->limit(5)
            ->get();

        $proxEventos = Evento::with('escenario')
            ->where('fecha', '>=', $now->toDateString())
            ->whereIn('estado', ['programado', 'en_curso'])
            ->orderBy('fecha')
            ->limit(5)
            ->get();

        $mantsPorEscenario = Escenario::withCount(['mantenimientos'])->get()
            ->map(fn($e) => ['nombre' => substr($e->nombre, 0, 30), 'total' => $e->mantenimientos_count]);

        $equiposPorTipo = Equipo::selectRaw('tipo, count(*) as total')
            ->groupBy('tipo')
            ->pluck('total', 'tipo');

        return response()->json([
            'stats' => [
                'mantenimientos_mes' => $mantsMes,
                'mantenimientos_total' => $mantsTotal,
                'eventos_mes' => $eventosMes,
                'eventos_total' => $eventosTotal,
                'equipos_total' => $equiposTotal,
                'equipos_operativos' => $equiposOperativos,
                'escenarios_activos' => $escenariosActivos,
            ],
            'proximos_mantenimientos' => $proxMants,
            'proximos_eventos' => $proxEventos,
            'mantenimientos_por_escenario' => $mantsPorEscenario,
            'equipos_por_tipo' => $equiposPorTipo,
        ]);
    }

    public function reporte(Request $request)
    {
        $mes = $request->get('mes', Carbon::now()->month);
        $anio = $request->get('anio', Carbon::now()->year);
        $escenarioId = $request->get('escenario_id');

        $mantsQuery = Mantenimiento::with(['escenario', 'tecnicoRel'])
            ->whereMonth('fecha', $mes)
            ->whereYear('fecha', $anio);

        $eventosQuery = Evento::with('escenario')
            ->whereMonth('fecha', $mes)
            ->whereYear('fecha', $anio);

        $piezasQuery = CambioPieza::with(['escenario', 'equipo', 'tecnico'])
            ->whereMonth('fecha', $mes)
            ->whereYear('fecha', $anio);

        if ($escenarioId) {
            $mantsQuery->where('escenario_id', $escenarioId);
            $eventosQuery->where('escenario_id', $escenarioId);
            $piezasQuery->where('escenario_id', $escenarioId);
        }

        $mants   = $mantsQuery->orderBy('fecha')->get();
        $eventos = $eventosQuery->orderBy('fecha')->get();
        $piezas  = $piezasQuery->orderBy('fecha')->get();

        $escenarios = $escenarioId
            ? Escenario::where('id', $escenarioId)->get()
            : Escenario::where(function ($q) use ($mes, $anio) {
                $q->whereHas('mantenimientos', fn($qq) => $qq->whereMonth('fecha', $mes)->whereYear('fecha', $anio))
                  ->orWhereHas('eventos',      fn($qq) => $qq->whereMonth('fecha', $mes)->whereYear('fecha', $anio));
            })->get();

        return response()->json([
            'mes'            => $mes,
            'anio'           => $anio,
            'escenarios'     => $escenarios,
            'mantenimientos' => MantenimientoResource::collection($mants)->resolve(),
            'eventos'        => EventoResource::collection($eventos)->resolve(),
            'cambios_piezas' => $piezas,
            'total_horas'    => $mants->sum('horas'),
            'total_preventivos' => $mants->where('tipo', 'preventivo')->count(),
            'total_correctivos' => $mants->where('tipo', 'correctivo')->count(),
            'total_operativos'  => $mants->where('tipo', 'operativo')->count(),
        ]);
    }
}
