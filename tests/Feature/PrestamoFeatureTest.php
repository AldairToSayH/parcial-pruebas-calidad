<?php
namespace Tests\Feature;

use Tests\TestCase;
use App\Contracts\UsuarioRepository;
use App\Contracts\LibroRepository;
use App\Services\PrestamoService;
use Mockery\MockInterface;
use DateTime;

class PrestamoFeatureTest extends TestCase
{
    public function test_registra_prestamo_usando_multiples_mocks_del_contenedor_laravel(): void
    {
        $this->mock(UsuarioRepository::class, function (MockInterface $mock) {
            $mock->shouldReceive('buscarPorCodigoBiblioteca')->once()->andReturn([
                     'codigo_biblioteca' => 'BIB-123',
                     'nombres' => 'Juan Aldair',
                     'rol' => 'Estudiante',
                     'moroso' => false,
                     'fecha_fin_penalizacion' => null
                 ]);
        });

        $this->mock(LibroRepository::class, function (MockInterface $mock) {
            $mock->shouldReceive('estaDisponible')->once()->andReturn(true);
        });

        $servicio = app(PrestamoService::class);
        $resultado = $servicio->registrarPrestamo('BIB-123', 'LIB-002', new DateTime('2026-05-07'));

        $this->assertTrue($resultado['exito']);
    }
}