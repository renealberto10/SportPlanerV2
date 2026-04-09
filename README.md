# ISATECH — Sistema de Gestión INDES OC 115/2026

## Stack
- **Backend**: Laravel 13 (API REST) en `/api`
- **Frontend**: Vue 3 + Vite + Pinia + Tailwind en `/frontend`
- **Base de datos**: SQLite (archivo `api/database/database.sqlite`)

## Iniciar el proyecto

```bash
./start.sh
```

Abre → http://localhost:5173

## Desarrollo individual

```bash
# API
cd api && php artisan serve --port=8000

# Frontend
cd frontend && npm run dev
```

## Base de datos
Los 9 escenarios INDES están pre-cargados. Para resetear:
```bash
cd api && php artisan migrate:fresh --seed
```

## Módulos
| Módulo | Descripción |
|--------|-------------|
| Dashboard | Stats, gráficas, próximas actividades |
| Escenarios | 9 escenarios con sistema COLOSSEO |
| Equipos | Inventario de pantallas, bocinas, consolas, servidores |
| Calendario | Vista mensual de mantenimientos y eventos |
| Mantenimiento | Bitácora de visitas técnicas |
| Eventos | Bitácora de eventos deportivos |
| Reportes | Informe mensual imprimible por escenario |
