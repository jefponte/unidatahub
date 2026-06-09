<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class HealthController extends Controller
{
    /**
     * Health check: retorna status da API e dos bancos de dados.
     */
    public function __invoke(): JsonResponse
    {
        $connections = ['default', 'sigaa', 'sistemas_comum'];

        $databases = [];

        foreach ($connections as $name) {
            $connection = $name === 'default' ? DB::connection() : DB::connection($name);
            try {
                $connection->getPdo();
                $connection->select('select 1');
                $databases[$name] = 'connected';
            } catch (\Exception $e) {
                $databases[$name] = 'down';
            }
        }

        $overall = collect($databases)->contains('down') ? 'down' : 'ok';

        return response()->json([
            'status'    => $overall,
            'timestamp' => now()->toIso8601String(),
            'databases' => $databases,
        ], $overall === 'ok' ? 200 : 503);
    }
}
