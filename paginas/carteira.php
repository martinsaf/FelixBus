<?php
require_once 'config.php';

// Obtém o total de transações
$total_transacoes_query = "SELECT COUNT(*) as total FROM transacao 
                         WHERE id_origem = $user_id OR id_destino = $user_id";
$total_result = $conn->query($total_transacoes_query);
$total_transacoes = $total_result->fetch_assoc()['total'];

// Configuração da paginação
$transacoes_por_pagina = 15;
$pagina_atual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina_atual - 1) * $transacoes_por_pagina;
$total_paginas = ceil($total_transacoes / $transacoes_por_pagina);

// Query para obter as transações da página atual
$historico_query = "SELECT * FROM transacao 
                  WHERE id_origem = $user_id OR id_destino = $user_id
                  ORDER BY dataOperacao DESC
                  LIMIT $offset, $transacoes_por_pagina";
$historico_result = $conn->query($historico_query);

// Processa recarga
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['recarregar'])){
    $valor = floatval($_POST['valor']);
    
    if($user_id != 1) {
        // Cliente normal recarregando
        $novo_saldo_cliente = $saldo + $valor;
        $update_cliente = "UPDATE carteira SET saldo = $novo_saldo_cliente WHERE id_utilizador = $user_id";
        
        $update_felixbus = "UPDATE carteira SET saldo = saldo + $valor WHERE id_utilizador = 1";
        
        if($conn->query($update_cliente) && $conn->query($update_felixbus)){
            $transacao_query = "INSERT INTO transacao
                              (id_origem, id_destino, valor, descricao)
                              VALUES ($user_id, 1, $valor, 'Recarga de saldo')";
            $conn->query($transacao_query);
            
            $auditoria_query = "INSERT INTO auditoria
                              (id_utilizador, tipo_operacao, valor)
                              VALUES ($user_id, 'RECARGA', $valor)";
            $conn->query($auditoria_query);

            $saldo = $novo_saldo_cliente;
        }
    } else {
        // FelixBus recarregando diretamente
        $update_felixbus = "UPDATE carteira SET saldo = saldo + $valor WHERE id_utilizador = 1";
        if($conn->query($update_felixbus)) {
            $auditoria_query = "INSERT INTO auditoria
                              (id_utilizador, tipo_operacao, valor)
                              VALUES (1, 'DEPOSITO_FELIXBUS', $valor)";
            $conn->query($auditoria_query);
        }
    }

    header("Location: carteira.php");
    exit();
}

// Processa levantamento
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['levantar'])){
    $valor = floatval($_POST['valor']);

    if($valor <= $saldo){
        if($user_id != 1) {
            // Cliente normal levantando
            $novo_saldo_cliente = $saldo - $valor;
            $update_cliente = "UPDATE carteira SET saldo = $novo_saldo_cliente WHERE id_utilizador = $user_id";
            
            $update_felixbus = "UPDATE carteira SET saldo = saldo - $valor WHERE id_utilizador = 1";
            
            if($conn->query($update_cliente) && $conn->query($update_felixbus)){
                $transacao_query = "INSERT INTO transacao 
                                  (id_origem, id_destino, valor, descricao) 
                                  VALUES (1, $user_id, $valor, 'Levantamento de saldo')";
                $conn->query($transacao_query);

                $auditoria_query = "INSERT INTO auditoria
                                  (id_utilizador, tipo_operacao, valor)
                                  VALUES ($user_id, 'LEVANTAMENTO', $valor)";
                $conn->query($auditoria_query);

                $saldo = $novo_saldo_cliente;
            }
        } else {
            // FelixBus levantando - pode levantar qualquer valor disponível
            $update_felixbus = "UPDATE carteira SET saldo = saldo - $valor WHERE id_utilizador = 1";
            
            if($conn->query($update_felixbus)) {
                $auditoria_query = "INSERT INTO auditoria
                                  (id_utilizador, tipo_operacao, valor)
                                  VALUES (1, 'LEVANTAMENTO_FELIXBUS', $valor)";
                $conn->query($auditoria_query);
            }
        }
    } else {
        $_SESSION['erro'] = "Saldo insuficiente para este levantamento.";
    }

    header("Location: carteira.php");
    exit();
}

// Restante do código...
?>
	
<!DOCTYPE html>
<html>
	<head>
		<link rel="stylesheet" href="style.css" />
		<title>Minha Carteira</title>
		 <style>
       .historico-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
}

.contentor-transacoes {
    margin-bottom: 30px;
    background-color: white;
    border-radius: 8px;
    padding: 0;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    border: 1px solid #e0e0e0;
    overflow: hidden;
}

.contentor-header {
    background-color: var(--cor-primaria);
    padding: 12px 15px;
    color: white;
    font-weight: bold;
    font-size: 1.1em;
}

.transacoes-lista {
    max-height: 600px;
    overflow-y: auto;
}

.transacao {
    display: flex;
    justify-content: space-between;
    padding: 12px 15px;
    border-bottom: 1px solid #f0f0f0;
    align-items: center;
}

.transacao:last-child {
    border-bottom: none;
}

.transacao:hover {
    background-color: #f9f9f9;
}

.transacao-dados {
    flex-grow: 1;
}

.transacao-data {
    font-size: 0.8em;
    color: var(--cor-texto-secundario);
}

.transacao-descricao {
    margin-top: 5px;
    font-size: 0.95em;
}

.transacao-valor {
    font-weight: bold;
    min-width: 80px;
    text-align: right;
    padding-left: 10px;
}

.transacao-valor.positivo {
    color: var(--cor-sucesso);
}

.transacao-valor.negativo {
    color: var(--cor-alerta);
}

/* Paginação */
.paginacao {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 5px;
    margin-top: 20px;
    padding: 15px;
    background-color: #f8f9fa;
    border-top: 1px solid #e0e0e0;
}

.pagina-link {
    padding: 8px 12px;
    background-color: #f0f0f0;
    border-radius: 4px;
    text-decoration: none;
    color: var(--cor-texto);
    transition: all 0.3s;
    font-size: 0.9em;
}

.pagina-link:hover {
    background-color: #e0e0e0;
    transform: translateY(-1px);
}

.pagina-link.ativo {
    background-color: var(--cor-primaria);
    color: white;
    font-weight: bold;
}

.paginacao span {
    padding: 8px;
    color: var(--cor-texto-secundario);
}

/* Responsividade */
@media (max-width: 600px) {
    .paginacao {
        flex-wrap: wrap;
    }
    
    .transacao {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .transacao-valor {
        margin-top: 5px;
        text-align: left;
        padding-left: 0;
    }
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
			<ul>
		</div>
		<div class="formulario">
			<h2>Saldo Atual: €<?= number_format($saldo, 2) ?></h2>
		
			<?php if(isset($erro)): ?>
                <p class="erro"><?php echo $erro; ?></p>
            <?php endif; ?>

			<div class="operacoes">
                <div>
                    <h3>Recarregar Saldo</h3>
                    <form method="POST">
                        <input type="number" name="valor" min="5" step="0.01" required>
                        <button type="submit" name="recarregar">Recarregar</button>
                    </form>
                </div>
                
                <div>
                    <h3>Levantar Saldo</h3>
                    <form method="POST">
                        <input type="number" name="valor" min="1" step="0.01" max="<?= $saldo ?>" required>
                        <button type="submit" name="levantar">Levantar</button>
                    </form>
                </div>
            </div>
        
			    <div class="historico-container">
    <h3>Histórico de Transações</h3>
    
    <?php if($historico_result && $historico_result->num_rows > 0): ?>
        <div class="contentor-transacoes">
            <div class="contentor-header">
                Página <?= $pagina_atual ?> (Transações <?= $offset+1 ?> a <?= min($offset + $transacoes_por_pagina, $total_transacoes) ?> de <?= $total_transacoes ?>)
            </div>
            
            <div class="transacoes-lista">
                <?php while($transacao = $historico_result->fetch_assoc()): ?>
                    <div class="transacao">
                        <div class="transacao-dados">
                            <div class="transacao-data"><?= date('d/m/Y H:i', strtotime($transacao['dataOperacao'])) ?></div>
                            <div class="transacao-descricao">
                                <?php 
                                    if($transacao['id_destino'] == $user_id) {
                                        echo 'Recebido de '.getNomeUtilizador($conn, $transacao['id_origem']);
                                    } else {
                                        echo 'Enviado para '.getNomeUtilizador($conn, $transacao['id_destino']);
                                    }
                                ?>
                            </div>
                        </div>
                        <div class="transacao-valor <?= $transacao['id_destino'] == $user_id ? 'positivo' : 'negativo' ?>">
                            <?= ($transacao['id_destino'] == $user_id ? '+' : '-') ?>
                            €<?= number_format($transacao['valor'], 2) ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
            
            <!-- Controles de paginação -->
            <div class="paginacao">
                <?php if($pagina_atual > 1): ?>
                    <a href="carteira.php?pagina=<?= $pagina_atual-1 ?>" class="pagina-link">&laquo; Anterior</a>
                <?php endif; ?>
                
                <?php 
                // Mostrar até 5 links de página ao redor da atual
                $inicio = max(1, $pagina_atual - 2);
                $fim = min($total_paginas, $pagina_atual + 2);
                
                if($inicio > 1) echo '<span>...</span>';
                
                for($i = $inicio; $i <= $fim; $i++): ?>
                    <a href="carteira.php?pagina=<?= $i ?>" class="pagina-link <?= $i == $pagina_atual ? 'ativo' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; 
                
                if($fim < $total_paginas) echo '<span>...</span>';
                ?>
                
                <?php if($pagina_atual < $total_paginas): ?>
                    <a href="carteira.php?pagina=<?= $pagina_atual+1 ?>" class="pagina-link">Próxima &raquo;</a>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <p>Nenhuma transação encontrada</p>
    <?php endif; ?>
</div>
			<footer>localizacao, contactos, horarios de funcionamento</footer>
</body>
</html>




<?php
// Função auxiliar para obter nome do utilizador
function getNomeUtilizador($conn, $id) {
    if($id == 1) return 'FelixBus';
    
    $query = "SELECT nome FROM utilizador WHERE id_utilizador = $id";
    $result = $conn->query($query);
    return $result && $result->num_rows > 0 ? $result->fetch_assoc()['nome'] : 'Utilizador #'.$id;
}
?>