<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="data:,">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    <link rel="stylesheet" href="{{ asset('css/users.css') }}">
    <title>Usuarios | Fundación Creciendo Juntos</title>
</head>
<body>
    <main class="users-layout">
        <header class="users-header">
            <a class="brand users-brand" href="{{ route('panel') }}">
                <span class="brand-mark" aria-hidden="true">CJ</span>
                <span>Fundación Creciendo Juntos</span>
            </a>

            <a class="text-link" href="{{ route('panel') }}">Volver al panel</a>
        </header>

        <section class="users-card" aria-labelledby="users-title">
            <div class="users-heading">
                <div>
                    <p class="eyebrow">Administración</p>
                    <h1 id="users-title">Usuarios</h1>
                    <p>Consulta las cuentas que tienes autorización para gestionar.</p>
                </div>

                @can('create', [\App\Models\User::class, \App\Enums\UserRole::Editor])
                    <a class="primary-link" href="{{ route('users.create') }}">Crear cuenta</a>
                @endcan
            </div>

            @if ($users->isEmpty())
                <div class="empty-state" role="status">
                    <h2>No hay usuarios para mostrar</h2>
                    <p>Actualmente no tienes cuentas disponibles dentro de tu alcance de administración.</p>
                </div>
            @else
                <div class="table-region" tabindex="0" aria-label="Usuarios autorizados">
                    <table>
                        <caption>Usuarios disponibles según tus permisos</caption>
                        <thead>
                            <tr>
                                <th scope="col">Nombre</th>
                                <th scope="col">Correo electrónico</th>
                                <th scope="col">Rol</th>
                                <th scope="col">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                                <tr>
                                    <td data-label="Nombre">{{ $user->name }}</td>
                                    <td data-label="Correo electrónico">{{ $user->email }}</td>
                                    <td data-label="Rol">{{ ucfirst($user->role->value) }}</td>
                                    <td data-label="Estado">
                                        <span class="status-badge {{ $user->disabled_at ? 'status-disabled' : 'status-active' }}">
                                            {{ $user->disabled_at ? 'Desactivada' : 'Activa' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($users->hasPages())
                    <nav class="pagination" aria-label="Paginación de usuarios">
                        @if ($users->onFirstPage())
                            <span aria-disabled="true">Anterior</span>
                        @else
                            <a href="{{ $users->previousPageUrl() }}" rel="prev">Anterior</a>
                        @endif
                        <span>Página {{ $users->currentPage() }} de {{ $users->lastPage() }}</span>
                        @if ($users->hasMorePages())
                            <a href="{{ $users->nextPageUrl() }}" rel="next">Siguiente</a>
                        @else
                            <span aria-disabled="true">Siguiente</span>
                        @endif
                    </nav>
                @endif
            @endif
        </section>
    </main>
</body>
</html>
