<?php
require_once 'config.php';

/* 
Obtem dados do utilizador autenticado. 
Utiliza o $user_id que foi definido em config.php a partir da sessao
A query seleciona todos os campos (*) da tabela utilizador para o ID correspondente
*/
$query = "SELECT * FROM utilizador WHERE id_utilizador = $user_id";

/*
Executa uma consulta SQL (para o servidor MySQL, $conn de config.php) na base de dados 
para obter todos os dados (*) do utilizador
com o ID correspondente ao user_id da sessao 
Retorna um objeto mysqli_result que contem os resultados da consulta
*/
$result = $conn->query($query);

/*
Converte o resultado da consulta num array associativo onde:
	- As chaves sao os nomes das colunas da tabela (nome, email, ...)
	- Os valores sao os dados do utilizador
	fetch_assoc() retorna uma unica linha (ja que estamos a procurar por um ID unico)
Se nao houver resultados, retorna null
*/
$user = $result->fetch_assoc();

/*
Verificacao de seguranca:
	- se a query falhou (!$result) OU
	- Se nao encontrou resultados (num_rows == 0)
Redireciona para login.php para evitar acesso a dados invalidos
*/
if(!$result || $result->num_rows == 0){
	header("Location: config.php");
	exit();
}

//variavel para armazenar mensagens
$message = "";

/*
Processamento do formulario de atualizacao
So executa se o metodo de requisicao for POST
*/
if($_SERVER["REQUEST_METHOD"] == "POST"){
	
	/*
	Filtragem basica dos inputs do formulario
	real_escape_string previne alguns ataques de SQL injection
	Operador ?? fornece valor padrao (string vazia) se nao existir
	*/
	$nome = $conn->real_escape_string($_POST['nome']);
	$email = $conn->real_escape_string($_POST['email']);
	$telefone = $conn->real_escape_string($_POST['telefone'] ?? '');
	$data_nascimento = $conn->real_escape_string($_POST['data_nascimento'] ?? '');
	
	/*
	Query de atualizacao com os valores filtrados
	(A senha esta a ser atualiazada em plain text (inseguro))
	*/
	$update_query = "UPDATE utilizador SET
					nome='$nome',
					email='$email',
					telefone='$telefone',
					data_nascimento='$data_nascimento',
					password='$password'
					WHERE id_utilizador=$user_id";

	/*
	Executa a query de atualizacao, se bem sucedido:
		- Atualiza mensagem, neste caso sucesso
		- Atualiza o nome na sessao
		- Recarrega os dados da base de dados
	*/
	if($conn->query($update_query)){
		$message = "Dados atualizados com sucesso!";
		$_SESSION['username'] = $nome;
		// Recarrega dados
		$result = $conn->query($query);
		$user = $result->fetch_assoc();
	} else {
		// Em caso de erro, mostra a mensagem com detalhe do MySQL
		$message = "Erro ao atualizar: " . $conn->error;
	}
}
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
		<div class="cabecalho">
			<h2><a href="index.php">FelixBus</a></h2>
		</div>
		<!-- Menu dinamico gerado por config.php -->
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
		
		<!-- Formulario do perfil -->
		<div class='formulario'>
			<h2>Meu Perfil</h2>
        
			<!-- Exibe mensagens de sucesso/erro -->
			<?php if(!empty($message)): ?>
				<div class="message"><?php echo $message; ?></div>
			<?php endif; ?>
        
			<!-- Formulario de edicao -->
			<form method="POST" action="perfil.php">
			
				<!-- Campo Nome -->
				<div>
					<label for="nome">Nome:</label>
					<input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($user['nome']); ?>" required>
				</div>
				
				<!-- Campo Email -->
				<div>
					<label for="email">Email:</label>
					<input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
				</div>
				
				<!-- Campo telefone -->
				<div>
					<label for="telefone">Telefone:</label>
					<input type="text" id="telefone" name="telefone" value="<?php echo htmlspecialchars($user['telefone']); ?>">
				</div>
				
				<!-- Campo data de nascimento -->
				<div>
					<label for="data_nascimento">Data de nascimento:</label>
					<input type="date" id="data_nascimento" name="data_nascimento" value="<?php echo htmlspecialchars($user['data_nascimento']); ?>">
				</div>
				
				<!-- Campo Password -->
				<div>
					<label>Password:</label>
					<input type="password" id="password" name="password" value="<?php echo htmlspecialchars($user['password']); ?>" required>
				</div>
				
				<!-- Botao de submissao -->
				<button type="submit">Atualizar Dados</button>
			</form>
		</div>
		
		<!-- Rodape padrao -->
		<footer>
			<p>Localização: Rua do Autocarro, 123</p>
			<p>Contacto: 123 456 789 | email@felixbus.com</p>
			<p>Horário: 8h-20h (Seg-Sab)</p>
		</footer>
	</body>
</html>