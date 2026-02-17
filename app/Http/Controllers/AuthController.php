<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Validation\ValidationException;

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
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

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

        throw ValidationException::withMessages([
            'email' => __('Предоставленные учетные данные не совпадают с нашими записями.'),
        ]);
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
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
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
     * Показать форму для сброса пароля
     */
    public function showForgotForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Отправка ссылки для сброса пароля
     */
    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? back()->with(['success' => __($status)])
            : back()->withErrors(['email' => __($status)]);
    }

    /**
     * Показать форму сброса пароля
     */
    public function showResetForm(string $token)
    {
        return view('auth.reset-password', ['token' => $token]);
    }

    /**
     * Сброс пароля
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('success', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    }

    /**
     * Подтверждение email (если используется)
     */
    public function verifyEmail(Request $request)
    {
        $request->user()->markEmailAsVerified();

        return redirect()->intended('dashboard')
            ->with('success', 'Email успешно подтвержден!');
    }

    /**
     * Отправка повторного письма для подтверждения email
     */
    public function resendVerificationEmail(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('success', 'Ссылка для подтверждения отправлена!');
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
    public function quickSwitchRole(Request $request)
    {
        // Этот метод должен быть доступен только в локальной среде
        if (!app()->environment('local')) {
            abort(403);
        }

        $request->validate([
            'role' => 'required|in:participant,jury,admin'
        ]);

        $user = Auth::user();
        $user->role = $request->role;
        $user->save();

        return redirect()->back()
            ->with('success', "Роль изменена на {$request->role}");
    }

    /**
     * Обновление профиля пользователя
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'current_password' => ['nullable', 'required_with:new_password', 'current_password'],
            'new_password' => ['nullable', 'min:8', 'confirmed'],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        if (!empty($validated['new_password'])) {
            $user->password = Hash::make($validated['new_password']);
        }

        $user->save();

        return redirect()->back()
            ->with('success', 'Профиль успешно обновлен!');
    }

    /**
     * Удаление аккаунта пользователя
     */
    public function deleteAccount(Request $request)
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

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
