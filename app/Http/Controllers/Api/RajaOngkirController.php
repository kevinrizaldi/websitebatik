<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\RajaOngkirService;
use Illuminate\Http\Request;

class RajaOngkirController extends Controller
{
    public function provinsi(RajaOngkirService $svc)
    {
        return response()->json(['data' => $svc->provinsi()]);
    }

    public function kota(RajaOngkirService $svc, int $provinsiId)
    {
        return response()->json(['data' => $svc->kota($provinsiId)]);
    }

    public function hitung(Request $request, RajaOngkirService $svc)
    {
        $validated = $request->validate([
            'destination' => 'required|integer', // kota_id tujuan
            'weight'      => 'required|integer|min:1', // total berat gram (sudah termasuk packaging)
            'courier'     => 'nullable|in:jne,tiki,pos,all',
        ]);

        $data = $svc->hitungOngkir(
            $validated['destination'],
            $validated['weight'],
            $validated['courier'] ?? 'jne'
        );

        return response()->json(['data' => $data]);
    }
}