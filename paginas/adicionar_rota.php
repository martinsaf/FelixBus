<?php
require_once 'config.php';

// Variavel para armazenar mensagens de sucesso/erro
$message = "";

/*
Processamento do formulario (metodo POST):
	- Executado quando o formulario e submetido
*/
if($_SERVER["REQUEST_METHOD"] == "POST") {
	
    // Dados da rota principal
    $origem = $conn->real_escape_string($_POST['origem']);
    $destino = $conn->real_escape_string($_POST['destino']);
    $distancia = floatval($_POST['distancia']);
	
	// Converte a duracao de minutos para formato HH:MM:00
    $duracao_minutos = intval($_POST['duracao']);
    $duracao_time = sprintf('%02d:%02d:00', floor($duracao_minutos / 60), $duracao_minutos % 60);
    $preco = floatval($_POST['preco']);
    
    // Obter numero de paragens intermediarias
    $num_paragens = isset($_POST['num_paragens']) ? intval($_POST['num_paragens']) : 0;
    
    // Iniciar transação para garantir integridade dos dados
    $conn->begin_transaction();
    
    try {
        
		/*
		1. Insercao da rota principal:
			- Insere os dados basicos da rota na tabela 'rota'
			- Usa o formato de tempo correto para duracao_estimada
		*/
        $sql = "INSERT INTO rota (origem, destino, distancia, duracao_estimada, preco_base) 
                VALUES ('$origem', '$destino', $distancia, '$duracao_time', $preco)";
        
        if(!$conn->query($sql)) {
            throw new Exception("Erro ao adicionar rota: " . $conn->error);
        }
        
		// Obtem o ID da rota recem-inserida
        $id_rota = $conn->insert_id;
        
        /*
		2. Insercao das paragens intermediarias:
			- Para cada paragem definida no formulario
			- Insere na tabela 'paragem' com a ordem correta
		*/
        if($num_paragens > 0) {
            for($i = 0; $i < $num_paragens; $i++) {
                if(!empty($_POST['paragem_'.$i])) {
                    $nome_local = $conn->real_escape_string($_POST['paragem_'.$i]);
                    
                    $sql_paragem = "INSERT INTO paragem (id_rota, nome_local, ordem) 
                                   VALUES ($id_rota, '$nome_local', ".($i+1).")";
                    
                    if(!$conn->query($sql_paragem)) {
                        throw new Exception("Erro ao adicionar paragem: " . $conn->error);
                    }
                }
            }
        }
        
        /*
		3. Criacao das viagens:
			- Para cada dia da semana selecionados
			- Cria uma entrada na tabela 'viagem' com horario e capacidade padrao
		*/
        if(isset($_POST['dias_semana']) && is_array($_POST['dias_semana']) && !empty($_POST['dias_semana'])) {
            $hora_partida = $_POST['hora_partida'];
            
            foreach($_POST['dias_semana'] as $dia_semana) {
                $sql_viagem = "INSERT INTO viagem (id_rota, dia_semana, hora, preco, capacidade_max, estado)
                            VALUES ($id_rota, '$dia_semana', '$hora_partida', $preco, 50, 'ativa')";
                
                if(!$conn->query($sql_viagem)) {
                    throw new Exception("Erro ao criar viagem: " . $conn->error);
                }
            }
        } else {
            throw new Exception("Selecione pelo menos um dia da semana para criar viagens.");
        }
        
		// Confirma todas as operacoes se tudo correr sem erros
        $conn->commit();
        $message = "Rota criada com sucesso com viagens nos dias selecionados!";
    } catch (Exception $e) {
		// Em caso de erro, desfaz todas as operacoes de transacao
        $conn->rollback();
        $message = $e->getMessage();
    }
}

/*
Define o numero de campos de paragem a mostrar:
	- Mantem o valor submetido se houver Erro
	- Usa 3 como padrao inicial
*/
$num_paragens = isset($_POST['num_paragens']) ? intval($_POST['num_paragens']) : 3;
?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link rel="stylesheet" href="style.css" />
		<title>FelixBus</title>
	</head>
	<body>
	
		<!-- Cabecalho da pagina com link para a pagina inicial -->
		<div class="cabecalho">
			<h2><a href="index.php">FelixBus</a></h2>
		</div>
		
		<!-- Menu de navegacao -->
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
		
		<!-- Botao para voltar a pagina de rotas -->
		<div class="formulario">
			<form action="rotas.php" method="get">
				<button type="submit" class="btn-submit">Voltar</button>
			</form>
		</div>
		
		<!-- Formulario principal para adicionar nova rota -->
		<div class="formulario">
			<h2>Adicionar Nova Rota</h2>
			
			<!-- Exibe mensagens de sucesso/erro -->
			<?php if(!empty($message)): ?>
				<div class="<?php echo strpos($message, 'sucesso') !== false ? 'mensagem-sucesso' : 'mensagem-erro'; ?>">
					<?php echo $message; ?>
				</div>
			<?php endif; ?>

			<!-- Formulario do registo da rota -->
			<form method="POST">
				<input type="hidden" name="num_paragens" value="<?php echo $num_paragens; ?>">
				
				<!-- Secao: Informacoes basicas da rota -->
				<h3>Informações da Rota</h3>
				<div>
					<label for="origem">Origem:</label>
					<input type="text" id="origem" name="origem" value="<?php echo $_POST['origem'] ?? ''; ?>" required>
				</div>
				
				<div>
					<label for="destino">Destino:</label>
					<input type="text" id="destino" name="destino" value="<?php echo $_POST['destino'] ?? ''; ?>" required>
				</div>
				
				<div>
					<label for="distancia">Distância (km):</label>
					<input type="number" id="distancia" name="distancia" step="0.1" min="1" 
						   value="<?php echo $_POST['distancia'] ?? ''; ?>" required>
				</div>
				
				<div>
					<label for="duracao">Duração (minutos):</label>
					<input type="number" id="duracao" name="duracao" min="1" 
						   value="<?php echo $_POST['duracao'] ?? ''; ?>" required>
				</div>
				
				<div>
					<label for="preco">Preço Base (€):</label>
					<input type="number" id="preco" name="preco" step="0.01" min="0" 
						   value="<?php echo $_POST['preco'] ?? ''; ?>" required>
				</div>
				
				<!-- Seção: Paragens intermediárias -->
				<h3>Paragens Intermédias</h3>
				<?php for($i = 0; $i < $num_paragens; $i++): ?>
					<div>
						<label for="paragem_<?php echo $i; ?>">Paragem <?php echo $i+1; ?>:</label>
						<input type="text" id="paragem_<?php echo $i; ?>" name="paragem_<?php echo $i; ?>" 
							   value="<?php echo $_POST['paragem_'.$i] ?? ''; ?>">
					</div>
				<?php endfor; ?>
				
				<!-- Botão para adicionar mais campos de paragem -->
				<div style="margin-top: 1rem;">
					<button type="submit" name="action" value="add_paragem" class="btn-adicionar">
						+ Adicionar Mais Paragem
					</button>
				</div>
				
				<!-- Seção: Horários e dias de operação -->
				<h3>Horários da Rota</h3>
				<div>
					<label>Dias da semana:</label><br>
					<?php 
					$dias_semana = ['Segunda', 'Terca', 'Quarta', 'Quinta', 'Sexta', 'Sabado', 'Domingo'];
					foreach($dias_semana as $dia): 
						$checked = isset($_POST['dias_semana']) && in_array($dia, $_POST['dias_semana']) ? 'checked' : '';
					?>
						<input type="checkbox" id="dia_<?php echo $dia; ?>" name="dias_semana[]" 
							value="<?php echo $dia; ?>" <?php echo $checked; ?>>
						<label for="dia_<?php echo $dia; ?>"><?php echo $dia; ?></label><br>
					<?php endforeach; ?>
				</div>

				<div>
					<label for="hora_partida">Hora de partida:</label>
					<input type="time" id="hora_partida" name="hora_partida" 
						value="<?php echo $_POST['hora_partida'] ?? '08:00'; ?>" required>
				</div>
				
				<!-- Botão para submeter o formulário -->
				<button type="submit" class="btn-submit">Salvar Rota</button>
			</form>
		</div>
	</body>
</html>