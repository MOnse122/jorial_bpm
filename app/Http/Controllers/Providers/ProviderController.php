<?php

namespace App\Http\Controllers\Providers;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use Illuminate\Http\Request;

class ProviderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $providers = Provider::all();
        return view('providers.index', compact('providers'));

   
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'plates' => 'required|string|min:6|max:10',
            'state' => 'required|in:ACTIVO,INACTIVO',
        ]);

        Provider::create([
            'name' => $request->name,
            'plates' => $request->plates,
            'state' => $request->state,
        ]);

        return redirect()->route('providers.index')->with('success', 'Proveedor creado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $requestedProvider = Provider::findOrFail($id);
        return view('providers.show', compact('requestedProvider'));
        
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'plates' => 'required|string|min:6|max:10|regex:/^[A-Z0-9-]+$/',
            'state' => 'required|in:ACTIVO,INACTIVO',
        ]);

        $provider = Provider::findOrFail($id);
        $provider->update([
            'name' => $request->name,
            'plates' => $request->plates,
            'state' => $request->state,
        ]);

        return redirect()->route('providers.index')->with('success', 'Proveedor actualizado corractamente.');
    }

    public function destroy(string $id)
    {
        $provider = Provider::findOrFail($id);
        $provider->delete();

        return redirect()->route('providers.index')->with('success', 'Proveedor eliminado correctamente.');
    }
}
