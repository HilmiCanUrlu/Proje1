<?php
session_start();
header('Content-Type: application/json');

$module = $_GET['module'] ?? '';
$role = $_SESSION['role'] ?? '';

// Rol bazlı yetkilendirme
$permissions = [
    'admin' => ['users', 'reports', 'settings'],
    'manager' => ['reports', 'settings'],
    'user' => ['reports']
];

$hasPermission = isset($permissions[$role]) && in_array($module, $permissions[$role]);

echo json_encode(['hasPermission' => $hasPermission]); 