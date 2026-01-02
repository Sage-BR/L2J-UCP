<?php
require 'db.php';
if (!isset($_SESSION['user'])) { header("Location: index.php"); exit; }
$user = $_SESSION['user'];
$msg = ""; $msgType = "";

// Verifica GM
$checkGM = $conn->query("SELECT count(*) as total FROM characters WHERE account_name='$user' AND accesslevel >= 100");
$isGM = ($checkGM->fetch_assoc()['total'] > 0);

// --- ADMIN ---
if ($isGM && isset($_POST['save_item'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $desc = $conn->real_escape_string($_POST['desc']);
    $icon = $conn->real_escape_string($_POST['icon']);
    $item_id = intval($_POST['item_id']);
    $count = intval($_POST['count']);
    $price = intval($_POST['price']);
    $stock = intval($_POST['stock']); 
    $limit = intval($_POST['limit']); 
    
    if (!empty($_POST['editing_id'])) {
        $editId = intval($_POST['editing_id']);
        $sql = "UPDATE site_shop_items SET item_id=$item_id, count=$count, name='$name', description='$desc', price=$price, icon='$icon', stock=$stock, limit_count=$limit WHERE id=$editId";
        $conn->query($sql);
        $msg = "Item atualizado!";
    } else {
        $sql = "INSERT INTO site_shop_items (item_id, count, name, description, price, icon, stock, limit_count) VALUES ($item_id, $count, '$name', '$desc', $price, '$icon', $stock, $limit)";
        $conn->query($sql);
        $msg = "Item criado!";
    }
    $msgType = "success";
}

if ($isGM && isset($_GET['del'])) {
    $delId = intval($_GET['del']);
    $conn->query("DELETE FROM site_shop_items WHERE id=$delId");
    header("Location: shop.php"); exit;
}

// --- COMPRA ---
if (isset($_POST['buy_id'])) {
    $buyDbId = intval($_POST['buy_id']);
    $charId = intval($_POST['char_id']);
    $itemQuery = $conn->query("SELECT * FROM site_shop_items WHERE id=$buyDbId");
    
    if ($itemQuery->num_rows > 0 && !empty($charId)) {
        $item = $itemQuery->fetch_assoc();
        
        // Verifica Limite
        $boughtCount = 0;
        if ($item['limit_count'] > 0) {
            $hist = $conn->query("SELECT COUNT(*) as total FROM site_shop_history WHERE login='$user' AND item_db_id=$buyDbId");
            $boughtCount = $hist->fetch_assoc()['total'];
        }

        if ($item['stock'] == 0) { $msg = "Esgotado!"; $msgType = "error"; }
        else if ($item['limit_count'] > 0 && $boughtCount >= $item['limit_count']) { $msg = "Limite atingido!"; $msgType = "error"; }
        else {
            $accRow = $conn->query("SELECT coins FROM accounts WHERE login='$user'")->fetch_assoc();
            if ($accRow['coins'] >= $item['price']) {
                $conn->query("UPDATE accounts SET coins = coins - {$item['price']} WHERE login='$user'");
                if ($item['stock'] > 0) $conn->query("UPDATE site_shop_items SET stock = stock - 1 WHERE id=$buyDbId");
                
                $conn->query("INSERT INTO site_shop_history (login, item_db_id, count) VALUES ('$user', $buyDbId, 1)"); // Histórico
                
                $stmt = $conn->prepare("INSERT INTO items_delayed (owner_id, item_id, count, payment_status, description) VALUES (?, ?, ?, 0, ?)");
                $desc = "Shop: " . $item['name'];
                $stmt->bind_param("iiis", $charId, $item['item_id'], $item['count'], $desc);
                $stmt->execute();
                
                $msg = "Compra realizada!"; $msgType = "success";
            } else { $msg = "Saldo insuficiente."; $msgType = "error"; }
        }
    } else { $msg = "Selecione um personagem."; $msgType = "error"; }
}

$chars = [];
$res = $conn->query("SELECT charId, char_name FROM characters WHERE account_name='$user'");
while($r = $res->fetch_assoc()) $chars[] = $r;

$shopItems = [];
$resShop = $conn->query("SELECT * FROM site_shop_items ORDER BY id DESC");
while($r = $resShop->fetch_assoc()) $shopItems[] = $r;
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Loja - <?php echo $user; ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script>
    function toggleAdmin() { document.getElementById('adminPanel').classList.toggle('hidden'); }
    function editItem(item) {
        document.getElementById('adminPanel').classList.remove('hidden');
        document.getElementById('edit_id').value = item.id;
        document.getElementById('edit_name').value = item.name;
        document.getElementById('edit_desc').value = item.description;
        document.getElementById('edit_iid').value = item.item_id;
        document.getElementById('edit_count').value = item.count;
        document.getElementById('edit_price').value = item.price;
        document.getElementById('edit_icon').value = item.icon;
        document.getElementById('edit_stock').value = item.stock;
        document.getElementById('edit_limit').value = item.limit_count;
        document.querySelector('.form-content').scrollTop = 0;
    }
    </script>
</head>
<body>

<div class="main-logo"><img src="https://l2mundo.com/assets/images/logo2025234.png"></div>

<div class="login-wrapper">
    <div class="dark-sidebar">
        <a href="panel.php" class="sidebar-btn"><i class="fa-solid fa-house"></i></a>
        <a href="shop.php" class="sidebar-btn active"><i class="fa-solid fa-cart-shopping"></i></a>
        <a href="donate.php" class="sidebar-btn"><i class="fa-solid fa-circle-dollar-to-slot"></i></a>
        <a href="logout.php" class="sidebar-btn"><i class="fa-solid fa-power-off"></i></a>
    </div>

    <div class="left-panel">
        <div class="left-content">
            <div class="welcome-text"><h2>MARKETPLACE</h2><span>Itens Exclusivos</span></div>
            <div style="font-size: 60px; color: rgba(255,255,255,0.2); margin: 30px 0;"><i class="fa-solid fa-bag-shopping"></i></div>
            <?php if($isGM): ?>
            <button onclick="toggleAdmin()" class="trans-btn"><i class="fa-solid fa-cog"></i> GERENCIAR</button>
            <?php endif; ?>
        </div>
    </div>

    <div class="right-panel">
        <div class="top-bar-white">
            <div class="flags"><img src="https://upload.wikimedia.org/wikipedia/commons/thumb/0/05/Flag_of_Brazil.svg/32px-Flag_of_Brazil.svg.png"></div>
        </div>

        <div class="form-content">
            <div class="section-header"><i class="fa-solid fa-cart-shopping"></i> LOJA DE ITENS</div>
            <?php if($msg): ?><div class="msg-alert <?php echo $msgType; ?>"><?php echo $msg; ?></div><?php endif; ?>

            <?php if($isGM): ?>
            <div id="adminPanel" class="admin-box hidden">
                <h4 style="margin-top:0; color:#ea4b18; border-bottom:1px solid #444; padding-bottom:10px;">Adicionar / Editar Item</h4>
                <form method="post" class="admin-form">
                    <input type="hidden" name="editing_id" id="edit_id">
                    <input type="text" name="name" id="edit_name" placeholder="Nome do Item" required>
                    <input type="text" name="desc" id="edit_desc" placeholder="Descrição Curta" required>
                    <input type="number" name="item_id" id="edit_iid" placeholder="ID no Jogo" required>
                    <input type="number" name="count" id="edit_count" placeholder="Quantidade" required>
                    <input type="number" name="price" id="edit_price" placeholder="Preço (Moedas)" required>
                    <input type="text" name="icon" id="edit_icon" placeholder="Ícone (fa-sword ou https://link...)" required>
                    <input type="number" name="stock" id="edit_stock" placeholder="Estoque (-1 = Infinito)" required>
                    <input type="number" name="limit" id="edit_limit" placeholder="Limite por Conta (0 = Sem)" required>
                    <div class="admin-btns">
                        <button type="submit" name="save_item" class="btn-save">SALVAR ITEM</button>
                        <button type="button" onclick="toggleAdmin()" class="btn-cancel">CANCELAR</button>
                    </div>
                </form>
            </div>
            <?php endif; ?>

            <form id="shopForm" method="post" style="margin-bottom:20px;">
                <select name="char_id" class="input-field" required>
                    <option value="">Selecione o Personagem...</option>
                    <?php foreach($chars as $c): ?><option value="<?php echo $c['charId']; ?>"><?php echo $c['char_name']; ?></option><?php endforeach; ?>
                </select>
                <input type="hidden" name="buy_id" id="buy_id_input">
            </form>

            <div class="shop-grid">
                <?php foreach($shopItems as $item): ?>
                    <?php $isSoldOut = ($item['stock'] == 0); $stockLabel = ($item['stock'] == -1) ? "∞" : $item['stock']; ?>
                <div class="item-card">
                    <span class="badge-stock <?php echo $isSoldOut?'badge-soldout':''; ?>">Estoque: <?php echo $stockLabel; ?></span>
                    <?php if($isGM): ?>
                        <div style="position:absolute; top:5px; right:5px; z-index:5;">
                            <i class="fa-solid fa-pen" style="color:#f39c12; cursor:pointer;" onclick='editItem(<?php echo json_encode($item); ?>)'></i>
                            <a href="?del=<?php echo $item['id']; ?>" onclick="return confirm('Apagar?');"><i class="fa-solid fa-trash" style="color:#e74c3c;"></i></a>
                        </div>
                    <?php endif; ?>
                    <div class="item-icon">
                        <?php if(strpos($item['icon'], '/') !== false): ?>
                            <img src="<?php echo $item['icon']; ?>" style="width:100%; height:100%; object-fit:contain; border-radius:50%;">
                        <?php else: ?>
                            <i class="fa-solid <?php echo $item['icon']; ?>"></i>
                        <?php endif; ?>
                    </div>
                    <div class="item-name"><?php echo $item['name']; ?> (x<?php echo $item['count']; ?>)</div>
                    <div class="item-desc"><?php echo $item['description']; ?></div>
                    <div class="item-price"><?php echo ($item['price']==0)?'GRÁTIS':$item['price'].' Moedas'; ?></div>
                    <button class="buy-btn" <?php echo $isSoldOut?'disabled':''; ?> onclick="document.getElementById('buy_id_input').value=<?php echo $item['id']; ?>; document.getElementById('shopForm').submit();">
                        <?php echo $isSoldOut?'ESGOTADO':'COMPRAR'; ?>
                    </button>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>