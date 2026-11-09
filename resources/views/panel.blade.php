<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel | Fundación Creciendo Juntos</title>
</head>
<body>
    <main>
        <h1>Panel de gestión</h1>
        <p>Sesión iniciada correctamente.</p>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit">Cerrar sesión</button>
        </form>
    </main>
</body>
</html>
