<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = htmlspecialchars($_POST['edit-user-name']);
    $age = htmlspecialchars($_POST['edit-user-age']);
    $info = htmlspecialchars($_POST['edit-user-info']);

    $_SESSION['login'] = $name;
    $_SESSION['age'] = $age;
    $_SESSION['user_info'] = $info;

    echo json_encode([
        'success' => true,
        'newName' => $name,
        'newAge' => $age,
        'newInfo' => $info
    ]);
    exit;
}