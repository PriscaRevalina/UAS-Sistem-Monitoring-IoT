<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    // ambil semua data service
    public function index() {
        $services = Service::all();
        return response()->json(Service::all(), 200);;
    }

    // tambah service baru
    public function store(Request $request) {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|integer|min:0'
        ]);

        $service = Service::create($validated);
        return response()->json([
            'message' => 'Service berhasil ditambahkan',
            'data' => $service
        ], 201);
    }

    // lihat detail service
    public function show($id) {
        $service = Service::find($id);
        
        if (!$service) {
            return response()->json([
                'message' => 'Service tidak ditemukan'
            ], 404);
        }

        return response()->json($service, 200);
    }

    // update service
    public function update(Request $request, $id) {
        $service = Service::find($id);
        
        if (!$service) {
            return response()->json([
                'message' => 'Service tidak ditemukan'
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'price' => 'sometimes|required|integer|min:0'
        ]);

        $service->update($validated);
        return response()->json([
            'message' => 'Service berhasil diupdate',
            'data' => $service
        ], 200);
    }

    // hapus service
    public function destroy($id) {
        $service = Service::find($id);
        
        if (!$service) {
            return response()->json([
                'message' => 'Service tidak ditemukan'
            ], 404);
        }

        $service->delete();
        return response()->json([
            'message' => 'Service berhasil dihapus'
        ], 200);
    }
}