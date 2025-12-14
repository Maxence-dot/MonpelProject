<?php
require_once __DIR__ . '/../connexionAll.php';
require_once __DIR__ . '/../Repositories/UserRepository.php';

class AuthController
{
    private static function getUserRepository(): UserRepository
    {
        return new UserRepository($GLOBALS['pdo']);
    }

    public static function login()
    {
        session_start();

        $error = null;
        $old = ['email' => ''];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $old['email'] = $email;
            if ($email && $password) {
                $userRepo = self::getUserRepository();
                $user = $userRepo->findByEmail($email);

                if ($user && password_verify($password, $user['password'])) {
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_email'] = $user['email'];

                    $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
                    header('Location: ' . $basePath . '/index.php');
                    exit;
                } else {
                    $error = 'Email ou mot de passe incorrect';
                }
            } else {
                $error = 'Veuillez remplir tous les champs';
            }
        }

        include __DIR__ . '/../views/auth/login_view.php';
    }

    public static function register()
    {
        session_start();

        $errors = [];
        $old = ['email' => '', 'firstname' => '', 'lastname' => ''];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $password2 = $_POST['password_confirm'] ?? '';
            $firstname = trim($_POST['firstname'] ?? '');
            $lastname = trim($_POST['lastname'] ?? '');

            $old = ['email' => $email, 'firstname' => $firstname, 'lastname' => $lastname];

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Email invalide';
            }
            if (strlen($password) < 6) {
                $errors[] = 'Le mot de passe doit contenir au moins 6 caractères';
            }
            if ($password !== $password2) {
                $errors[] = 'Les mots de passe ne correspondent pas';
            }

            if (empty($errors)) {
                $userRepo = self::getUserRepository();
                if ($userRepo->emailExists($email)) {
                    $errors[] = 'Un compte avec cet email existe déjà';
                } else {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    try {
                        $userRepo->create($email, $hash, $firstname, $lastname);
                        $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
                        header('Location: ' . $basePath . '/login.php?registered=1');
                        exit;
                    } catch (Exception $e) {
                        $errors[] = 'Erreur enregistrement: ' . $e->getMessage();
                    }
                }
            }
        }

        include __DIR__ . '/../views/auth/register_view.php';
    }

    public static function logout()
    {
        session_start();
        session_destroy();
        $basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
        header('Location: ' . $basePath . '/login.php');
        exit;
    }
}
