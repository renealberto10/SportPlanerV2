<?php

namespace Database\Seeders;

use App\Models\Escenario;
use App\Models\Equipo;
use App\Models\Evento;
use App\Models\Mantenimiento;
use App\Models\Tecnico;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Admin user ──────────────────────────────────────
        User::updateOrCreate(
            ['email' => 'admin@isatech.sv'],
            [
                'name'     => 'Administrador ISATECH',
                'password' => Hash::make('Admin2026!'),
                'rol'      => 'admin',
                'activo'   => true,
            ]
        );

        // ── Escenarios INDES ────────────────────────────────
        $escenarios = [
            ['nombre' => 'Estadio Nacional Las Delicias',                     'descripcion' => 'Estadio principal de fútbol',           'activo' => true,  'tiene_colosseo' => true],
            ['nombre' => 'Gimnasio Nacional José Adolfo Pineda',               'descripcion' => 'Gimnasio multideportivo techado',        'activo' => true,  'tiene_colosseo' => true],
            ['nombre' => 'Estadio Nacional Jorge "El Mágico" González',        'descripcion' => 'Estadio de fútbol',                     'activo' => true,  'tiene_colosseo' => true],
            ['nombre' => 'Estadio de Sóftbol Pablo Arnoldo Guzmán',            'descripcion' => 'Estadio de sóftbol',                    'activo' => true,  'tiene_colosseo' => true],
            ['nombre' => 'Parque de Pelota Saturnino Bengoa',                  'descripcion' => 'Parque de béisbol y pelota',            'activo' => true,  'tiene_colosseo' => true],
            ['nombre' => 'Polideportivo de Ciudad Merliot',                    'descripcion' => 'Complejo polideportivo',                'activo' => true,  'tiene_colosseo' => true],
            ['nombre' => 'Polideportivo El Polvorín',                          'descripcion' => 'Complejo polideportivo',                'activo' => true,  'tiene_colosseo' => true],
            ['nombre' => 'Palacio de los Deportes Carlos "El Famoso" Hernández','descripcion' => 'Palacio de deportes',                  'activo' => true,  'tiene_colosseo' => true],
            ['nombre' => 'Bodega de Repuestos — Villa Centroamericana',         'descripcion' => 'Bodega de repuestos y almacén',         'activo' => false, 'tiene_colosseo' => false],
        ];

        $esc = [];
        foreach ($escenarios as $data) {
            $esc[] = Escenario::firstOrCreate(['nombre' => $data['nombre']], $data);
        }

        // ── Técnicos ────────────────────────────────────────
        $tecnicosData = [
            ['nombre' => 'Carlos',   'apellido' => 'Ramírez',   'telefono' => '7891-2345', 'email' => 'c.ramirez@isatech.sv',   'especialidad' => 'video',      'activo' => true],
            ['nombre' => 'María',    'apellido' => 'González',  'telefono' => '7654-3210', 'email' => 'm.gonzalez@isatech.sv',  'especialidad' => 'audio',      'activo' => true],
            ['nombre' => 'Roberto',  'apellido' => 'Hernández', 'telefono' => '7123-4567', 'email' => 'r.hernandez@isatech.sv', 'especialidad' => 'electronica','activo' => true],
            ['nombre' => 'Ana',      'apellido' => 'López',     'telefono' => '7456-7890', 'email' => 'a.lopez@isatech.sv',     'especialidad' => 'redes',      'activo' => true],
            ['nombre' => 'Jorge',    'apellido' => 'Martínez',  'telefono' => '7789-0123', 'email' => 'j.martinez@isatech.sv',  'especialidad' => 'general',    'activo' => true],
            ['nombre' => 'Patricia', 'apellido' => 'Flores',    'telefono' => '7234-5678', 'email' => 'p.flores@isatech.sv',    'especialidad' => 'video',      'activo' => true],
            ['nombre' => 'Luis',     'apellido' => 'Castillo',  'telefono' => '7567-8901', 'email' => 'l.castillo@isatech.sv',  'especialidad' => 'audio',      'activo' => false],
        ];

        $tecs = [];
        foreach ($tecnicosData as $data) {
            $tecs[] = Tecnico::firstOrCreate(
                ['email' => $data['email']],
                $data
            );
        }

        // ── Equipos ─────────────────────────────────────────
        $equiposBase = [
            ['tipo' => 'pantalla',  'nombre' => 'Pantalla COLOSSEO Principal',   'modelo' => 'COLOSSEO HD-01', 'estado' => 'operativo'],
            ['tipo' => 'pantalla',  'nombre' => 'Pantalla COLOSSEO Secundaria',  'modelo' => 'COLOSSEO HD-02', 'estado' => 'operativo'],
            ['tipo' => 'bocina',    'nombre' => 'Sistema de Audio Central',       'modelo' => 'JBL PRO-2K',    'estado' => 'operativo'],
            ['tipo' => 'consola',   'nombre' => 'Consola de Audio Principal',     'modelo' => 'Yamaha CL5',    'estado' => 'operativo'],
            ['tipo' => 'servidor',  'nombre' => 'Servidor COLOSSEO',              'modelo' => 'Dell R750',     'estado' => 'operativo'],
        ];

        // Distribuir equipos en los primeros 6 escenarios activos
        foreach (array_slice($esc, 0, 6) as $idx => $escenario) {
            foreach ($equiposBase as $i => $eqData) {
                Equipo::firstOrCreate(
                    [
                        'nombre'      => $eqData['nombre'],
                        'escenario_id' => $escenario->id,
                    ],
                    [
                        'tipo'              => $eqData['tipo'],
                        'modelo'            => $eqData['modelo'],
                        'serie'             => strtoupper('SN-' . substr(md5($escenario->id . $i), 0, 8)),
                        'estado'            => ($idx === 2 && $i === 2) ? 'mantenimiento' : $eqData['estado'],
                        'escenario_id'      => $escenario->id,
                        'fecha_instalacion' => '2024-03-15',
                        'notas'             => 'Instalado bajo OC 115/2026',
                    ]
                );
            }
        }

        // Un equipo con falla
        Equipo::firstOrCreate(
            ['nombre' => 'Bocina Zona Norte', 'escenario_id' => $esc[0]->id],
            ['tipo' => 'bocina', 'modelo' => 'QSC K12.2', 'serie' => 'SN-FALLA001',
             'estado' => 'falla', 'escenario_id' => $esc[0]->id, 'fecha_instalacion' => '2024-01-10']
        );

        // ── Mantenimientos ───────────────────────────────────
        $today   = now();
        $meses   = [-3, -2, -1, 0]; // últimos 3 meses + mes actual

        $mantsSeed = [
            // mes -3
            ['dias' => -90, 'esc' => 0, 'tec' => 0, 'tipo' => 'preventivo',  'estado' => 'completado', 'horas' => 4,   'act' => 'Limpieza y calibración pantallas COLOSSEO. Verificación de conectores y cables.',   'obs' => 'Todo en orden.'],
            ['dias' => -85, 'esc' => 1, 'tec' => 1, 'tipo' => 'operativo',   'estado' => 'completado', 'horas' => 2.5, 'act' => 'Verificación sistema de audio previo a evento deportivo.',                         'obs' => 'Sin novedad.'],
            ['dias' => -80, 'esc' => 2, 'tec' => 2, 'tipo' => 'correctivo',  'estado' => 'completado', 'horas' => 6,   'act' => 'Reemplazo de fuente de poder en servidor COLOSSEO. Pruebas post-reparación.',       'obs' => 'Fuente sustituida exitosamente.'],
            // mes -2
            ['dias' => -60, 'esc' => 3, 'tec' => 0, 'tipo' => 'preventivo',  'estado' => 'completado', 'horas' => 3.5, 'act' => 'Mantenimiento preventivo mensual. Actualización firmware pantallas.',              'obs' => 'Firmware actualizado a v2.1.4.'],
            ['dias' => -55, 'esc' => 4, 'tec' => 1, 'tipo' => 'operativo',   'estado' => 'completado', 'horas' => 2,   'act' => 'Configuración audio para evento de sóftbol nacional.',                             'obs' => 'Sin inconvenientes.'],
            ['dias' => -50, 'esc' => 0, 'tec' => 2, 'tipo' => 'correctivo',  'estado' => 'completado', 'horas' => 5,   'act' => 'Reparación bocina zona norte. Diagnóstico y cambio de driver.',                    'obs' => 'Driver reemplazado.'],
            ['dias' => -45, 'esc' => 1, 'tec' => 3, 'tipo' => 'preventivo',  'estado' => 'completado', 'horas' => 3,   'act' => 'Revisión cableado red y switches. Pruebas de conectividad.',                       'obs' => 'Red estable.'],
            // mes -1
            ['dias' => -30, 'esc' => 5, 'tec' => 0, 'tipo' => 'preventivo',  'estado' => 'completado', 'horas' => 4,   'act' => 'Limpieza general y calibración de pantallas COLOSSEO.',                          'obs' => 'Calibración completada.'],
            ['dias' => -25, 'esc' => 6, 'tec' => 5, 'tipo' => 'operativo',   'estado' => 'completado', 'horas' => 2,   'act' => 'Soporte técnico evento cultural. Operación pantallas y audio.',                   'obs' => 'Evento exitoso.'],
            ['dias' => -20, 'esc' => 7, 'tec' => 1, 'tipo' => 'preventivo',  'estado' => 'completado', 'horas' => 4.5, 'act' => 'Revisión sistema completo Palacio de Deportes. Pruebas de pantallas.',            'obs' => 'Pantalla 2 requiere ajuste de brillo.'],
            ['dias' => -15, 'esc' => 2, 'tec' => 4, 'tipo' => 'correctivo',  'estado' => 'completado', 'horas' => 3,   'act' => 'Corrección falla en consola de audio. Actualización software.',                   'obs' => 'Software actualizado.'],
            // mes actual
            ['dias' =>  -7, 'esc' => 0, 'tec' => 0, 'tipo' => 'preventivo',  'estado' => 'completado', 'horas' => 3.5, 'act' => 'Mantenimiento preventivo mensual Las Delicias. Limpieza y revisión.',            'obs' => 'En perfectas condiciones.'],
            ['dias' =>  -3, 'esc' => 3, 'tec' => 5, 'tipo' => 'operativo',   'estado' => 'completado', 'horas' => 2,   'act' => 'Soporte técnico evento deportivo. Operación de pantallas.',                      'obs' => 'Sin novedad.'],
            ['dias' =>   3, 'esc' => 1, 'tec' => 0, 'tipo' => 'preventivo',  'estado' => 'pendiente',  'horas' => 4,   'act' => 'Mantenimiento preventivo mensual programado.',                                    'obs' => ''],
            ['dias' =>   7, 'esc' => 4, 'tec' => 2, 'tipo' => 'correctivo',  'estado' => 'pendiente',  'horas' => 3,   'act' => 'Revisión y corrección de falla en pantalla COLOSSEO.',                           'obs' => ''],
        ];

        foreach ($mantsSeed as $m) {
            $fecha = $today->copy()->addDays($m['dias'])->format('Y-m-d');
            $tec   = $tecs[$m['tec']];
            Mantenimiento::firstOrCreate(
                ['fecha' => $fecha, 'escenario_id' => $esc[$m['esc']]->id, 'tecnico_id' => $tec->id],
                [
                    'escenario_id' => $esc[$m['esc']]->id,
                    'tecnico_id'   => $tec->id,
                    'tecnico'      => $tec->nombre . ' ' . $tec->apellido,
                    'fecha'        => $fecha,
                    'hora'         => '08:00',
                    'tipo'         => $m['tipo'],
                    'actividades'  => $m['act'],
                    'observaciones'=> $m['obs'],
                    'estado'       => $m['estado'],
                    'horas'        => $m['horas'],
                    'personal'     => '',
                ]
            );
        }

        // ── Eventos ─────────────────────────────────────────
        $eventosSeed = [
            ['dias' => -60, 'esc' => 0, 'nombre' => 'Copa El Salvador — Semifinales',            'tipo' => 'deportivo',  'estado' => 'realizado',   'personal' => 'Carlos Ramírez, María González'],
            ['dias' => -45, 'esc' => 1, 'nombre' => 'Campeonato Nacional de Baloncesto',          'tipo' => 'deportivo',  'estado' => 'realizado',   'personal' => 'Jorge Martínez, Patricia Flores'],
            ['dias' => -30, 'esc' => 7, 'nombre' => 'Festival Cultural INDES 2026',               'tipo' => 'cultural',   'estado' => 'realizado',   'personal' => 'María González, Luis Castillo'],
            ['dias' => -15, 'esc' => 2, 'nombre' => 'Copa El Salvador — Final',                   'tipo' => 'deportivo',  'estado' => 'realizado',   'personal' => 'Carlos Ramírez, Ana López'],
            ['dias' =>  -7, 'esc' => 3, 'nombre' => 'Torneo Nacional de Sóftbol',                 'tipo' => 'deportivo',  'estado' => 'realizado',   'personal' => 'Roberto Hernández'],
            ['dias' =>   5, 'esc' => 0, 'nombre' => 'Eliminatoria FIFA — El Salvador vs Guatemala','tipo' => 'deportivo',  'estado' => 'programado',  'personal' => 'Carlos Ramírez, Jorge Martínez'],
            ['dias' =>  10, 'esc' => 1, 'nombre' => 'Torneo Centroamericano de Boxeo',            'tipo' => 'deportivo',  'estado' => 'programado',  'personal' => 'Patricia Flores'],
            ['dias' =>  15, 'esc' => 5, 'nombre' => 'Transmisión Producción Canal 4',             'tipo' => 'produccion', 'estado' => 'programado',  'personal' => 'María González, Ana López'],
            ['dias' =>  20, 'esc' => 6, 'nombre' => 'Juegos Deportivos Nacionales 2026',          'tipo' => 'deportivo',  'estado' => 'programado',  'personal' => 'Carlos Ramírez, Roberto Hernández'],
            ['dias' =>  30, 'esc' => 7, 'nombre' => 'Gran Premio de Atletismo INDES',             'tipo' => 'deportivo',  'estado' => 'programado',  'personal' => 'Jorge Martínez, Patricia Flores'],
        ];

        foreach ($eventosSeed as $e) {
            $fecha = $today->copy()->addDays($e['dias'])->format('Y-m-d');
            Evento::firstOrCreate(
                ['nombre' => $e['nombre'], 'fecha' => $fecha],
                [
                    'escenario_id' => $esc[$e['esc']]->id,
                    'nombre'       => $e['nombre'],
                    'fecha'        => $fecha,
                    'hora'         => '14:00',
                    'tipo'         => $e['tipo'],
                    'estado'       => $e['estado'],
                    'descripcion'  => 'Evento organizado por INDES. Producción técnica a cargo de ISATECH.',
                    'personal'     => $e['personal'],
                    'equipos_notas'=> '2x Pantallas COLOSSEO, Sistema de audio completo, Consola de mezclas',
                ]
            );
        }
    }
}
