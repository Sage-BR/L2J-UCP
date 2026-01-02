<?php
require 'db.php';
if (!isset($_SESSION['user'])) { header("Location: index.php"); exit; }
$user = $_SESSION['user'];
$msg = ""; $msgType = "";

// UNSTUCK
if (isset($_POST['unstuck_char'])) {
    $charId = intval($_POST['unstuck_char']);
    $check = $conn->query("SELECT char_name, online FROM characters WHERE charId=$charId AND account_name='$user'");
    if($check->num_rows > 0) {
        $cData = $check->fetch_assoc();
        if($cData['online'] == 0) {
            $conn->query("UPDATE characters SET x=83400, y=147943, z=-3404 WHERE charId=$charId");
            $msg = "Personagem {$cData['char_name']} movido para Giran!"; $msgType = "success";
        } else { $msg = "Erro: O personagem deve estar OFFLINE."; $msgType = "error"; }
    }
}

// DADOS CONTA
$accData = $conn->query("SELECT coins, email, lastAccess FROM accounts WHERE login='$user'")->fetch_assoc();
$coinBalance = $accData['coins'];
$email = !empty($accData['email']) ? $accData['email'] : 'Sem Email';
$lastDate = ($accData['lastAccess'] > 0) ? date("d/m/Y H:i", $accData['lastAccess']) : "Primeiro Acesso";
$lastIP = $_SERVER['REMOTE_ADDR'];
$conn->query("UPDATE accounts SET lastAccess=".time()." WHERE login='$user'");

// CHARS
$sql_chars = "SELECT * FROM characters WHERE account_name='$user'";
$result = $conn->query($sql_chars);
$chars = [];
while($row = $result->fetch_assoc()) $chars[] = $row;

$selectedChar = null;
if (isset($_GET['select'])) {
    foreach($chars as $c) { if ($c['charId'] == $_GET['select']) { $selectedChar = $c; break; } }
} else { if (count($chars) > 0) $selectedChar = $chars[0]; }

// HELPERS
function getClassName($id) {
    $c = [0=>'Human Fighter',1=>'Warrior',2=>'Gladiator',88=>'Duelist',10=>'Human Mage',11=>'Wizard',12=>'Sorcerer',94=>'Archmage',18=>'Elf Fighter',19=>'Elf Knight',25=>'Elf Mage',26=>'Elf Wizard',27=>'Spellsinger',31=>'DE Fighter',38=>'DE Mage',40=>'Spellhowler',44=>'Orc Fighter',49=>'Orc Mage',51=>'Overlord',53=>'Dwarf',55=>'Bounty Hunter',57=>'Warsmith',113=>'Titan',114=>'Grand Khavatari',115=>'Dominator',117=>'Fortune Seeker',118=>'Maestro']; 
    return isset($c[$id]) ? $c[$id] : "ClassID: $id";
}
function getRaceAvatar($race, $sex) {
    $key = $race . '_' . $sex;
    $avatars = [
        '0_0'=>'https://i.imgur.com/IqOJB7P.jpeg', '0_1'=>'https://i.imgur.com/ZYNVeQG.jpeg', //humano homem e mulher
        '1_0'=>'https://i.imgur.com/o003ySK.jpeg', '1_1'=>'https://l2free.org/wp-content/uploads/2024/01/blog3.jpg',
        '2_0'=>'https://i.imgur.com/nPnlbKG.jpeg', '2_1'=>'https://i.imgur.com/dbBPFx0.jpeg', //dark elf homem e mulher
        '3_0'=>'https://i.imgur.com/qQE158N.jpeg', '3_1'=>'https://i.imgur.com/UQF6bPe.jpeg', //orc homem e mulher
        '4_0'=>'https://i.imgur.com/KPE8zmc.jpeg', '4_1'=>'https://i.imgur.com/9m9gMCd.jpeg', //anao homem e mulher
        '5_0'=>'https://i.imgur.com/QOKM3JT.jpeg', '5_1'=>'https://i.imgur.com/dZfkble.jpeg', //kamael homem e mulher
    ];
    return isset($avatars[$key]) ? $avatars[$key] : 'https://i.imgur.com/G4fPj1x.png';
}
function formatTime($s) { return floor($s/3600)."h ".floor(($s%3600)/60)."m"; }
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel - <?php echo $user; ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

<div class="main-logo"><img src="https://l2mundo.com/assets/images/logo2025234.png"></div>

<div class="login-wrapper">
    <div class="dark-sidebar">
        <a href="panel.php" class="sidebar-btn active" title="Painel"><i class="fa-solid fa-house"></i></a>
        <a href="ranking.php" class="sidebar-btn" title="Ranking"><i class="fa-solid fa-trophy"></i></a>
        <a href="shop.php" class="sidebar-btn" title="Loja"><i class="fa-solid fa-cart-shopping"></i></a>
        <a href="donate.php" class="sidebar-btn" title="Doar"><i class="fa-solid fa-circle-dollar-to-slot"></i></a>
        <a href="logout.php" class="sidebar-btn" title="Sair"><i class="fa-solid fa-power-off"></i></a>
    </div>

    <div class="left-panel">
        <div class="left-content">
            <div class="avatar-box">
                <?php 
                    $dispChar = $selectedChar ? $selectedChar : ($chars ? $chars[0] : null);
                    $avatarUrl = $dispChar ? getRaceAvatar($dispChar['race'], $dispChar['sex']) : "https://i.imgur.com/G4fPj1x.png"; 
                ?>
                <img src="<?php echo $avatarUrl; ?>">
            </div>
            <div class="welcome-text"><h2><?php echo strtoupper($user); ?></h2><span><?php echo date("d/m/Y"); ?></span></div>
            <div class="balance-section">
                <div class="balance-header"><span>SALDO ATUAL</span><span><?php echo number_format($coinBalance, 0, ',', '.'); ?> MOEDAS</span></div>
                <div class="balance-actions">
                    <button class="square-btn" onclick="location.href='donate.php'"><i class="fa-solid fa-plus"></i> COMPRAR</button>
                    <button class="square-btn" onclick="location.href='shop.php'"><i class="fa-solid fa-bag-shopping"></i> GASTAR</button>
                </div>
            </div>
        </div>
    </div>

    <div class="right-panel">
        <div class="top-bar-white">
            <div class="flags"><img src="https://upload.wikimedia.org/wikipedia/commons/thumb/0/05/Flag_of_Brazil.svg/32px-Flag_of_Brazil.svg.png"></div>
        </div>

        <div class="form-content">
            <div class="section-header"><i class="fa-solid fa-user-gear"></i> PAINEL DO JOGADOR</div>
            <?php if($msg): ?><div class="msg-alert <?php echo $msgType; ?>"><?php echo $msg; ?></div><?php endif; ?>

            <div class="info-grid">
                <div class="info-box">
                    <div class="box-header"><i class="fa-solid fa-id-card"></i> CONTA</div>
                    <table class="data-table">
                        <tr><td>Login</td><td align="right"><span class="orange-pill"><?php echo $user; ?></span></td></tr>
                        <tr><td>Email</td><td align="right"><?php echo $email; ?></td></tr>
                        <tr><td>Chars</td><td align="right"><b><?php echo count($chars); ?></b></td></tr>
                    </table>
                </div>
                <div class="info-box">
                    <div class="box-header"><i class="fa-solid fa-wifi"></i> ACESSO</div>
                    <table class="data-table">
                        <tr><td>IP</td><td align="right"><?php echo $lastIP; ?></td></tr>
                        <tr><td>Data</td><td align="right"><?php echo $lastDate; ?></td></tr>
                        <tr><td>Status</td><td align="right"><span class="val-green">Online</span></td></tr>
                    </table>
                </div>
            </div>

            <div class="info-box">
                <div class="box-header"><i class="fa-solid fa-users"></i> SELEÇÃO DE PERSONAGEM</div>
                <div class="char-selector">
                    <?php if(empty($chars)) echo "<div style='padding:15px; color:#888;'>Nenhum personagem.</div>"; ?>
                    <?php foreach($chars as $c): ?>
                        <?php $active = ($selectedChar && $selectedChar['charId'] == $c['charId']) ? 'active' : ''; ?>
                        <a href="?select=<?php echo $c['charId']; ?>" class="char-thumb <?php echo $active; ?>" title="<?php echo $c['char_name']; ?>">
                            <img src="<?php echo getRaceAvatar($c['race'], $c['sex']); ?>">
                        </a>
                    <?php endforeach; ?>
                </div>

                <?php if($selectedChar): ?>
                <div class="stats-grid">
                    
                    <div class="stat-item">
                        <div class="stat-label"><i class="fa-solid fa-user"></i> Nome</div>
                        <div class="stat-value"><?php echo $selectedChar['char_name']; ?></div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label"><i class="fa-solid fa-chess-knight"></i> Classe</div>
                        <div class="stat-value"><?php echo getClassName($selectedChar['base_class']); ?></div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label"><i class="fa-solid fa-star"></i> Nível</div>
                        <div class="stat-value"><?php echo $selectedChar['level']; ?></div>
                    </div>

                    <div class="stat-item">
                        <div class="stat-label"><i class="fa-solid fa-crown"></i> Título</div>
                        <div class="stat-value" style="color:#d4b06c;"><?php echo $selectedChar['title'] ?: 'Sem Título'; ?></div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label"><i class="fa-solid fa-venus-mars"></i> Sexo</div>
                        <div class="stat-value"><?php echo $selectedChar['sex']==0?'Masculino':'Feminino'; ?></div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label"><i class="fa-solid fa-shield-halved"></i> Clan</div>
                        <div class="stat-value"><?php echo ($selectedChar['clanid']>0) ? 'Com Clan' : 'Sem Clan'; ?></div>
                    </div>

                    <div class="stat-item">
                        <div class="stat-label"><i class="fa-solid fa-khanda"></i> PvP / PK</div>
                        <div class="stat-value val-green"><?php echo $selectedChar['pvpkills']; ?> <span style="color:#ccc">/</span> <span class="val-red"><?php echo $selectedChar['pkkills']; ?></span></div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label"><i class="fa-solid fa-award"></i> Reputação / Fame</div>
                        <div class="stat-value"><?php echo $selectedChar['reputation']; ?> / <?php echo $selectedChar['fame']; ?></div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label"><i class="fa-solid fa-fire"></i> Karma</div>
                        <div class="stat-value val-red"><?php echo isset($variavel['karma']) ? $variavel['karma'] : 0; ?></div>
                    </div>

                    <div class="stat-item">
                        <div class="stat-label"><i class="fa-solid fa-medal"></i> Status Nobre</div>
                        <div class="stat-value <?php echo $selectedChar['nobless']?'val-gold':''; ?>">
                            <?php echo $selectedChar['nobless']?'Sim (Noble)':'Não'; ?>
                        </div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label"><i class="fa-solid fa-clock"></i> Tempo Jogado</div>
                        <div class="stat-value"><?php echo formatTime($selectedChar['onlinetime']); ?></div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-label"><i class="fa-solid fa-signal"></i> Status Atual</div>
                        <div class="stat-value <?php echo $selectedChar['online']?'val-green':'val-red'; ?>">
                            <?php echo $selectedChar['online']?'Online':'Offline'; ?>
                        </div>
                    </div>

                    <div class="stat-item" style="grid-column: 1 / -1;">
                        <div class="stat-label"><i class="fa-solid fa-calendar-day"></i> Criado em</div>
                        <div class="stat-value" style="font-weight:normal; font-size:12px;">
                            <?php echo isset($selectedChar['createDate']) ? date("d/m/Y", strtotime($selectedChar['createDate'])) : 'Data Indisponível'; ?>
                        </div>
                    </div>

                    <div class="unstuck-area">
                        <form method="post" onsubmit="return confirm('Mover para Giran? (Char deve estar Offline)');">
                            <input type="hidden" name="unstuck_char" value="<?php echo $selectedChar['charId']; ?>">
                            <button type="submit" class="buy-btn" style="width: auto; background: #2c3e50; border:none; color: #fff;">
                                <i class="fa-solid fa-person-walking-arrow-right"></i> DESBUGAR PERSONAGEM
                            </button>
                        </form>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>