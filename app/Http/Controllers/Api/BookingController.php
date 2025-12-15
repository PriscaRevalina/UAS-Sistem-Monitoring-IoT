<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    // ambil semua booking
    public function index() {
        $bookings = Booking::with(['user', 'service'])->get();
        return response()->json($bookings, 200);
    }

    // buat booking baru
    public function store(Request $request) {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'service_id' => 'required|exists:services,id',
            'booking_date' => 'required|date',
            'status' => 'sometimes|in:pending,confirmed,completed,cancelled'
        ]);

        $booking = Booking::create($validated);
        return response()->json([
            'message' => 'Booking berhasil dibuat',
            'data' => $booking
        ], 201);
    }

    // lihat detail booking
    public function show($id) {
        $booking = Booking::with(['user', 'service'])->find($id);
        
        if (!$booking) {
            return response()->json([
                'message' => 'Booking tidak ditemukan'
            ], 404);
        }

        return response()->json($booking, 200);
    }

    // update booking
    public function update(Request $request, $id) {
        $booking = Booking::find($id);
        
        if (!$booking) {
            return response()->json([
                'message' => 'Booking tidak ditemukan'
            ], 404);
        }

        $validated = $request->validate([
            'booking_date' => 'sometimes|required|date',
            'status' => 'sometimes|in:pending,confirmed,completed,cancelled'
        ]);

        $booking->update($validated);
        return response()->json([
            'message' => 'Booking berhasil diupdate',
            'data' => $booking
        ], 200);
    }

    // hapus booking
    public function destroy($id) {
        $booking = Booking::find($id);
        
        if (!$booking) {
            return response()->json([
                'message' => 'Booking tidak ditemukan'
            ], 404);
        }

        $booking->delete();
        return response()->json([
            'message' => 'Booking berhasil dihapus'
        ], 200);
    }
}