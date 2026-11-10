<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="data:,">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    <title>Recuperar contraseña | Fundación Creciendo Juntos</title>
</head>
<body>
    <main class="auth-layout">
        <section class="auth-intro" aria-labelledby="institution-title">
            <div class="brand">
                <span class="brand-mark" aria-hidden="true">CJ</span>
                <span>Fundación Creciendo Juntos</span>
            </div>

            <div class="intro-copy">
                <h1 id="institution-title">Recupera tu acceso de forma segura.</h1>
                <p>Te enviaremos instrucciones si encontramos una cuenta asociada al correo ingresado.</p>
            </div>

            <small>Plataforma editorial institucional</small>
        </section>

        <section class="auth-panel" aria-labelledby="recovery-title">
            <div class="auth-card">
                <p class="eyebrow">Acceso seguro</p>
                <h2 id="recovery-title">Recuperar contraseña</h2>
                <p class="auth-description">Ingresa el correo de tu cuenta para solicitar un enlace de recuperación.</p>

                @if (session('status'))
                    <div class="alert alert-success" role="status">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert" role="alert">
                        Revisa el correo ingresado e intenta nuevamente.
                    </div>
                @endif

                <form method="POST" action="{{ route('password.email') }}" novalidate>
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

                    <button class="submit-button" type="submit">Enviar enlace</button>

                    <div class="auth-actions">
                        <a class="text-link" href="{{ route('login') }}">Volver al inicio de sesión</a>
                    </div>
                </form>
            </div>
        </section>
    </main>
</body>
</html>
