<?php

namespace App\Http\Controllers\Providers;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use App\Models\PlatesModel;
use Illuminate\Http\Request;

class ProviderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(
            Provider::with('plates')->get()
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'   => 'required|string|max:255',
            'state'  => 'required|in:NORMAL,SEVERA,REDUCIDA',
            'plates' => 'nullable|array',
            'plates.*' => 'string|min:6|max:10',
        ]);

        // Validar que no exista proveedor igual
        $existingProvider = Provider::where('name', $request->name)
            ->where('state', $request->state)
            ->first();

        if ($existingProvider) {
            return response()->json([
                'message' => 'Ya existe este proveedor'
            ], 409);
        }

        // Crear proveedor
        $provider = Provider::create([
            'name'  => $request->name,
            'state' => $request->state,
        ]);

        // Crear placas si vienen
        if ($request->has('plates')) {
            foreach ($request->plates as $plate) {
                PlatesModel::create([
                    'plate_number' => strtoupper($plate),
                    'id_provider'  => $provider->id_provider,
                ]);
            }
        }

        return response()->json(
            Provider::with('plates')->find($provider->id_provider),
            201
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return response()->json(
            Provider::with('plates')->findOrFail($id)
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name'   => 'required|string|max:255',
            'state'  => 'required|in:NORMAL,SEVERA,REDUCIDA',
            'plates' => 'nullable|array',
            'plates.*' => 'string|min:6|max:10',
        ]);

        $provider = Provider::findOrFail($id);

        $provider->update([
            'name'  => $request->name,
            'state' => $request->state,
        ]);

        // Si mandan placas, reemplazarlas
        if ($request->has('plates')) {

            // eliminar placas anteriores
            $provider->plates()->delete();

            // crear nuevas
            foreach ($request->plates as $plate) {
                PlatesModel::create([
                    'plate_number' => strtoupper($plate),
                    'id_provider'  => $provider->id_provider,
                ]);
            }
        }

        return response()->json([
            'message' => 'Proveedor actualizado correctamente.',
            'provider' => Provider::with('plates')->find($id)
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $provider = Provider::findOrFail($id);

        // eliminar placas relacionadas
        $provider->plates()->delete();

        $provider->delete();

        return response()->json([
            'message' => 'Proveedor eliminado correctamente.'
        ]);
    }
}
