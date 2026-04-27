<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    
     // pa mostrar el formulario de login
     
    public function showLogin()
    {
        // Crear un usuario si no existe
        $this->createDefaultUser();
        
        // Si ya está autenticado, redirigir al dashboard*(o sea pantalla general)
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        
        return view('auth.login');
    }

     
    private function createDefaultUser()
    {
        if (!User::where('email', 'admin@neurovida.com')->exists()) {
            User::create([
                'name' => 'Administrador',
                'email' => 'admin@neurovida.com',
                'password' => Hash::make('password123'),
            ]);
        }
    }


    public function login(Request $request)
    {
        // Crear usuario si no existe
        $this->createDefaultUser();

        // Validación
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6'
        ]);

        // Intentar login
        if (Auth::attempt([
            'email' => $request->email,
            'password' => $request->password
        ])) {
            $request->session()->regenerate();
            return redirect()->route('dashboard');
        }

        return back()->withErrors([
            'email' => 'Las credenciales no coinciden.',
        ])->onlyInput('email');
    }

    // Cerrar sesión
     
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/');
    }
}
