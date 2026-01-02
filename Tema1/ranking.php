<?php
require 'db.php';
if (!isset($_SESSION['user'])) { header("Location: index.php"); exit; }
$user = $_SESSION['user'];

// TOP 10 PvP
$sqlPvP = "SELECT char_name, level, pvpkills, pkkills, classid FROM characters WHERE accesslevel=0 ORDER BY pvpkills DESC LIMIT 10";
$resPvP = $conn->query($sqlPvP);

// TOP 10 PK
$sqlPK = "SELECT char_name, level, pvpkills, pkkills, classid FROM characters WHERE accesslevel=0 ORDER BY pkkills DESC LIMIT 10";
$resPK = $conn->query($sqlPK);

// Helper de Classe (Versão resumida para economizar espaço)
function getClassName($id) {
    // Adicione a lista completa aqui se quiser, ou inclua de um arquivo separado
    $classes = [88=>'Duelist', 2=>'Gladiator', 90=>'Phoenix Knight', 92=>'Sagittarius', 94=>'Archmage', 95=>'Soultaker', 108=>'Ghost Hunter', 110=>'Storm Screamer', 113=>'Titan', 114=>'Grand Khavatari', 115=>'Dominator', 117=>'Fortune Seeker'];
    return isset($classes[$id]) ? $classes[$id] : 'Warrior'; 
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Ranking - <?php echo $user; ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .rank-badge { font-size: 14px; margin-right: 10px; }
        .rank-1 { color: #f1c40f; } /* Ouro */
        .rank-2 { color: #bdc3c7; } /* Prata */
        .rank-3 { color: #d35400; } /* Bronze */
    </style>
</head>
<body>

<div class="main-logo"><img src="https://l2mundo.com/assets/images/logo2025234.png"></div>

<div class="login-wrapper">
    <div class="dark-sidebar">
        <a href="panel.php" class="sidebar-btn"><i class="fa-solid fa-house"></i></a>
        <a href="ranking.php" class="sidebar-btn active"><i class="fa-solid fa-trophy"></i></a>
        <a href="shop.php" class="sidebar-btn"><i class="fa-solid fa-cart-shopping"></i></a>
        <a href="donate.php" class="sidebar-btn"><i class="fa-solid fa-circle-dollar-to-slot"></i></a>
        <a href="logout.php" class="sidebar-btn"><i class="fa-solid fa-power-off"></i></a>
    </div>

    <div class="left-panel">
        <div class="left-content">
            <div class="welcome-text"><h2>HALL DA FAMA</h2><span>Os Melhores do Servidor</span></div>
            <div style="font-size: 60px; color: rgba(255,255,255,0.2); margin: 30px 0;"><i class="fa-solid fa-crown"></i></div>
            
            <div class="balance-section">
                <div class="balance-header"><span>ESTATÍSTICAS GERAIS</span></div>
                <div style="text-align:center; font-size:12px; margin-bottom:10px;">
                    PvP Total: <b>9.999</b><br>
                    PK Total: <b>5.432</b>
                </div>
            </div>
        </div>
    </div>

    <div class="right-panel">
        <div class="top-bar-white">
            <div class="flags"><img src="https://upload.wikimedia.org/wikipedia/commons/thumb/0/05/Flag_of_Brazil.svg/32px-Flag_of_Brazil.svg.png"></div>
        </div>

        <div class="form-content">
            <div class="section-header"><i class="fa-solid fa-trophy"></i> TOP 10 PVP</div>
            <div class="info-box" style="margin-bottom:30px;">
                <table class="data-table">
                    <thead><tr style="background:#eee; font-weight:bold;"><td>#</td><td>NOME</td><td>CLASSE</td><td align="right">PVP</td></tr></thead>
                    <tbody>
                        <?php $i=1; while($row = $resPvP->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <?php 
                                    if($i==1) echo '<i class="fa-solid fa-trophy rank-badge rank-1"></i>';
                                    elseif($i==2) echo '<i class="fa-solid fa-medal rank-badge rank-2"></i>';
                                    elseif($i==3) echo '<i class="fa-solid fa-medal rank-badge rank-3"></i>';
                                    else echo $i;
                                ?>
                            </td>
                            <td><span style="font-weight:bold; color:#ea4b18;"><?php echo $row['char_name']; ?></span></td>
                            <td><?php echo getClassName($row['classid']); ?></td>
                            <td align="right" style="color:#27ae60; font-weight:bold;"><?php echo $row['pvpkills']; ?></td>
                        </tr>
                        <?php $i++; endwhile; ?>
                    </tbody>
                </table>
            </div>

            <div class="section-header"><i class="fa-solid fa-skull"></i> TOP 10 PK</div>
            <div class="info-box">
                <table class="data-table">
                    <thead><tr style="background:#eee; font-weight:bold;"><td>#</td><td>NOME</td><td>CLASSE</td><td align="right">PK</td></tr></thead>
                    <tbody>
                        <?php $i=1; while($row = $resPK->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $i; ?></td>
                            <td><span style="font-weight:bold; color:#c0392b;"><?php echo $row['char_name']; ?></span></td>
                            <td><?php echo getClassName($row['classid']); ?></td>
                            <td align="right" style="color:#c0392b; font-weight:bold;"><?php echo $row['pkkills']; ?></td>
                        </tr>
                        <?php $i++; endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>