<?php

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transferencia extends Model
{
    use HasFactory;

    protected $table = 'transferencias';
    protected $fillable = [
        'status',
        'valor',
        'carteira_pagador_id',
        'carteira_beneficiario_id',
        'notificacao_enviada'
    ];
}