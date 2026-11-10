<?php

namespace Tests\Unit\Http\Requests;

use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class LoginRequestTest extends TestCase
{
    #[DataProvider('invalidLoginDataProvider')]
    public function test_rejects_invalid_login_data(array $data, string $field): void
    {
        $validator = Validator::make($data, (new LoginRequest)->rules());

        self::assertTrue($validator->fails());
        self::assertTrue($validator->errors()->has($field));
    }

    public function test_accepts_valid_login_data(): void
    {
        $validator = Validator::make([
            'email' => 'maria@example.test',
            'password' => 'ClaveSegura123!',
            'remember' => true,
        ], (new LoginRequest)->rules());

        self::assertTrue($validator->passes());
    }

    public static function invalidLoginDataProvider(): array
    {
        $valid = [
            'email' => 'maria@example.test',
            'password' => 'ClaveSegura123!',
        ];

        return [
            'correo vacío' => [array_replace($valid, ['email' => '']), 'email'],
            'correo inválido' => [array_replace($valid, ['email' => 'no-es-correo']), 'email'],
            'contraseña vacía' => [array_replace($valid, ['password' => '']), 'password'],
            'contraseña demasiado corta' => [array_replace($valid, ['password' => 'corta']), 'password'],
            'recordarme inválido' => [array_replace($valid, ['remember' => 'quizás']), 'remember'],
        ];
    }
}
