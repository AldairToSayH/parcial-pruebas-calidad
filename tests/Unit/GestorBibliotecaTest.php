<?php

namespace Tests\Unit;

use App\Services\GestorBiblioteca;
use PHPUnit\Framework\TestCase;
use InvalidArgumentException;

class GestorBibliotecaTest extends TestCase
{
    public function test_calcula_multa_exacta_por_retraso(): void
    {
        $gestor = new GestorBiblioteca();
        $multaGenerada = $gestor->calcularMulta(3);
        $this->assertEquals(6.0, $multaGenerada);
    }

    public function test_no_permite_dias_de_retraso_negativos(): void
    {
        $gestor = new GestorBiblioteca();
        
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Los días de retraso no pueden ser negativos.");
        
        $gestor->calcularMulta(-2);
    }
}