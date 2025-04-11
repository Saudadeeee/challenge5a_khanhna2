<?php
// Utility functions for the application

// Check if user is logged in, redirect to login page if not
function checkLogin() {
    session_start();
    if (!isset($_SESSION['user'])) {
        header('Location: login.php');
        exit;
    }
}

// Check if logged-in user is a teacher
function isTeacher() {
    if (!isset($_SESSION['user'])) {
        return false;
    }
    return $_SESSION['user']['role'] === 'teacher';
}

// Check if logged-in user is a student
function isStudent() {
    if (!isset($_SESSION['user'])) {
        return false;
    }
    return $_SESSION['user']['role'] === 'student';
}

// Get current user ID
function getCurrentUserId() {
    if (!isset($_SESSION['user'])) {
        return null;
    }
    return $_SESSION['user']['id'];
}
