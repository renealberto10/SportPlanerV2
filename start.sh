#!/bin/bash
export PATH="/opt/homebrew/bin:/usr/local/bin:$PATH"
ROOT="$(cd "$(dirname "$0")" && pwd)"

echo "============================================"
echo "  ISATECH — Sistema de Gestión INDES"
echo "  OC 115/2026"
echo "============================================"

# Start Laravel API
echo "[1/2] Iniciando Laravel API en puerto 8000..."
cd "$ROOT/api"
php artisan serve --port=8000 &
API_PID=$!

sleep 2

# Start Vue frontend
echo "[2/2] Iniciando Vue frontend en puerto 5173..."
cd "$ROOT/frontend"
npm run dev &
FRONT_PID=$!

echo ""
echo "✅ App corriendo:"
echo "   Frontend → http://localhost:5173"
echo "   API      → http://localhost:8000/api/v1"
echo ""
echo "Presiona Ctrl+C para detener ambos servicios."

trap "kill $API_PID $FRONT_PID 2>/dev/null; echo 'Servicios detenidos.'" INT TERM
wait
