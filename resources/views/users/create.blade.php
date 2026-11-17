<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="data:,">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    <title>Crear cuenta | Fundación Creciendo Juntos</title>
</head>
<body>
    <main class="auth-layout">
        <section class="auth-intro" aria-labelledby="institution-title">
            <div class="brand">
                <span class="brand-mark" aria-hidden="true">CJ</span>
                <span>Fundación Creciendo Juntos</span>
            </div>

            <div class="intro-copy">
                <h1 id="institution-title">Sumamos personas para crecer juntos.</h1>
                <p>Crea una cuenta con el acceso necesario para su responsabilidad. Los permisos siempre se comprobarán en el servidor.</p>
            </div>

            <small>Administración segura de cuentas</small>
        </section>

        <section class="auth-panel" aria-labelledby="create-user-title">
            <div class="auth-card">
                <p class="eyebrow">Administración</p>
                <h2 id="create-user-title">Crear cuenta</h2>
                <p class="auth-description">Completa los datos y entrega la contraseña temporal por un medio seguro.</p>

                @if (session('status'))
                    <div class="alert alert-success" role="status">{{ session('status') }}</div>
                @endif

                @if ($errors->any())
                    <div class="alert" role="alert">Revisa los datos ingresados e intenta nuevamente.</div>
                @endif

                <form method="POST" action="{{ route('users.store') }}" novalidate>
                    @csrf

                    <div class="field">
                        <label for="name">Nombre</label>
                        <input id="name" name="name" type="text" value="{{ old('name') }}" autocomplete="name"
                            aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}"
                            @error('name') aria-describedby="name-error" @enderror required>
                        @error('name')<span id="name-error" class="field-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="field">
                        <label for="email">Correo electrónico</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" inputmode="email"
                            aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                            @error('email') aria-describedby="email-error" @enderror required>
                        @error('email')<span id="email-error" class="field-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="field">
                        <label for="role">Rol</label>
                        <select id="role" name="role" aria-invalid="{{ $errors->has('role') ? 'true' : 'false' }}"
                            @error('role') aria-describedby="role-error" @enderror required>
                            @foreach ($roles as $role)
                                <option value="{{ $role->value }}" @selected(old('role') === $role->value)>{{ ucfirst($role->value) }}</option>
                            @endforeach
                        </select>
                        @error('role')<span id="role-error" class="field-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="field">
                        <label for="password">Contraseña temporal</label>
                        <input id="password" name="password" type="password" autocomplete="new-password"
                            aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                            aria-describedby="{{ $errors->has('password') ? 'password-help password-error' : 'password-help' }}" required>
                        <span id="password-help" class="field-help">Usa entre 15 y 64 caracteres. No envíes la contraseña por correo sin protección.</span>
                        @error('password')<span id="password-error" class="field-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="field">
                        <label for="password_confirmation">Confirmar contraseña temporal</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password"
                            aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                            @error('password') aria-describedby="password-error" @enderror required>
                    </div>

                    <button class="submit-button" type="submit">Crear cuenta</button>
                </form>

                <div class="auth-actions">
                    <a class="text-link" href="{{ route('panel') }}">Volver al panel</a>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
