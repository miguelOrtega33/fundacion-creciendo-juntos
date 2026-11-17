<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="data:,">
    <title>Panel | Fundación Creciendo Juntos</title>
</head>
<body>
    <main>
        <h1>Panel de gestión</h1>
        <p>Sesión iniciada correctamente.</p>

        @can('viewAny', \App\Models\User::class)
            <p><a href="{{ route('users.index') }}">Administrar usuarios</a></p>
        @endcan

        @can('create', [\App\Models\User::class, \App\Enums\UserRole::Editor])
            <p><a href="{{ route('users.create') }}">Crear cuenta de usuario</a></p>
        @endcan

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit">Cerrar sesión</button>
        </form>
    </main>
</body>
</html>
