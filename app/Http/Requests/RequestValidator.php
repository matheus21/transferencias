<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;

abstract class RequestValidator extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function failedValidation(Validator $validator): void
    {
        $validationErrors = ['mensagem' => trans('validation.invalid'), "erros" => $validator->errors()];

        throw new HttpResponseException(response()->json($validationErrors, Response::HTTP_UNPROCESSABLE_ENTITY));
    }
}
