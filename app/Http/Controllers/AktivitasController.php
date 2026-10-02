<?php

namespace App\Http\Controllers;

use App\Models\Aktivitas;
use Illuminate\Http\Request;

class AktivitasController extends Controller
{
    public function index(Request $request)
    {
        $rows = Aktivitas::with('user:id,nama,role')->orderByDesc('id')->paginate(50);
        return view('aktivitas', compact('rows'));
    }
}
