<?php
namespace Tests\Unit;
use App\Contracts\UsuarioRepository;
use App\Services\PagosService;
use PHPUnit\Framework\TestCase;
use Mockery;
use Exception;
use DateTime;
class PagosServiceTest extends TestCase
{
    protected function tearDown(): void { Mockery::close(); parent::tearDown(); }

    public function test_rechaza_pago_si_monto_supera_deuda_actual(): void
    {
        $mockDB = Mockery::mock(UsuarioRepository::class);
        $mockDB->shouldReceive('buscarPorCodigoBiblioteca')->once()->with('BIB-888')
               ->andReturn(['deuda' => 10.00]);

        $servicio = new PagosService($mockDB);
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("Error: El monto ingresado (S/ 10000000) supera la deuda actual (S/ 10).");
        
        $servicio->pagarDeuda('BIB-888', 10000000.00, new DateTime());
    }

    public function test_procesa_pago_descuenta_deuda_y_aplica_penalizacion_de_3_semanas(): void
    {
        $mockDB = Mockery::mock(UsuarioRepository::class);
        $mockDB->shouldReceive('buscarPorCodigoBiblioteca')->once()->with('BIB-888')
               ->andReturn(['deuda' => 10.00]);

        $servicio = new PagosService($mockDB);
        $fechaHoy = new DateTime('2026-05-07 10:00:00');
        $resultado = $servicio->pagarDeuda('BIB-888', 10.00, $fechaHoy);

        $this->assertTrue($resultado['exito']);
        $this->assertEquals(0.00, $resultado['nueva_deuda']);
        $this->assertEquals('2026-05-28 10:00:00', $resultado['fecha_fin_penalizacion']);
    }
}