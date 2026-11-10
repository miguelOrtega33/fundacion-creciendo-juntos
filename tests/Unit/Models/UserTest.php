<?php

namespace Tests\Unit\Models;

use App\Models\User;
use App\Support\UserRules;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class UserTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('invalidUserDataProvider')]
    public function test_rejects_invalid_user_data(array $data, string $field): void
    {
        $validator = Validator::make($data, UserRules::create());

        self::assertTrue($validator->fails());
        self::assertTrue($validator->errors()->has($field));
    }

    public function test_accepts_a_valid_user(): void
    {
        $validator = Validator::make($this->validUserData(), UserRules::create());

        self::assertTrue($validator->passes());
    }

    #[DataProvider('validPasswordBoundaries')]
    public function test_accepts_password_boundaries(string $password): void
    {
        $validator = Validator::make(
            array_replace($this->validUserData(), ['password' => $password]),
            UserRules::create(),
        );

        self::assertTrue($validator->passes());
    }

    public function test_hashes_password_when_user_is_created(): void
    {
        $user = new User($this->validUserData());

        $user->save();

        self::assertNotSame('ClaveSegura123!', $user->password);
        self::assertTrue(Hash::check('ClaveSegura123!', $user->password));
        self::assertSame('argon2id', password_get_info($user->password)['algoName']);
    }

    public function test_rejects_a_duplicate_email(): void
    {
        User::create($this->validUserData());

        $validator = Validator::make($this->validUserData(), UserRules::create());

        self::assertTrue($validator->fails());
        self::assertTrue($validator->errors()->has('email'));
    }

    public static function invalidUserDataProvider(): array
    {
        $valid = [
            'name' => 'María Pérez',
            'email' => 'maria@example.test',
            'password' => 'ClaveSegura123!',
        ];

        return [
            'correo vacío' => [array_replace($valid, ['email' => '']), 'email'],
            'correo inválido' => [array_replace($valid, ['email' => 'no-es-correo']), 'email'],
            'contraseña vacía' => [array_replace($valid, ['password' => '']), 'password'],
            'contraseña de 14 caracteres' => [array_replace($valid, ['password' => '12345678901234']), 'password'],
            'contraseña de 65 emojis' => [array_replace($valid, ['password' => str_repeat('😀', 65)]), 'password'],
        ];
    }

    /**
     * @return array<string, array{string}>
     */
    public static function validPasswordBoundaries(): array
    {
        return [
            '15 caracteres' => [str_repeat('a', 15)],
            '64 emojis' => [str_repeat('😀', 64)],
        ];
    }

    private function validUserData(): array
    {
        return [
            'name' => 'María Pérez',
            'email' => 'maria@example.test',
            'password' => 'ClaveSegura123!',
        ];
    }
}
