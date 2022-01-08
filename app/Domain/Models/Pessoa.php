<?php

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pessoa extends Model
{
    use HasFactory;

    protected $table = 'pessoas';
    protected $fillable = ['nome', 'email', 'cpf_cnpj', 'senha', 'tipo_pessoa'];
}