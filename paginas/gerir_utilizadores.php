<?php
require_once 'config.php';

// Verificar se é admin
if(!$isLoggedIn || $user_type != 'administrador') {
    header("Location: index.php");
    exit;
}

// Processar ações
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['acao'])) {
    $id = intval($_POST['id_utilizador']);
    $acao = $_POST['acao'];
    
    switch($acao) {
        case 'ativar':
            $sql = "UPDATE utilizador SET estado = 'ativo' WHERE id_utilizador = ?";
            break;
        case 'desativar':
            $sql = "UPDATE utilizador SET estado = 'inativo' WHERE id_utilizador = ?";
            break;
        case 'remover':
            $sql = "DELETE FROM utilizador WHERE id_utilizador = ? AND tipo_utilizador != 'administrador'";
            break;
        default:
            $_SESSION['erro'] = "Ação inválida";
            header("Location: gerir_utilizadores.php");
            exit;
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if($stmt->execute()) {
        $_SESSION['sucesso'] = "Operação realizada com sucesso";
    } else {
        $_SESSION['erro'] = "Erro ao executar operação: " . $conn->error;
    }
    
    header("Location: gerir_utilizadores.php");
    exit;
}

// Obter lista de utilizadores
$sql = "SELECT id_utilizador, nome, email, tipo_utilizador, estado, 
        DATE_FORMAT(data_registo, '%d/%m/%Y %H:%i') as data_registo_formatada
        FROM utilizador
        ORDER BY data_registo DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="style.css">
    <title>Gerir Utilizadores - FelixBus</title>
    <style>
        .estado-ativo { color: var(--cor-sucesso); }
        .estado-inativo { color: var(--cor-alerta); }
        .estado-pendente { color: var(--cor-destaque); }
        
        .acoes-utilizador {
            display: flex;
            gap: 0.5rem;
        }
        
        .acoes-utilizador form {
            margin: 0;
        }
    </style>
</head>
<body>

	<div class="cabecalho">
		<h2><a href="index.php">FelixBus</a></h2>
	</div>
    
	<div class="menu">
		<ul>
			<?php foreach($menuItems as $item): ?>
				<?php if($item['text'] == 'Saldo' && $isLoggedIn): ?>
					<li class="saldo-menu">
						<a href="carteira.php">
								Saldo: €<?php echo number_format($saldo, 2); ?>
						</a>
					</li>
				<?php else: ?>
					<li><a href="<?php echo $item['link']; ?>"><?php echo $item['text']; ?></a></li>
				<?php endif; ?>
			<?php endforeach; ?>
		</ul>
	</div>

	<div class="formulario">
        <form action="menu_admin.php" method="get">
			<button type="submit" class="btn-submit">Voltar</button>
		</form>
    </div>
    <div class="dados">
        <h2>Gerir Utilizadores</h2>
        
        <?php if(isset($_SESSION['sucesso'])): ?>
            <div class="mensagem-sucesso"><?= $_SESSION['sucesso'] ?></div>
            <?php unset($_SESSION['sucesso']); ?>
        <?php endif; ?>
        
        <?php if(isset($_SESSION['erro'])): ?>
            <div class="mensagem-erro"><?= $_SESSION['erro'] ?></div>
            <?php unset($_SESSION['erro']); ?>
        <?php endif; ?>
        <div class="tabela-container">
			<table class="tabela-utilizadores">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>Email</th>
            <th>Tipo</th>
            <th>Estado</th>
            <th>Registado em</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php while($user = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $user['id_utilizador'] ?></td>
            <td><?= htmlspecialchars($user['nome']) ?></td>
            <td><?= htmlspecialchars($user['email']) ?></td>
            <td><?= ucfirst($user['tipo_utilizador']) ?></td>
            <td class="estado-<?= $user['estado'] ?>">
                <?= ucfirst($user['estado']) ?>
            </td>
            <td><?= $user['data_registo_formatada'] ?></td>
            <td>
                <div class="acoes-utilizador">
                    <?php if($user['estado'] != 'ativo'): ?>
                        <form method="POST">
                            <input type="hidden" name="id_utilizador" value="<?= $user['id_utilizador'] ?>">
                            <input type="hidden" name="acao" value="ativar">
                            <button type="submit" class="btn-pequeno btn-sucesso">Ativar</button>
                        </form>
                    <?php endif; ?>
                    
                    <?php if($user['estado'] != 'inativo' && $user['tipo_utilizador'] != 'administrador'): ?>
                        <form method="POST">
                            <input type="hidden" name="id_utilizador" value="<?= $user['id_utilizador'] ?>">
                            <input type="hidden" name="acao" value="desativar">
                            <button type="submit" class="btn-pequeno btn-alerta">Desativar</button>
                        </form>
                    <?php endif; ?>
                    
                    <?php if($user['tipo_utilizador'] != 'administrador'): ?>
                        <form method="POST" onsubmit="return confirm('Tem certeza que deseja remover este utilizador?');">
                            <input type="hidden" name="id_utilizador" value="<?= $user['id_utilizador'] ?>">
                            <input type="hidden" name="acao" value="remover">
                            <button type="submit" class="btn-pequeno btn-perigo">Remover</button>
                        </form>
                    <?php endif; ?>
                </div>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>
		</div>
    </div>
</body>
</html>