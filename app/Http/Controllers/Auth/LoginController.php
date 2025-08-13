<?php
namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Usuario; // Asegúrate de importar tu modelo Usuario
class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'correo' => ['required', 'email'],
            'contrasena' => ['required'],
        ]);
        // Laravel usa 'password' por defecto, pero tu columna se llama 'contrasena'
        $user = Usuario::where('correo', $credentials['correo'])->first();
        if ($user && password_verify($credentials['contrasena'], $user->getAuthPassword())) {
            Auth::login($user);
            $request->session()->regenerate();
            // Redirige al usuario según su rol
            switch ($user->rol->nombre_rol) {
                case 'admin':
                    return redirect()->intended('/admin/dashboard');
                case 'encargado':
                    return redirect()->intended('/encargado/dashboard');
                case 'estudiante':
                    return redirect()->intended('/estudiante/dashboard');
                case 'vinculacion':
                    return redirect()->intended('/vinculacion/dashboard');
            }
        }
        return back()->withErrors([
            'correo' => 'Las credenciales proporcionadas no coinciden con nuestros registros.',
        ])->onlyInput('correo');
    }
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}