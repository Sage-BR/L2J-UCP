<?php
require 'db.php';
require 'config.php';
if (!isset($_SESSION['user'])) { header("Location: index.php"); exit; }
$user = $_SESSION['user'];
$acc = $conn->query("SELECT coins FROM accounts WHERE login='$user'")->fetch_assoc();
$balance = $acc['coins'];
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Doar - <?php echo $user; ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

<div class="main-logo"><img src="https://l2mundo.com/assets/images/logo2025234.png"></div>

<div class="login-wrapper">
    <div class="dark-sidebar">
        <div class="sidebar-btn" onclick="location.href='panel.php'"><i class="fa-solid fa-house"></i></div>
        <div class="sidebar-btn" onclick="location.href='shop.php'"><i class="fa-solid fa-cart-shopping"></i></div>
        <div class="sidebar-btn active"><i class="fa-solid fa-circle-dollar-to-slot"></i></div>
        <div class="sidebar-btn" onclick="location.href='logout.php'"><i class="fa-solid fa-power-off"></i></div>
    </div>

    <div class="left-panel">
        <div class="left-content">
            <div class="welcome-text"><h2>DOAÇÃO PIX</h2><span>Recebimento Automático</span></div>
            <div style="font-size: 60px; color: rgba(255,255,255,0.2); margin: 30px 0;"><i class="fa-brands fa-pix"></i></div>
            <div class="balance-section">
                <div class="balance-header"><span>SALDO ATUAL</span><span><?php echo $balance; ?> MOEDAS</span></div>
            </div>
        </div>
    </div>

    <div class="right-panel">
        <div class="top-bar-white">
            <div class="flags"><img src="https://upload.wikimedia.org/wikipedia/commons/thumb/0/05/Flag_of_Brazil.svg/32px-Flag_of_Brazil.svg.png"></div>
        </div>

        <div class="form-content">
            <div class="section-header"><i class="fa-solid fa-hand-holding-dollar"></i> ADQUIRIR MOEDAS</div>

            <div id="step1">
                <div class="info-box" style="padding: 30px; text-align: center; border-left: 4px solid #ea4b18;">
                    <p style="color:#666; font-size:13px; margin-bottom: 20px;">
                        1 Moeda = <b>R$ <?php echo number_format(COIN_PRICE, 2, ',', '.'); ?></b><br>
                        Digite a quantidade desejada abaixo:
                    </p>
                    
                    <input type="number" id="coinAmount" value="10" min="1" class="input-field" style="width:120px; text-align:center; font-size:18px; margin:0 auto;">
                    <div style="margin: 15px 0; color:#27ae60; font-size:18px; font-weight:bold;">Total: R$ <span id="valBrl">10.00</span></div>
                    
                    <button class="login-submit-btn" onclick="generatePix()">GERAR QR CODE</button>
                </div>
            </div>

            <div id="step2" style="display:none; text-align:center;">
                <div class="msg-alert success">Aguardando Pagamento...</div>
                <img id="qrImage" src="" style="width: 200px; border: 4px solid #fff; box-shadow: 0 0 10px rgba(0,0,0,0.1); border-radius:4px;">
                <br><br>
                <textarea id="copyPaste" class="input-field" style="height:60px; font-size:10px;" readonly></textarea>
                <button class="buy-btn" onclick="copyCode()" style="margin-top:10px;">COPIAR CÓDIGO</button>
                <div id="statusCheck" style="margin-top:20px; font-weight:bold; color:#ea4b18;"><i class="fa-solid fa-spinner fa-spin"></i> Verificando...</div>
            </div>
        </div>
    </div>
</div>

<script>
    $('#coinAmount').on('input', function() {
        var coins = $(this).val();
        $('#valBrl').text((coins * <?php echo COIN_PRICE; ?>).toFixed(2));
    });

    function generatePix() {
        var coins = $('#coinAmount').val();
        if(coins < 1) { alert("Mínimo 1"); return; }
        var btn = $('.login-submit-btn');
        btn.prop('disabled', true).text('Gerando...');

        $.post('api_pix.php', { coins: coins }, function(data) {
            try {
                var res = (typeof data === 'object') ? data : JSON.parse(data);
                if(res.error) { alert(res.error); btn.prop('disabled', false).text('GERAR QR CODE'); } 
                else {
                    $('#step1').slideUp();
                    $('#step2').fadeIn();
                    $('#qrImage').attr('src', 'data:image/jpeg;base64,' + res.qr_base64);
                    $('#copyPaste').val(res.qr_code);
                    startPolling(res.payment_id);
                }
            } catch(e) { alert("Erro ao processar."); btn.prop('disabled', false).text('TENTAR NOVAMENTE'); }
        });
    }

    function copyCode() {
        document.getElementById("copyPaste").select();
        document.execCommand("copy");
        alert("Copiado!");
    }

    function startPolling(paymentId) {
        var interval = setInterval(function() {
            $.post('check_status.php', { id: paymentId }, function(resp) {
                if(resp.trim() == 'approved') {
                    clearInterval(interval);
                    $('#statusCheck').html('<span style="color:green">PAGO! Redirecionando...</span>');
                    setTimeout(function(){ location.href = "panel.php"; }, 2000);
                }
            });
        }, 5000);
    }
</script>
</body>
</html>