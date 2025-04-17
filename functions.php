<?php

function checkLogin() {
    session_start();
    if (!isset($_SESSION['user'])) {
        header('Location: login.php');
        exit;
    }
}

function isTeacher() {
    if (!isset($_SESSION['user'])) {
        return false;
    }
    return $_SESSION['user']['role'] === 'teacher';
}

function isStudent() {
    if (!isset($_SESSION['user'])) {
        return false;
    }
    return $_SESSION['user']['role'] === 'student';
}

function getCurrentUserId() {
    if (!isset($_SESSION['user'])) {
        return null;
    }
    return $_SESSION['user']['id'];
}
