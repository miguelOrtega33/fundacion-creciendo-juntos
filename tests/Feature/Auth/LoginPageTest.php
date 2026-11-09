<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;

final class LoginPageTest extends TestCase
{
    public function test_login_page_is_available(): void
    {
        $response = $this->get('/login');

        $response
            ->assertOk()
            ->assertViewIs('auth.login')
            ->assertSee('Iniciar sesión')
            ->assertSee('Correo electrónico')
            ->assertSee('Contraseña')
            ->assertSee('Recordarme en este dispositivo')
            ->assertSee('Ingresar');
    }
}
