<?php

namespace App\Http\Controllers;

use App\Models\CheckBpm;
use Illuminate\Http\Request;
use Inertia\Inertia;



class TestController extends Controller

{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Views/Test');
        
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_purchase_order' => 'required|exists:purchase_order,id_purchase_order',
            'users_id' => 'required|exists:users,users_id',
            'id_evaluation' => 'required|exists:evaluation,id_evaluation',
            'observations' => 'nullable|string',
            'name_provider' => 'required|string',
        ]);

        $test_bpm = CheckBpm::create($request->all());

        return response()->json($test_bpm, 201);
        
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
