<?php
require_once 'config.php';

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
		<div class="menu">
			<ul>
				<?php foreach($menuItems as $item): ?>
				
					<!--
					* Estrutura condicional para item de Saldo
					* So mostra se o utilizador estiver autenticado ($isLoggedIn)
					-->
					
					<?php if($item['text'] == 'Saldo' && $isLoggedIn): ?>
						<li class="saldo-menu">
							<a href="carteira.php">
								Saldo: €<?php echo number_format($saldo, 2); ?>
							</a>
						</li>
					<?php else: ?>
					
						<!--
						* Loop padrao para outros itens de menu
						* $item['link'] vem da funcao conceberMenuItems()
						* $item['text'] e o texto exibido no link
						-->
						
						<li><a href="<?php echo $item['link']; ?>"><?php echo $item['text']; ?></a></li>
					<?php endif; ?>
				<?php endforeach; ?>
			</ul>
		</div>
		
		<footer>
				<p>Localização: Rua do Autocarro, 123</p>
				<p>Contacto: 123 456 789 | email@felixbus.com</p>
				<p>Horário: 8h-20h (Seg-Sab)</p>
		</footer>
	</body>
</html>
