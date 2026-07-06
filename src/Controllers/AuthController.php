<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Auth;
use App\Csrf;
use App\View;

final class AuthController
{
    public function showLogin(): void
    {
        if (Auth::check()) {
            $this->redirectToDashboard();
            return;
        }

        View::render('auth/login', ['pageTitle' => 'Entrar'], null);
    }

    public function login(): void
    {
        Csrf::requireValid();

        $email = trim($_POST['email'] ?? '');
        $password = (string) ($_POST['password'] ?? '');

        if (Auth::attempt($email, $password)) {
            $this->redirectToDashboard();
            return;
        }

        $_SESSION['flash_error'] = 'E-mail ou senha inválidos.';
        header('Location: /login');
    }

    public function logout(): void
    {
        Csrf::requireValid();
        Auth::logout();
        header('Location: /login');
    }

    private function redirectToDashboard(): void
    {
        $role = Auth::role();
        $target = match ($role) {
            'admin' => '/admin/users',
            'promoter' => '/promoter/events',
            'client' => '/client/events',
            default => '/login',
        };
        header('Location: ' . $target);
    }
}
