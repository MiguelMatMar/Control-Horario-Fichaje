<?php

require_once __DIR__ . './Fichaje.php';
require_once __DIR__ . './User.php';

class Reporte
{
    private Fichaje $fichajeModel;
    private User $userModel;

    public function __construct()
    {
        $this->fichajeModel = new Fichaje();
        $this->userModel = new User();
    }

    /* =====================================================
       Obtener reporte global de fichajes
       Opcionalmente filtrando por usuario y rango de fechas
    ===================================================== */
    public function getReporteGlobal(?int $userId = null, ?string $fechaInicio = null, ?string $fechaFin = null): array
    {
        $usuarios = [];
        if ($userId) {
            $usuarios[] = $this->userModel->findById($userId);
        } else {
            $usuarios = $this->userModel->getAll();
        }

        $reporte = [];
        foreach ($usuarios as $u) {
            $fichajes = $this->fichajeModel->getFichajes($u['id'], $fechaInicio, $fechaFin);
            $resumen = $this->fichajeModel->calcularResumen($fichajes);

            $reporte[$u['id']] = [
                'usuario' => $u['nombre'],
                'rol'     => $u['rol'] ?? $u['role_nombre'] ?? '',
                'resumen' => $resumen
            ];
        }

        return $reporte;
    }

    /* =====================================================
       Exportar reporte a CSV
       $reporte → array obtenido de getReporteGlobal()
       $rutaArchivo → ruta completa donde guardar CSV
    ===================================================== */
    public function exportarCSV(array $reporte, string $rutaArchivo): bool
    {
        $fp = fopen($rutaArchivo, 'w');
        if (!$fp) return false;

        // Cabecera
        fputcsv($fp, ['Usuario','Rol','Fecha','Horas Trabajadas','Horas Descanso']);

        foreach ($reporte as $data) {
            foreach ($data['resumen']['resumen_diario'] as $fecha => $d) {
                fputcsv($fp, [
                    $data['usuario'],
                    $data['rol'],
                    $fecha,
                    $d['horas_trabajadas'],
                    $d['horas_descanso']
                ]);
            }
        }

        fclose($fp);
        return true;
    }
}