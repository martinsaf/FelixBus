
<?php
require_once 'config.php';

$message = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    // Obter e sanitizar os dados
    $nome = $conn->real_escape_string($_POST['nome'] ?? '');
    $email = $conn->real_escape_string($_POST['email'] ?? '');
    $password = $conn->real_escape_string($_POST['password'] ?? '');
    
    // Validação dos campos
    if(empty($nome) || empty($email) || empty($password)) {
        $message = "Todos os campos são obrigatórios!";
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Por favor, insira um email válido!";
    } else {
        // Verificar se o email já existe
        $check_email = "SELECT id_utilizador FROM utilizador WHERE email = '$email'";
        $result = $conn->query($check_email);
        
        if($result->num_rows > 0) {
            $message = "Este email já está registado!";
        } else {
            // Inserir Utilizador
            $insert_query = "INSERT INTO utilizador (nome, email, password, tipo_utilizador)
                            VALUES ('$nome', '$email', '$password', 'cliente')";
                            
            if($conn->query($insert_query)) {
                $user_id = $conn->insert_id;
                
                // Criar carteira
                $carteira_query = "INSERT INTO carteira (id_utilizador, saldo)
                                  VALUES ($user_id, 0.00)";
                
                if($conn->query($carteira_query)) {
                    $message = "Registo bem-sucedido!";
                    $_SESSION['id_utilizador'] = $user_id;
                    header("Location: index.php");
                    exit();
                } else {
                    $message = "Erro ao criar carteira: " . $conn->error;
                    // Rollback
                    $conn->query("DELETE FROM utilizador WHERE id_utilizador = $user_id");
                }
            } else {
                $message = "Erro no registo: " . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="style.css" />
    <title>Registo - FelixBus</title>
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
        <h2>REGISTO</h2>
        <?php if(!empty($message)): ?>
            <div class="mensagem-erro"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="registo.php">
            <input type="email" name="email" placeholder="Email" required>
			<input type="nome" name="nome" placeholder="Nome" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Registar</button>
        </form>
        <p>Já está registado? Faça o login <a href="login.php">Aqui!</a></p>
    </div>
    
    <footer>localizacao, contactos, horarios de funcionamento</footer>
</body>
</html>