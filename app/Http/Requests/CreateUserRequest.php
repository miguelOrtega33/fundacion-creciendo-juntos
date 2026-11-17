<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use App\Models\User;
use App\Support\UserRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

final class CreateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $actor = $this->user();
        $role = UserRole::tryFrom($this->string('role')->toString());

        return $actor !== null && Gate::forUser($actor)->allows(
            'create',
            [User::class, $role ?? UserRole::Editor],
        );
    }

    public function rules(): array
    {
        $rules = UserRules::create();
        $rules['password'][] = 'confirmed';
        $rules['role'] = ['required', Rule::enum(UserRole::class)];

        return $rules;
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Ingresa el nombre de la persona.',
            'name.max' => 'El nombre no puede superar los 255 caracteres.',
            'email.required' => 'Ingresa un correo electrónico.',
            'email.email' => 'Ingresa un correo electrónico válido.',
            'email.max' => 'El correo no puede superar los 255 caracteres.',
            'email.unique' => 'El correo ya está registrado.',
            'password.required' => 'Ingresa una contraseña temporal.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'password.min' => 'La contraseña debe tener al menos 15 caracteres.',
            'password.max' => 'La contraseña no puede superar los 64 caracteres.',
            'role.required' => 'Selecciona un rol.',
            'role.enum' => 'Selecciona un rol permitido.',
        ];
    }
}
