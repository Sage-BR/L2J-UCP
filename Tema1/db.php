<?php
session_start();
$host = '127.0.0.1';
$user = 'root';
$pass = 'asenha'; // <--- COLOQUE SUA SENHA DO BANCO
$db   = 'l2jmobiusclassicinterlude';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("Erro Fatal: " . $conn->connect_error);

// Função para Criptografia (SHA-1 que seu servidor usa)
function encryptPass($password) {
    return base64_encode(sha1($password, true));
}
?>