<?php
require 'db.php';
require 'config.php';

$id = $_POST['id'];

$ch = curl_init("https://api.mercadopago.com/v1/payments/$id");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer " . MP_ACCESS_TOKEN]);
$response = curl_exec($ch);
curl_close($ch);
$mp = json_decode($response, true);

$status = $mp['status'] ?? 'pending';

if ($status == 'approved') {
    // Verifica se já não entregou antes (segurança contra duplicidade)
    $check = $conn->query("SELECT status, account_name, coins_received FROM donations WHERE payment_id='$id'")->fetch_assoc();
    
    if ($check && $check['status'] != 'approved') {
        // 1. Atualiza doação para 'approved'
        $conn->query("UPDATE donations SET status='approved' WHERE payment_id='$id'");
        
        // 2. Adiciona saldo na conta do player
        $coins = $check['coins_received'];
        $acc = $check['account_name'];
        $conn->query("UPDATE accounts SET coins = coins + $coins WHERE login='$acc'");
    }
}

echo $status;
?>