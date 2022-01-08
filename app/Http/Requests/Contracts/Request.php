<?php

namespace App\Http\Requests\Contracts;

interface Request
{
    public function rules(): array;

    public function messages(): array;
}