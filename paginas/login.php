<?php
require_once 'config.php';

// Processar formulário de login apenas se for um pedido POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['username'];
    $password = $_POST['password'];

    // Consulta SQL (sem hash) para verificar credenciais
    $sql = "SELECT id_utilizador, tipo_utilizador FROM utilizador 
            WHERE email = '$email' AND password = '$password' AND estado = 'ativo'";
    $result = $conn->query($sql);

	// Se encontrar exatemente 1 resultado, inicia a sessao
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['id_utilizador'];
        $_SESSION['user_type'] = $user['tipo_utilizador'];
        header("Location: index.php"); // Redireciona para a pagina inicial
        exit(); // Termina a execucao
    } else {
        $login_error = "Email, senha incorretos ou conta inativa!"; // Mensagem de erro
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
<head>
	<meta charset="UTF-8">
	<title>Login - FelixBus</title>
	<link rel="stylesheet" href="style.css">
</head>
<body>
	<div class="cabecalho">
		<h2><a href="index.php">FelixBus</a></h2>
	</div>

	<div class="menu">
		<ul>
			<?php foreach($menuItems as $key => $item): ?>
				<li <?php echo isset($item['class']) ? 'class="' . $item['class'] . '"' : ''; ?>>
					<a href="<?php echo $item['link']; ?>"><?php echo $item['text']; ?></a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>

	<main class="formulario">
		<h2>LOGIN</h2>
		<?php if (isset($login_error)): ?>
            <p class="error"><?php echo $login_error; ?></p>
        <?php endif; ?>
		<form method="POST" action="login.php">
			<input type="text" name="username" placeholder="Email" required>
			<input type="password" name="password" placeholder="Password" required>
			<button type="submit">Entrar</button>
		</form>
		<p>Novo utilizador <a href="registo.php">Crie uma conta aqui.</a></p>
	</main>

	<footer>
		<p>Localizacao: Rua do Autocarro, 123</p>
		<p>Contacto: geral@felixbus.com</p>
	</footer>
</body>
</html>
