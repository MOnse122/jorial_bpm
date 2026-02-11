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
        return response()->json(Provider::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'plates' => 'required|string|min:6|max:10',
            'state' => 'required|in:NORMAL,SEVERA,REDUCIDA',
        ]);

        $existingProvider = Provider::where('plates', $request->plates)
            ->orWhere(function ($query) use ($request) {
                $query->where('name', $request->name)
                      ->where('state', $request->state);
            })
            ->first();
        if ($existingProvider) {
            return response()->json(['message' => 'Ya existe este registro'], 409);
        }


         $provider = Provider::create([
            'name' => $request->name,
            'plates' => $request->plates,
            'state' => $request->state,
        ]);

        return response()->json($provider, 201);

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $requestedProvider = Provider::findOrFail($id);
        return response()->json($requestedProvider);
        
        
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'plates' => 'required|string|min:6|max:10',
            'state' => 'required|in:NORMAL,SEVERA,REDUCIDA',
        ]);

       return response()->json([
            'message' => 'Proveedor actualizado correctamente.',
            'response' => Provider::findOrFail($id)->update([
                'name' => $request->name,
                'plates' => $request->plates,
                'state' => $request->state,
            ]), 200
        ]); 

    }

    public function destroy(string $id)
    {
        $provider = Provider::findOrFail($id);
        $provider->delete();

        return response()->json(['message' => 'Proveedor eliminado correctamente.']);    }
}
