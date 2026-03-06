<?php

require_once __DIR__ . '/../../config/Database.php';

/** 
 * Lo que hace este modelo Fichaje.php
 * 
 * registrar($userId, $tipo) → Añade un fichaje del tipo indicado 
 *      ('entrada','salida','inicio_descanso','fin_descanso'), 
 *      validando que no se repita consecutivo.
 *
 * ultimoFichaje($userId) → Obtiene el último movimiento/fichaje de un usuario.
 *
 * getFichajes($userId, $fechaInicio, $fechaFin) → Trae todos los fichajes 
 *      de un usuario, opcionalmente filtrando por un rango de fechas.
 *
 * calcularHorasPorFecha($userId, $fecha) → Calcula horas trabajadas y descansos de un día concreto.
 *
 * resumenSemanal($userId, $fecha) → Devuelve un resumen diario y total de horas trabajadas
 *      y descansos de la semana correspondiente a la fecha indicada.
 *
 * resumenMensual($userId, $fecha) → Devuelve un resumen diario y total de horas trabajadas
 *      y descansos del mes correspondiente a la fecha indicada.
 *
 * calcularResumen($fichajes) → Método privado que recibe un array de fichajes y devuelve
 *      un resumen organizado por fecha con horas trabajadas y descansos.
 *
 * horasExtra($userId, $fecha) → Calcula las horas extraordinarias de un día, 
 *      considerando 8 horas como jornada estándar.
 *
 * getUserFichajesFiltrados($userId, $tipo=null, $fechaInicio=null, $fechaFin=null) → 
 *      (Opcional) Permite obtener fichajes filtrados por tipo y/o rango de fechas.
 *
 * Este modelo está preparado para:
 *      - Controlar fichajes de entrada, salida y descansos.
 *      - Evitar doble fichaje consecutivo.
 *      - Calcular horas trabajadas, horas de descanso y horas extra.
 *      - Generar resúmenes diarios, semanales y mensuales.
 *      - Proveer datos para dashboards gráficos y reportes.
 */

class Fichaje
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /* =====================================================
       Registrar un fichaje
       tipos: 'entrada','salida','inicio_descanso','fin_descanso'
    ===================================================== */
public function registrar(int $userId, string $tipo): bool{
    $tiposValidos = ['entrada','salida','inicio_descanso','fin_descanso'];

    if (!in_array($tipo, $tiposValidos)) {
        throw new Exception("Tipo de fichaje inválido");
    }

    $ultimo = $this->ultimoFichaje($userId);
    $ultimoTipo = $ultimo['tipo'] ?? 'ninguno';

    switch ($tipo) {

        case 'entrada':
            if ($ultimoTipo !== 'ninguno' && $ultimoTipo !== 'salida') {
                throw new Exception("Ya tienes una jornada iniciada.");
            }
            break;

        case 'inicio_descanso':
            if (!in_array($ultimoTipo, ['entrada','fin_descanso'])) {
                throw new Exception("No puedes iniciar descanso ahora.");
            }
            break;

        case 'fin_descanso':
            if ($ultimoTipo !== 'inicio_descanso') {
                throw new Exception("No estás en descanso.");
            }
            break;

        case 'salida':
            if (!in_array($ultimoTipo, ['entrada','fin_descanso'])) {
                throw new Exception("No puedes fichar salida ahora.");
            }
            break;
    }

    $sql = "INSERT INTO fichajes (user_id, tipo, fecha_hora) 
            VALUES (:user_id, :tipo, NOW())";

    $stmt = $this->db->prepare($sql);

    return $stmt->execute([
        ':user_id' => $userId,
        ':tipo'    => $tipo
    ]);
}

    /* =====================================================
       Obtener último fichaje de un usuario
    ===================================================== */
    public function ultimoFichaje(int $userId): ?array
    {
        $sql = "SELECT * 
                FROM fichajes 
                WHERE user_id = :user_id 
                ORDER BY fecha_hora DESC 
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);

        $fichaje = $stmt->fetch(PDO::FETCH_ASSOC);
        return $fichaje ?: null;
    }

    /* =====================================================
       Obtener todos los fichajes de un usuario (opcional filtro por fecha)
    ===================================================== */
public function getFichajes(int $userId, ?string $fechaInicio = null, ?string $fechaFin = null): array
{
    $sql = "SELECT * FROM fichajes WHERE user_id = :user_id";
    $params = [':user_id' => $userId];

    if ($fechaInicio) {
        // Forzamos el inicio del día
        $sql .= " AND fecha_hora >= :fechaInicio";
        $params[':fechaInicio'] = date('Y-m-d 00:00:00', strtotime($fechaInicio));
    }
    if ($fechaFin) {
        // Forzamos el final del día hasta el último segundo
        $sql .= " AND fecha_hora <= :fechaFin";
        $params[':fechaFin'] = date('Y-m-d 23:59:59', strtotime($fechaFin));
    }

    $sql .= " ORDER BY fecha_hora ASC";
    $stmt = $this->db->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
    public function contarFichajesHoy(): int
    {
        $hoy = date('Y-m-d');

        $sql = "SELECT COUNT(*) 
                FROM fichajes 
                WHERE DATE(fecha_hora) = :hoy 
                AND tipo = 'entrada'";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':hoy', $hoy);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    /* =====================================================
       Calcular horas trabajadas de un usuario por fecha
       Devuelve array: ['horas_trabajadas'=>x,'horas_descanso'=>y]
    ===================================================== */
    public function calcularHorasPorFecha(int $userId, string $fecha): array
    {
        $fichajes = $this->getFichajes($userId, $fecha, $fecha);

        $entrada = null;
        $salida = null;
        $descansoInicio = null;
        $descansoTotal = 0;

        foreach ($fichajes as $f) {
            switch ($f['tipo']) {
                case 'entrada':
                    $entrada = $f['fecha_hora'];
                    break;
                case 'inicio_descanso':
                    $descansoInicio = $f['fecha_hora'];
                    break;
                case 'fin_descanso':
                    if ($descansoInicio) {
                        $descansoTotal += (strtotime($f['fecha_hora']) - strtotime($descansoInicio)) / 3600;
                        $descansoInicio = null;
                    }
                    break;
                case 'salida':
                    $salida = $f['fecha_hora'];
                    break;
            }
        }

        $horasTrabajadas = 0;
        if ($entrada && $salida) {
            $horasTrabajadas = (strtotime($salida) - strtotime($entrada)) / 3600 - $descansoTotal;
        }

        return [
            'horas_trabajadas' => round(max($horasTrabajadas,0),2),
            'horas_descanso'   => round($descansoTotal,2)
        ];
    }
    /* =====================================================
       Resumen semanal de horas trabajadas
    ===================================================== */
    public function resumenSemanal(int $userId, string $fecha): array
    {
        // Calcula lunes de la semana
        $ts = strtotime($fecha);
        $diaSemana = date('N', $ts); // 1 (lunes) - 7 (domingo)
        $lunes = date('Y-m-d', strtotime("-" . ($diaSemana - 1) . " days", $ts));
        $domingo = date('Y-m-d', strtotime("+".(7-$diaSemana)." days", $ts));

        $fichajes = $this->getFichajes($userId, $lunes, $domingo);
        return $this->calcularResumen($fichajes);
    }

    /* =====================================================
       Resumen mensual de horas trabajadas
    ===================================================== */
    public function resumenMensual(int $userId, string $fecha): array
    {
        $inicio = date('Y-m-01', strtotime($fecha));
        $fin = date('Y-m-t', strtotime($fecha));

        $fichajes = $this->getFichajes($userId, $inicio, $fin);
        return $this->calcularResumen($fichajes);
    }

    /* =====================================================
       Calcular resumen genérico (diario, semanal o mensual)
    ===================================================== */


    /* =====================================================
       Horas extraordinarias
       Se calcula restando la jornada estándar (8h)
    ===================================================== */
    public function horasExtra(int $userId, string $fecha): float
    {
        $resumen = $this->calcularHorasPorFecha($userId, $fecha);
        $horasExtra = $resumen['horas_trabajadas'] - 8;
        return max(round($horasExtra,2),0);
    }
private function calcularResumen(array $fichajes): array
{
    if (empty($fichajes)) return ['total_horas_trabajadas' => 0];

    $totalSegundos = 0;
    $entradaActual = null;
    $inicioDescanso = null;
    $descontarDescanso = 0;

    foreach ($fichajes as $f) {
        $time = strtotime($f['fecha_hora']);
        $tipo = $f['tipo'];

        if ($tipo === 'entrada') {
            $entradaActual = $time;
            $descontarDescanso = 0;
        } 
        elseif ($tipo === 'inicio_descanso' && $entradaActual) {
            $inicioDescanso = $time;
        } 
        elseif ($tipo === 'fin_descanso' && $inicioDescanso) {
            $descontarDescanso += ($time - $inicioDescanso);
            $inicioDescanso = null;
        } 
        elseif ($tipo === 'salida' && $entradaActual) {
            $totalSegundos += ($time - $entradaActual) - $descontarDescanso;
            $entradaActual = null;
            $inicioDescanso = null;
        }
    }

    return ['total_horas_trabajadas' => round(max($totalSegundos / 3600, 0), 2)];
}
public function totalHistorico(int $userId): array
{
    // Traemos TODO sin filtros de fecha
    $sql = "SELECT tipo, fecha_hora FROM fichajes WHERE user_id = :user_id ORDER BY fecha_hora ASC";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([':user_id' => $userId]);
    $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // DEBUG: Si quieres ver si llegan datos, puedes hacer un: die(var_dump($res));
    return $this->calcularResumen($res);
}
/**
 * Método auxiliar para obtener todos los fichajes (sin filtros)
 * Si no se pasan fechas, el método getFichajes ya funciona, 
 * pero asegúrate de que sea consistente.
 */
}