<?php

namespace App\Http\Requests;

use App\Http\Requests\Contracts\Request;

class CadastrarTransferenciaRequest extends RequestValidator implements Request
{

    public function rules(): array
    {
        return [
            'valor'        => 'required|numeric|min:0.01',
            'pagador'      => 'required|exists:carteiras,id|different:beneficiario',
            'beneficiario' => 'required|exists:carteiras,id'
        ];
    }

    public function messages(): array
    {
        return [
            'valor.required'        => trans('validation.required', ['attribute' => 'valor']),
            'valor.min'             => trans('validation.min', ['attribute' => 'valor', 'min' => '0.01']),
            'pagador.required'      => trans('validation.required', ['attribute' => 'pagador']),
            'beneficiario.required' => trans('validation.required', ['attribute' => 'beneficiario']),
            'pagador.exists'        => trans('validation.exists'),
            'beneficiario.exists'   => trans('validation.exists'),
            'pagador.different'     => trans('validation.different',
                                             ['attribute' => 'pagador', 'other' => 'beneficiario'])
        ];
    }
}