<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;

final class ForgotPasswordPageTest extends TestCase
{
    public function test_recovery_page_is_available(): void
    {
        $this->get('/recuperar-clave')
            ->assertOk()
            ->assertViewIs('auth.forgot-password')
            ->assertSee('Recuperar contraseña')
            ->assertSee('Correo electrónico')
            ->assertSee('Enviar enlace')
            ->assertSee('Volver al inicio de sesión')
            ->assertSee(route('password.email'), false);
    }
}
