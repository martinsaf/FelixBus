<?php
require_once 'config.php';

// Verificar se é admin
if(!$isLoggedIn || $user_type != 'administrador') {
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="style.css">
    <title>Menu Administrador - FelixBus</title>
    <style>
       
    </style>
</head>
<body>
    <div class="cabecalho">
        <h2><a href="index.php">FelixBus</a></h2>
    </div>
    <div class="menu">
        <ul>
            <?php foreach($menuItems as $item): ?>
                <?php if(isset($item['class']) && $item['class'] == 'saldo-menu'): ?>
                    <li class="saldo-menu">
                        <a href="<?= $item['link'] ?>">
                            <?= $item['text'] ?>
                        </a>
                    </li>
                <?php else: ?>
                    <li><a href="<?= $item['link'] ?>"><?= $item['text'] ?></a></li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ul>
    </div>
    
    <div class="dados">
        <h2>Menu Administrador</h2>
        
        <div class="admin-container">
            <div class="admin-card">
                <a href="gerir_utilizadores.php">
                    <h3>Gerir Utilizadores</h3>
                    <p>Ativar, desativar ou remover utilizadores do sistema</p>
                </a>
            </div>
            
            <div class="admin-card">
                <a href="">
                    <h3>Gerir Rotas</h3>
                    <p>Adicionar ou editar rotas disponíveis</p>
                </a>
            </div>
            
            <div class="admin-card">
                <a href="#">
                    <h3>Gerir Viagens</h3>
                    <p>Programar horários e viagens</p>
                </a>
            </div>
            
            <div class="admin-card">
                <a href="#">
                    <h3>Promoções</h3>
                    <p>Criar e gerir promoções especiais</p>
                </a>
            </div>
            
            <div class="admin-card">
                <a href="#">
                    <h3>Relatórios</h3>
                    <p>Visualizar relatórios do sistema</p>
                </a>
            </div>
            
            <div class="admin-card">
                <a href="#">
                    <h3>Configurações</h3>
                    <p>Configurar parâmetros do sistema</p>
                </a>
            </div>
        </div>
    </div>
    
    <footer>localizacao, contactos, horarios de funcionamento</footer>
</body>
</html>