<?php
namespace App\Contracts;

interface UsuarioRepository
{
    public function buscarPorCodigoBiblioteca(string $codigoBiblioteca): ?array;
}