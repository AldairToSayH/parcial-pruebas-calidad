<?php

namespace Tests\Unit;

use App\Contracts\UsuarioRepository;
use App\Contracts\LibroRepository;
use App\Services\PrestamoService;
use PHPUnit\Framework\TestCase;
use Mockery;
use Exception;
use DateTime;

class PrestamoServiceTest extends TestCase
{
    private $mockDBUsuarios;
    private $mockDBLibros;
    private $servicio;

    protected function setUp(): void
    {
        parent::setUp();
        // Arrange: Configuración global de Mocks para cada prueba
        $this->mockDBUsuarios = Mockery::mock(UsuarioRepository::class);
        $this->mockDBLibros = Mockery::mock(LibroRepository::class);
        $this->servicio = new PrestamoService($this->mockDBUsuarios, $this->mockDBLibros);
    }

    protected function tearDown(): void 
    { 
        Mockery::close(); 
        parent::tearDown(); 
    }

    /**
     * Prueba 3.4: Disponibilidad de stock
     */
    public function test_rechaza_prestamo_si_libro_no_esta_disponible(): void
    {
        $this->mockDBLibros->shouldReceive('estaDisponible')->once()->with('LIB-001')->andReturn(false);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("El libro solicitado se encuentra prestado actualmente.");
        
        $this->servicio->registrarPrestamo('BIB-000', 'LIB-001', new DateTime());
    }

    /**
     * Prueba 3.5: Bloqueo de usuario moroso
     * Este es el bloque que faltaba en tu archivo.
     */
    public function test_rechaza_prestamo_si_usuario_es_moroso(): void 
    {
        // El libro está físicamente en la biblioteca
        $this->mockDBLibros->shouldReceive('estaDisponible')->once()->andReturn(true);
        
        // El usuario existe pero tiene el estado moroso en MySQL (simulado)
        $this->mockDBUsuarios->shouldReceive('buscarPorCodigoBiblioteca')
               ->once()
               ->andReturn(['rol' => 'Docente', 'moroso' => true]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Préstamo denegado. El usuario mantiene deudas.");
        
        $this->servicio->registrarPrestamo('BIB-999M', 'LIB-001', new DateTime());
    }

    /**
     * Prueba 3.6: Penalización temporal (Horas/Minutos/Segundos)
     */
    public function test_rechaza_prestamo_por_penalizacion_activa_mostrando_tiempo_restante_exacto(): void
    {
        $this->mockDBLibros->shouldReceive('estaDisponible')->once()->with('LIB-001')->andReturn(true);
        $this->mockDBUsuarios->shouldReceive('buscarPorCodigoBiblioteca')->once()->with('BIB-777')
               ->andReturn([
                   'moroso' => false,
                   'rol' => 'Estudiante',
                   'fecha_fin_penalizacion' => '2026-05-10 15:30:15'
               ]);

        $fechaIntento = new DateTime('2026-05-08 10:00:00');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage("USTED AUN NO PUEDE RECIBIR UN LIBRO LE QUEDAN 53 HORAS, 30 MINUTOS, 15 SEGUNDOS DE PENALIZACION.");
        
        $this->servicio->registrarPrestamo('BIB-777', 'LIB-001', $fechaIntento);
    }
}