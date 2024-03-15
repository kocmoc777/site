<?php
include SITE_ROOT . "/app/database/db.php";

$errMsg = [];

function userAuth($user){
    $_SESSION['id'] = $user['id'];
    $_SESSION['login'] = $user['username'];
    $_SESSION['admin'] = $user['admin'];
    if($_SESSION['admin']){
        header('location: ' . BASE_URL . "admin/posts/index.php");
    }else{
        header('location: ' . BASE_URL);
    }
}

$users = selectAll('users');

// Код для форми реєстрації
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['button-reg'])){
    $admin = 0;
    $login = trim($_POST['login']);
    $email = trim($_POST['mail']);
    $passF = trim($_POST['pass-first']);
    $passS = trim($_POST['pass-second']);

    if($login === '' || $email === '' || $passF === ''){
        array_push($errMsg, "Не всі поля заповнені!");
    }elseif (mb_strlen($login, 'UTF8') < 2){
        array_push($errMsg, "Логін має бути більше 2-х символів");
    }elseif ($passF !== $passS) {
        array_push($errMsg, "Паролі в обох полях повинні відповідати!");
    }else{
        $existence = selectOne('users', ['email' => $email]);
        if($existence && $existence['email'] === $email){
            array_push($errMsg, "Користувач із такою поштою вже зареєстрований!");
        }else{
            $pass = password_hash($passF, PASSWORD_DEFAULT);
            $post = [
                'admin' => $admin,
                'username' => $login,
                'email' => $email,
                'password' => $pass
            ];
            $id = insert('users', $post);
            $user = selectOne('users', ['id' => $id] );
            userAuth($user);
        }
    }
}else{
    $login = '';
    $email = '';
}

// Код для форми авторизації
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['button-log'])){
    $email = trim($_POST['mail']);
    $pass = trim($_POST['password']);

    if($email === '' || $pass === '') {
        array_push($errMsg, "Пользователь с такой почтой уже зарегистрирован!");
    }else{
        $existence = selectOne('users', ['email' => $email]);
        if($existence && password_verify($pass, $existence['password'])){
            userAuth($existence);
        }else{
            array_push($errMsg, "ППошта або пароль введені неправильно!");
        }
    }
}else{
    $email = '';
}

// Код добавления пользователя в админку
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create-user'])){
    $admin = 0;
    $login = trim($_POST['login']);
    $email = trim($_POST['mail']);
    $passF = trim($_POST['pass-first']);
    $passS = trim($_POST['pass-second']);

    if($login === '' || $email === '' || $passF === ''){
        array_push($errMsg, "Не всі поля заповнені!");
    }elseif (mb_strlen($login, 'UTF8') < 2){
        array_push($errMsg, "Логін має бути більше 2-х символів");
    }elseif ($passF !== $passS) {
        array_push($errMsg, "Паролі в обох полях повинні відповідати!");
    }else{
        $existence = selectOne('users', ['email' => $email]);
        if($existence && $existence['email'] === $email){
            array_push($errMsg, "Користувач із такою поштою вже зареєстрований!");
        }else{
            $pass = password_hash($passF, PASSWORD_DEFAULT);
            if (isset($_POST['admin'])) $admin = 1;
            $user = [
                'admin' => $admin,
                'username' => $login,
                'email' => $email,
                'password' => $pass
            ];
            $id = insert('users', $user);
            $user = selectOne('users', ['id' => $id] );
            userAuth($user);
        }
    }
}else{
    $login = '';
    $email = '';
}

// Код видалення користувача в адмінці
if($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['delete_id'])){
    $id = $_GET['delete_id'];
    delete('users', $id);
    header('location: ' . BASE_URL . 'admin/users/index.php');
}

// РЕДАГУВАННЯ КОРИСТУВАЧА ЧЕРЕЗ АДМІНКУ
if($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['edit_id'])){
    $user = selectOne('users', ['id' => $_GET['edit_id']]);
    $id = $user['id'];
    $admin = $user['admin'];
    $username = $user['username'];
    $email = $user['email'];
}

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update-user'])){
    $id = $_POST['id'];
    $mail = trim($_POST['mail']);
    $login = trim($_POST['login']);
    $passF = trim($_POST['pass-first']);
    $passS = trim($_POST['pass-second']);
    $admin = isset($_POST['admin']) ? 1 : 0;

    if($login === ''){
        array_push($errMsg, "Не всі поля заповнені!");
    }elseif (mb_strlen($login, 'UTF8') < 2){
        array_push($errMsg, "Логін має бути більше 2-х символів");
    }elseif ($passF !== $passS) {
        array_push($errMsg, "Паролі в обох полях повинні відповідати!");
    }else{
        $pass = password_hash($passF, PASSWORD_DEFAULT);
        if (isset($_POST['admin'])) $admin = 1;
        $user = [
            'admin' => $admin,
            'username' => $login,
            'password' => $pass
        ];
        update('users', $id, $user);
        header('location: ' . BASE_URL . 'admin/users/index.php');
    }
}
