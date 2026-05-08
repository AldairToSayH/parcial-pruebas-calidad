<?php

namespace App\Services;

use InvalidArgumentException;

class GestorBiblioteca
{
    public function calcularMulta(int $diasRetraso): float
    {
        if ($diasRetraso < 0) {
            throw new InvalidArgumentException("Los días de retraso no pueden ser negativos.");
        }

        return round($diasRetraso * 2.00, 2);
    }
}