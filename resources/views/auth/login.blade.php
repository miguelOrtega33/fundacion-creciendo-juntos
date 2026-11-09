<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="data:,">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    <title>Iniciar sesión | Fundación Creciendo Juntos</title>
</head>
<body>
    <main class="auth-layout">
        <section class="auth-intro" aria-labelledby="institution-title">
            <div class="brand">
                <span class="brand-mark" aria-hidden="true">CJ</span>
                <span>Fundación Creciendo Juntos</span>
            </div>

            <div class="intro-copy">
                <h1 id="institution-title">Historias que fortalecen comunidades.</h1>
                <p>Gestiona contenidos institucionales desde un espacio seguro, claro y preparado para colaborar.</p>
            </div>

            <small>Plataforma editorial institucional</small>
        </section>

        <section class="auth-panel" aria-labelledby="login-title">
            <div class="auth-card">
                <p class="eyebrow">Acceso seguro</p>
                <h2 id="login-title">Iniciar sesión</h2>
                <p class="auth-description">Ingresa tus credenciales para acceder al panel de gestión.</p>

                @if ($errors->any())
                    <div class="alert" role="alert">
                        Revisa los datos ingresados e intenta nuevamente.
                    </div>
                @endif

                <form method="POST" action="{{ route('login.store') }}" novalidate>
                    @csrf

                    <div class="field">
                        <label for="email">Correo electrónico</label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email') }}"
                            autocomplete="email"
                            inputmode="email"
                            aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                            @error('email') aria-describedby="email-error" @enderror
                            required
                        >
                        @error('email')
                            <span id="email-error" class="field-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="field">
                        <label for="password">Contraseña</label>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            autocomplete="current-password"
                            aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                            @error('password') aria-describedby="password-error" @enderror
                            required
                        >
                        @error('password')
                            <span id="password-error" class="field-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-options">
                        <label class="check-label" for="remember">
                            <input id="remember" name="remember" type="checkbox" value="1" @checked(old('remember'))>
                            <span>Recordarme en este dispositivo</span>
                        </label>
                    </div>

                    <button class="submit-button" type="submit">Ingresar</button>
                </form>
            </div>
        </section>
    </main>
</body>
</html>
