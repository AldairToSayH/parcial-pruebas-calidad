<?php
namespace App\Services;

use App\Contracts\UsuarioRepository;
use App\Contracts\LibroRepository;
use Exception;
use DateTime;

class PrestamoService
{
    private UsuarioRepository $dbUsuarios;
    private LibroRepository $dbLibros;

    public function __construct(UsuarioRepository $dbUsuarios, LibroRepository $dbLibros)
    {
        $this->dbUsuarios = $dbUsuarios;
        $this->dbLibros = $dbLibros;
    }

    public function registrarPrestamo(string $codigoBiblioteca, string $codigoLibro, DateTime $fechaPrestamo): array
    {
        if (!$this->dbLibros->estaDisponible($codigoLibro)) {
            throw new Exception("El libro solicitado se encuentra prestado actualmente.");
        }

        $usuario = $this->dbUsuarios->buscarPorCodigoBiblioteca($codigoBiblioteca);

        if (!$usuario) {
            throw new Exception("Usuario no encontrado en el sistema.");
        }

        if ($usuario['moroso']) {
            throw new Exception("Préstamo denegado. El usuario mantiene deudas.");
        }

        if (isset($usuario['fecha_fin_penalizacion']) && $usuario['fecha_fin_penalizacion'] !== null) {
            $finPenalizacion = new DateTime($usuario['fecha_fin_penalizacion']);
            
            if ($fechaPrestamo < $finPenalizacion) {
                $intervalo = $fechaPrestamo->diff($finPenalizacion);
                $horasTotales = ($intervalo->days * 24) + $intervalo->h;
                $minutos = $intervalo->i;
                $segundos = $intervalo->s;
                
                throw new Exception("USTED AUN NO PUEDE RECIBIR UN LIBRO LE QUEDAN {$horasTotales} HORAS, {$minutos} MINUTOS, {$segundos} SEGUNDOS DE PENALIZACION.");
            }
        }

        $diasPrestamo = ($usuario['rol'] === 'Estudiante') ? 7 : 14;
        $fechaDevolucion = clone $fechaPrestamo;
        $fechaDevolucion->modify("+$diasPrestamo days");

        return [
            'exito' => true,
            'mensaje' => 'Préstamo registrado correctamente.',
            'datos_prestamo' => [
                'codigo_biblioteca' => $usuario['codigo_biblioteca'],
                'nombres' => $usuario['nombres'],
                'codigo_libro' => $codigoLibro,
                'fecha_devolucion' => $fechaDevolucion->format('Y-m-d')
            ]
        ];
    }
}