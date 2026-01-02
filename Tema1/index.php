<?php
require 'db.php';
require 'config.php'; // Para pegar as chaves do Recaptcha

$msg = ""; $msgType = "";

// Função para validar o Recaptcha no Google
function validarRecaptcha($response) {
    if (empty($response)) return false;
    
    $secret = RECAPTCHA_SECRET_KEY;
    $ip = $_SERVER['REMOTE_ADDR'];
    $url = "https://www.google.com/recaptcha/api/siteverify?secret=$secret&response=$response&remoteip=$ip";
    
    $fire = file_get_contents($url);
    $data = json_decode($fire);
    
    return $data->success;
}

// --- LOGAR ---
if (isset($_POST['login_btn'])) {
    $login = $conn->real_escape_string($_POST['login']);
    $pass  = encryptPass($_POST['password']);
    
    // Verifica Recaptcha
    if (!validarRecaptcha($_POST['g-recaptcha-response'])) {
        $msg = "Por favor, confirme que você não é um robô.";
        $msgType = "error";
    } else {
        $check = $conn->query("SELECT login FROM accounts WHERE login='$login' AND password='$pass'");
        if ($check->num_rows > 0) {
            $_SESSION['user'] = $login;
            header("Location: panel.php"); exit;
        } else {
            $msg = "Login ou senha incorretos.";
            $msgType = "error";
        }
    }
}

// --- CADASTRAR ---
if (isset($_POST['register'])) {
    $login = $conn->real_escape_string($_POST['login']);
    $pass  = encryptPass($_POST['password']);
    
    // Verifica Recaptcha
    if (!validarRecaptcha($_POST['g-recaptcha-response'])) {
        $msg = "Por favor, confirme que você não é um robô.";
        $msgType = "error";
    } else {
        $check = $conn->query("SELECT login FROM accounts WHERE login='$login'");
        if ($check->num_rows > 0) { 
            $msg = "Esta conta já existe!"; 
            $msgType = "error"; 
        } else {
            $conn->query("INSERT INTO accounts (login, password) VALUES ('$login', '$pass')");
            $msg = "Conta criada com sucesso! Faça login."; 
            $msgType = "success";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>L2 Shavuska - Login</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body>

<div class="main-logo">
    <img src="https://l2mundo.com/assets/images/logo2025234.png" alt="Logo">
</div>

<div class="login-wrapper">

    <div class="dark-sidebar">
        <div class="sidebar-header"><i class="fa-solid fa-shield-cat"></i></div>
        <div class="sidebar-btn active" onclick="location.reload()" title="Início">
            <img src="botão home.png" alt="Home">
        </div>
        <div class="sidebar-btn hidden" id="btn-voltar-icon" onclick="showLogin()" title="Voltar">
            <img src="botão voltar.png" alt="Voltar">
        </div>
    </div>

    <div class="left-panel">
        <div class="left-content">
            <div class="avatar-box">
                <img src="https://l2free.org/wp-content/uploads/2024/01/blog3.jpg">
            </div>
            <div class="welcome-text">
                <h2>BEM-VINDO!</h2>
                <span>Gerencie sua conta com segurança.</span>
            </div>
            <div class="orange-actions">
                <button onclick="showRegister()" class="trans-btn" id="btn-criar">
                    <i class="fa-solid fa-user-plus"></i> CADASTRAR CONTA
                </button>
                <button onclick="alert('Em breve!')" class="trans-btn">
                    <i class="fa-solid fa-key"></i> RECUPERAR SENHA
                </button>
            </div>
        </div>
    </div>

    <div class="right-panel">
        <div class="top-bar-white">
            <div class="flags">
                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/a/a4/Flag_of_the_United_States.svg/32px-Flag_of_the_United_States.svg.png">
                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/0/05/Flag_of_Brazil.svg/32px-Flag_of_Brazil.svg.png">
            </div>
        </div>

        <div class="form-content">
            <?php if($msg): ?><div class="msg-alert <?php echo $msgType; ?>"><?php echo $msg; ?></div><?php endif; ?>

            <div id="login-box">
                <div class="section-header"><i class="fa-solid fa-right-to-bracket"></i> ACESSAR PAINEL</div>
                <form method="post">
                    <div class="input-group">
                        <label class="input-label">Login:</label>
                        <input type="text" name="login" class="input-field" required>
                    </div>
                    <div class="input-group">
                        <label class="input-label">Senha:</label>
                        <input type="password" name="password" class="input-field" required>
                    </div>
                    
                    <div style="margin-bottom: 20px; display: flex; justify-content: center;">
                        <div class="g-recaptcha" data-sitekey="<?php echo RECAPTCHA_SITE_KEY; ?>"></div>
                    </div>

                    <button type="submit" name="login_btn" class="login-submit-btn">ENTRAR AGORA</button>
                </form>
            </div>

            <div id="register-box" class="hidden">
                <div class="section-header"><i class="fa-solid fa-user-plus"></i> NOVA CONTA</div>
                <form method="post">
                    <div class="input-group">
                        <label class="input-label">Usuário:</label>
                        <input type="text" name="login" class="input-field" required>
                    </div>
                    <div class="input-group">
                        <label class="input-label">Senha:</label>
                        <input type="password" name="password" class="input-field" required>
                    </div>

                    <div style="margin-bottom: 20px; display: flex; justify-content: center;">
                        <div class="g-recaptcha" data-sitekey="<?php echo RECAPTCHA_SITE_KEY; ?>"></div>
                    </div>

                    <button type="submit" name="register" class="login-submit-btn">CRIAR CONTA</button>
                </form>
                <div class="form-links"><a onclick="showLogin()">Já tenho conta</a></div>
            </div>
        </div>
    </div>
</div>

<script>
function showRegister() {
    document.getElementById('login-box').classList.add('hidden');
    document.getElementById('register-box').classList.remove('hidden');
    document.getElementById('btn-voltar-icon').classList.remove('hidden');
    document.getElementById('btn-criar').style.display = 'none';
}
function showLogin() {
    document.getElementById('register-box').classList.add('hidden');
    document.getElementById('login-box').classList.remove('hidden');
    document.getElementById('btn-voltar-icon').classList.add('hidden');
    document.getElementById('btn-criar').style.display = 'flex';
}
</script>
</body>
</html>