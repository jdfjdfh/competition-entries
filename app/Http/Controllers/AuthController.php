<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Http\Requests\Profile\DeleteAccountRequest;
use App\Http\Requests\Auth\QuickSwitchRoleRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;

class AuthController extends Controller
{
    /**
     * Показать форму входа
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Обработка входа в систему
     */
    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            // Перенаправление в зависимости от роли
            $user = Auth::user();

            if ($user->isAdmin()) {
                return redirect()->intended(route('admin.users'))
                    ->with('success', 'Добро пожаловать, администратор!');
            }

            if ($user->isJury()) {
                return redirect()->intended(route('submissions.index'))
                    ->with('success', 'Добро пожаловать в панель жюри!');
            }

            return redirect()->intended(route('dashboard'))
                ->with('success', 'Вы успешно вошли в систему!');
        }

        return back()->withErrors([
            'email' => 'Предоставленные учетные данные не совпадают с нашими записями.',
        ])->onlyInput('email');
    }

    /**
     * Показать форму регистрации
     */
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    /**
     * Обработка регистрации нового пользователя
     */
    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'participant', // По умолчанию все новые пользователи - участники
        ]);

        Auth::login($user);

        return redirect()->route('dashboard')
            ->with('success', 'Регистрация прошла успешно! Добро пожаловать!');
    }

    /**
     * Выход из системы
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')
            ->with('success', 'Вы успешно вышли из системы.');
    }

    /**
     * Проверка текущего статуса аутентификации (для AJAX)
     */
    public function checkAuth(Request $request)
    {
        return response()->json([
            'authenticated' => Auth::check(),
            'user' => Auth::user() ? [
                'id' => Auth::user()->id,
                'name' => Auth::user()->name,
                'email' => Auth::user()->email,
                'role' => Auth::user()->role,
            ] : null
        ]);
    }

    /**
     * Быстрая смена роли (только для разработки/тестирования)
     */
    public function quickSwitchRole(QuickSwitchRoleRequest $request)
    {
        // Этот метод должен быть доступен только в локальной среде
        if (!app()->environment('local')) {
            abort(403);
        }

        $user = Auth::user();
        $user->role = $request->role;
        $user->save();

        return redirect()->back()
            ->with('success', "Роль изменена на {$request->role}");
    }

    /**
     * Обновление профиля пользователя
     */
    public function updateProfile(UpdateProfileRequest $request)
    {
        $user = Auth::user();

        $user->name = $request->name;
        $user->email = $request->email;

        if ($request->filled('new_password')) {
            $user->password = Hash::make($request->new_password);
        }

        $user->save();

        return redirect()->back()
            ->with('success', 'Профиль успешно обновлен!');
    }

    /**
     * Удаление аккаунта пользователя
     */
    public function deleteAccount(DeleteAccountRequest $request)
    {
        $user = Auth::user();

        // Выходим из системы
        Auth::logout();

        // Удаляем пользователя
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')
            ->with('success', 'Ваш аккаунт был успешно удален.');
    }
}
