<?php
namespace App\Services;

use App\Contracts\UsuarioRepository;
use Exception;
use DateTime;

class PagosService
{
    private UsuarioRepository $dbUsuarios;

    public function __construct(UsuarioRepository $dbUsuarios)
    {
        $this->dbUsuarios = $dbUsuarios;
    }

    public function pagarDeuda(string $codigoBiblioteca, float $montoPago, DateTime $fechaPago): array
    {
        $usuario = $this->dbUsuarios->buscarPorCodigoBiblioteca($codigoBiblioteca);

        if (!$usuario) {
            throw new Exception("Usuario no encontrado.");
        }

        if ($montoPago <= 0) {
            throw new Exception("El monto de pago debe ser mayor a cero.");
        }

        if ($montoPago > $usuario['deuda']) {
            throw new Exception("Error: El monto ingresado (S/ {$montoPago}) supera la deuda actual (S/ {$usuario['deuda']}).");
        }

        $nuevaDeuda = $usuario['deuda'] - $montoPago;
        $fechaFinPenalizacion = clone $fechaPago;
        // 3 semanas de penalización = 21 días
        $fechaFinPenalizacion->modify('+21 days');

        return [
            'exito' => true,
            'nueva_deuda' => $nuevaDeuda,
            'fecha_fin_penalizacion' => $fechaFinPenalizacion->format('Y-m-d H:i:s'),
            'mensaje' => "Deuda descontada. Penalización de 3 semanas activada."
        ];
    }
}