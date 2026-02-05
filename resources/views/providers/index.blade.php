<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'Jorial') }}</title>
    </head>
    <body>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

        <h1>Proveedores</h1>

        {{-- Mensaje de éxito --}}
        @if(session('success'))
            <p style="color: green;">
                {{ session('success') }}
            </p>
        @endif

        {{-- ERRORES --}}
        @if ($errors->any())
            <ul style="color:red">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        @endif

        {{-- FORMULARIO CREAR --}}
        <h2>Nuevo proveedor</h2>

        <form action="{{ route('providers.store') }}" method="POST">
            @csrf

            <div>
                <label>Nombre</label><br>
                <input type="text" name="name" value="{{ old('name') }}">
            </div>

            <div>
                <label>Placas</label><br>
                <input type="text" name="plates" value="{{ old('plates') }}">
            </div>

            <div>
                <label>Estado de proveedor</label><br>
                <select name="state">
                    <option value="">-- Selecciona --</option>
                    <option value="NORMAL" {{ old('state') == 'NORMAL' ? 'selected' : '' }}>NORMAL</option>
                    <option value="REDUCIDO" {{ old('state') == 'REDUCIDO' ? 'selected' : '' }}>REDUCIDO</option>
                    <option value="SEVERA" {{ old('state') == 'SEVERA' ? 'selected' : '' }}>SEVERA</option>

                </select>
            </div>

            <br>
            <button type="submit">Guardar</button>
        </form>

        <hr>

        {{-- LISTADO --}}
        <h2>Listado de proveedores</h2>

        <table border="1" cellpadding="5">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Placas</th>
                    <th>Estado de proveedor</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($providers as $provider)
                    <tr>
                        <td>{{ $provider->id_provider ?? $provider->id }}</td>
                        <td>{{ $provider->name }}</td>
                        <td>{{ $provider->plates }}</td>
                        <td>{{ $provider->state }}</td>
                        <td>
                            {{-- ELIMINAR --}}
                            <form action="{{ route('providers.destroy', $provider->id_provider ?? $provider->id) }}"
                                method="POST"
                                style="display:inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    onclick="return confirm('¿Eliminar proveedor?')">
                                    Eliminar
                                </button>

                            </form>
                                                <!-- Botón para activar el modal -->
                            <button type="button" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#confirmUpdateModal{{ $provider->id_provider ?? $provider->id }}">
                                Actualizar
                            </button>

                            <!-- Estructura del Modal (Bootstrap 5) -->
                            <div class="modal fade" 
                                id="confirmUpdateModal{{ $provider->id_provider ?? $provider->id }}" 
                                tabindex="-1" 
                                aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Confirmar Actualización</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            ¿Está seguro de que desea actualizar los datos del proveedor <strong>{{ $provider->name }}</strong>?
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                            
                                            <form action="{{ route('providers.update', $provider->id_provider ?? $provider->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div>
                                                    <label>Nombre</label>
                                                    <input type="text" name="name" value="{{ $provider->name }}">
                                                </div>
                                                <div>
                                                    <label>Placas</label>
                                                    <input type="text" name="plates" value="{{ $provider->plates }}">
                                                </div>
                                                <div>
                                                    <label>Estado del proveedor</label>
                                                    <select name="state">
                                                        <option value="NORMAL" {{ $provider->state=='NORMAL'?'selected':'' }}>NORMAL</option>
                                                        
                                                        <option value="REDUCIDA" {{ $provider->state=='REDUCIDA'?'selected':'' }}>REDUCIDA</option>

                                                        <option value="SEVERA" {{ $provider->state=='SEVERA'?'selected':'' }}>SEVERA</option>
                                                    </select>
                                                </div>
                                                <button type="submit" class="btn btn-primary">Actualizar proveedor</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            

                        </td>
                        
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">No hay proveedores</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

    </body>
</html>
