<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión | Fundación Creciendo Juntos</title>
</head>
<body>
    <main>
        <h1>Iniciar sesión</h1>
        <p>Accede al panel de gestión de Fundación Creciendo Juntos.</p>

        <form method="POST" action="{{ url('/login') }}">
            @csrf

            <div>
                <label for="email">Correo electrónico</label>
                <input id="email" name="email" type="email" autocomplete="email" required>
            </div>

            <div>
                <label for="password">Contraseña</label>
                <input id="password" name="password" type="password" autocomplete="current-password" required>
            </div>

            <label for="remember">
                <input id="remember" name="remember" type="checkbox" value="1">
                Recordarme en este dispositivo
            </label>

            <button type="submit">Ingresar</button>
        </form>
    </main>
</body>
</html>
