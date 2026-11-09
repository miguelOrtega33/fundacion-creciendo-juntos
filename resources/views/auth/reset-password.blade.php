<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="data:,">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    <title>Nueva contraseña | Fundación Creciendo Juntos</title>
</head>
<body>
    <main class="auth-layout">
        <section class="auth-intro" aria-labelledby="institution-title">
            <div class="brand">
                <span class="brand-mark" aria-hidden="true">CJ</span>
                <span>Fundación Creciendo Juntos</span>
            </div>

            <div class="intro-copy">
                <h1 id="institution-title">Protege nuevamente tu cuenta.</h1>
                <p>Define una contraseña segura para recuperar el acceso a la plataforma.</p>
            </div>

            <small>Plataforma editorial institucional</small>
        </section>

        <section class="auth-panel" aria-labelledby="reset-title">
            <div class="auth-card">
                <p class="eyebrow">Acceso seguro</p>
                <h2 id="reset-title">Crear nueva contraseña</h2>
                <p class="auth-description">Completa los datos para proteger nuevamente tu cuenta.</p>

                @if ($errors->any())
                    <div class="alert" role="alert">
                        Revisa los datos o solicita un nuevo enlace de recuperación.
                    </div>
                @endif

                <form method="POST" action="{{ route('password.update') }}" novalidate>
                    @csrf
                    <input name="token" type="hidden" value="{{ $token }}">

                    <div class="field">
                        <label for="email">Correo electrónico</label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email', $email) }}"
                            autocomplete="email"
                            aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                            @error('email') aria-describedby="email-error" @enderror
                            required
                        >
                        @error('email')
                            <span id="email-error" class="field-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="field">
                        <label for="password">Nueva contraseña</label>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            autocomplete="new-password"
                            aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                            @error('password') aria-describedby="password-error" @enderror
                            required
                        >
                        @error('password')
                            <span id="password-error" class="field-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="field">
                        <label for="password_confirmation">Confirmar nueva contraseña</label>
                        <input
                            id="password_confirmation"
                            name="password_confirmation"
                            type="password"
                            autocomplete="new-password"
                            aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                            @error('password') aria-describedby="password-error" @enderror
                            required
                        >
                    </div>

                    <button class="submit-button" type="submit">Guardar contraseña</button>

                    <div class="auth-actions">
                        <a class="text-link" href="{{ route('password.request') }}">Solicitar un nuevo enlace</a>
                    </div>
                </form>
            </div>
        </section>
    </main>
</body>
</html>
