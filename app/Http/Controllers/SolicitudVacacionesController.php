<?php

namespace App\Http\Controllers;

use App\Models\SolicitudVacaciones;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SolicitudVacacionesController extends Controller
{
    public function index() {
        return SolicitudVacaciones::with('empleado')->where('user_id', Auth::id())->get();
    }

    public function store(Request $request) {
        $request->validate([
            'fecha_inicio' => 'required|date|after_or_equal:today',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'motivo' => 'nullable|string'
        ]);

        return SolicitudVacaciones::create([
            'user_id' => Auth::id(),
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
            'motivo' => $request->motivo,
        ]);
    }

    public function pendientes() {
        return SolicitudVacaciones::with('empleado')
            ->whereIn('estado', ['pendiente_rrhh', 'pendiente_jefe'])
            ->get();
    }

    public function aprobar($id) {
        $solicitud = SolicitudVacaciones::findOrFail($id);
        if ($solicitud->estado === 'pendiente_rrhh') {
            $solicitud->estado = 'pendiente_jefe';
            $solicitud->aprobado_por_rrhh = Auth::id();
        } elseif ($solicitud->estado === 'pendiente_jefe') {
            $solicitud->estado = 'aprobada';
            $solicitud->aprobado_por_jefe = Auth::id();
        }
        $solicitud->save();
        return response()->json(['success' => true, 'data' => $solicitud]);
    }

    public function rechazar($id) {
        $solicitud = SolicitudVacaciones::findOrFail($id);
        $solicitud->estado = 'rechazada';
        if ($solicitud->estado === 'pendiente_rrhh') {
            $solicitud->aprobado_por_rrhh = Auth::id();
        } elseif ($solicitud->estado === 'pendiente_jefe') {
            $solicitud->aprobado_por_jefe = Auth::id();
        }
        $solicitud->save();
        return response()->json(['success' => true, 'data' => $solicitud]);
    }
}
