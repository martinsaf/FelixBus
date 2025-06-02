<?php
require_once 'config.php';

// Verifica se é funcionário
if(!$isLoggedIn){
    header("Location: login.php");
    exit();
}

$message = "";
$clientes = [];
$termo_pesquisa = "";
$mostrar_todos = true;

// Processar pesquisa de clientes
if($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['pesquisa'])){
    $termo_pesquisa = trim($conn->real_escape_string($_GET['pesquisa'] ?? ''));
    
    if(!empty($termo_pesquisa)) {
        $mostrar_todos = false;
        // Procurar clientes com base no termo de pesquisa
        $clientes_query = "SELECT id_utilizador, nome, email FROM utilizador 
                          WHERE tipo_utilizador = 'cliente' 
                          AND (nome LIKE '%$termo_pesquisa%' OR email LIKE '%$termo_pesquisa%')
                          ORDER BY nome";
        $clientes_result = $conn->query($clientes_query);
        
        if($clientes_result){
            $clientes = $clientes_result->fetch_all(MYSQLI_ASSOC);
        }
    }
}

// Se não houver termo de pesquisa ou pesquisa vazia, mostra todos os clientes
if($mostrar_todos) {
    $clientes_query = "SELECT id_utilizador, nome, email FROM utilizador 
                      WHERE tipo_utilizador = 'cliente' 
                      ORDER BY nome
                      LIMIT 50"; // Limita a 50 resultados
    $clientes_result = $conn->query($clientes_query);
    if($clientes_result){
        $clientes = $clientes_result->fetch_all(MYSQLI_ASSOC);
    }
}

// Processa compra
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['comprar'])){
    $cliente_id = $conn->real_escape_string($_POST['cliente_id']);
    $viagem_id = $conn->real_escape_string($_POST['viagem_id']);
    $quantidade = intval($_POST['quantidade']);
    $funcionario_id = $user_id; // ID do funcionário logado

    // Verifica se o cliente e a viagem existem
    $verifica_cliente = $conn->query("SELECT id_utilizador FROM utilizador WHERE id_utilizador = $cliente_id AND tipo_utilizador = 'cliente'");
    $verifica_viagem = $conn->query("SELECT id_viagem, preco FROM viagem WHERE id_viagem = $viagem_id AND estado = 'ativa'");

    if($verifica_cliente->num_rows == 1 && $verifica_viagem->num_rows == 1){
        $viagem = $verifica_viagem->fetch_assoc();
        $preco_total = $viagem['preco'] * $quantidade;
        $codigo_validacao = uniqid('BLT'); // Gera um código único para o bilhete

        // Insere o bilhete na base de dados
        $insere_bilhete = $conn->query("INSERT INTO bilhete (id_utilizador, id_viagem, codigo_validacao, preco, estado) 
                                      VALUES ($cliente_id, $viagem_id, '$codigo_validacao', $preco_total, 'valido')");

        if($insere_bilhete){
            $message = "Bilhete comprado com sucesso para o cliente! Código: $codigo_validacao";
        } else {
            $message = "Erro ao comprar bilhete: " . $conn->error;
        }
    } else {
        $message = "Cliente ou viagem inválidos!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Comprar para Cliente - FelixBus</title>
    <link rel="stylesheet" href="style.css">
    
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
        <h2>Comprar Bilhete para Cliente</h2>
        
        <?php if(!empty($message)): ?>
            <div class="mensagem <?php echo strpos($message, 'sucesso') !== false ? 'mensagem-sucesso' : 'mensagem-erro'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <div>
            <form method="GET" action="comprar_cliente.php">
                <label for="pesquisa"><strong>Pesquisar Cliente:</strong></label><br>
                <input type="text" name="pesquisa" id="pesquisa" 
                       value="<?php echo htmlspecialchars($termo_pesquisa); ?>" 
                       placeholder="Digite nome ou email do cliente">
                <button type="submit">Pesquisar</button>
                <?php if(!empty($termo_pesquisa)): ?>
                    <a href="comprar_cliente.php" style="margin-left: 10px;">Limpar pesquisa</a>
                <?php endif; ?>
            </form>
        </div>
        
        <div>
            <?php if(!empty($termo_pesquisa)): ?>
                <h3>Resultados para "<?php echo htmlspecialchars($termo_pesquisa); ?>":</h3>
            <?php else: ?>
                <h3>Todos os Clientes:</h3>
            <?php endif; ?>
            
            <?php if(empty($clientes)): ?>
                <p>Nenhum cliente encontrado.</p>
            <?php else: ?>
                <form method="POST" action="comprar_cliente.php" class="form-compra">
                    <ul class="lista-clientes">
                        <?php foreach($clientes as $cliente): ?>
                            <li class="cliente-item">
                                <input type="radio" name="cliente_id" id="cliente_<?php echo $cliente['id_utilizador']; ?>" 
                                       value="<?php echo $cliente['id_utilizador']; ?>" required>
                                <label for="cliente_<?php echo $cliente['id_utilizador']; ?>" class="cliente-info">
                                    <span class="cliente-nome"><?php echo htmlspecialchars($cliente['nome']); ?></span>
                                    <span class="cliente-email"><?php echo htmlspecialchars($cliente['email']); ?></span>
                                </label>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    
                    <div>
                        <label for="viagem_id">Selecione a Viagem:</label>
                        <select name="viagem_id" id="viagem_id" required>
                            <option value="">-- Selecione uma viagem --</option>
                            <?php
                            // Buscar viagens disponíveis
                            $viagens_query = "SELECT v.id_viagem, r.origem, r.destino, v.dia_semana, v.hora, v.preco
                 FROM viagem v
                 JOIN rota r ON v.id_rota = r.id_rota
                 WHERE v.estado = 'ativa'
                 ORDER BY v.dia_semana, v.hora";
                            $viagens_result = $conn->query($viagens_query);
                            
                            if($viagens_result && $viagens_result->num_rows > 0) {
                                while($viagem = $viagens_result->fetch_assoc()) {
                                    echo '<option value="' . $viagem['id_viagem'] . '">';
echo htmlspecialchars($viagem['origem'] . ' → ' . $viagem['destino'] . 
     ' (' . $viagem['dia_semana'] . ' ' . $viagem['hora'] . ') - €' . number_format($viagem['preco'], 2));
echo '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div>
                        <label for="quantidade">Quantidade de Bilhetes:</label>
                        <input type="number" name="quantidade" min="1" value="1" required>
                    </div>
                    
                    <button type="submit" name="comprar">Comprar Bilhete</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
    
    <footer>localizacao, contactos, horarios de funcionamento</footer>
</body>
</html>