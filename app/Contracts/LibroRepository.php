<?php
namespace App\Contracts;

interface LibroRepository
{
    public function estaDisponible(string $codigoLibro): bool;
}