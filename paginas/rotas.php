<?php
require_once 'config.php';

if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['origem']) && isset($_POST['destino']) && isset($_POST['data'])){
	$_SESSION['pesquisa_rota'] = [
		'origem' => htmlspecialchars($_POST['origem']),
		'destino' => htmlspecialchars($_POST['destino']),
		'data' => htmlspecialchars($_POST['data'])
	];
	
	header("Location: horarioCompra.php");
	exit;
}

?>

<!DOCTYPE html>
<html>
	<head>
		<link rel="stylesheet" href="style.css" />
		<title>FelixBus</title>
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
		<div  class="formulario"> //Apos introduzir o origem, destino e data - o utilizador deve ser redirecionado para horarioCompra.php
			<form method="POST" action="rotas.php">
				<div>
					<label for="destino">Destino</label>
					<input type="text" id="destino" name="destino" required>
				</div>
				<div>
					<label for="origem">Origem</label>
					<input type="text" id="origem" name="origem" required>
				</div>
				<div>
					<label for="data">Partida</label>
					<input type="date" id="data" name="data" required>
				</div>
				<button type="submit">Submeter</button>
			</form>
		</div>
		<?php
			if($user_type == 'administrador'){
				echo '<div class="formulario">';
				echo '<form action="adicionar_rota.php" method="get">';
				echo '<button type="submit" class="btn-submit">Adicionar Nova Rota</button>';
				echo '</form>';
				echo '</div>';
			}
		?>
		
		<footer>localizacao, contactos, horarios de funcionamento</footer>
	</body>
</html>