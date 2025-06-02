<?php
require_once 'config.php';

// verificar se veio da pesquisa de rotas (dados estao na sessao)
if(!isset($_SESSION['pesquisa_rota'])){
	header("Location: rotas.php");
	exit;
}

/*
Obtem parametros da pesquisa armazenados na sessao
	-origem: local de partida selecionado
	-destino: local de chegada selecionado
	-data: data selecionada para a viagem 
	
*/
$origem = $_SESSION['pesquisa_rota']['origem'];
$destino = $_SESSION['pesquisa_rota']['destino'];
$data = $_SESSION['pesquisa_rota']['data'];

// Buscar funcao de config.php
$dia_semana = getDiaSemana($data);

/*
Consulta para obter horarios disponiveis:
	- Junta tabelas rota e viagem
	- Filtra por origem, destino, dia da semana e status ativo
	- Ordena por hora de partida
	- Calcula hora de chegada somando duracao estimada
*/
$query = "SELECT 
            R.id_rota, 
            R.origem,
            R.destino, 
            R.distancia, 
            R.duracao_estimada, 
            R.preco_base,
            V.id_viagem,
            V.hora as hora_partida, 
            ADDTIME(V.hora, R.duracao_estimada) as hora_chegada,
            V.capacidade_max,
            V.estado
          FROM rota R
          JOIN viagem V ON R.id_rota = V.id_rota
          WHERE R.origem = ? AND R.destino = ? AND V.dia_semana = ? AND V.estado = 'ativa'
          ORDER BY V.hora";

$stmt = $conn->prepare($query);
$stmt->bind_param("sss", $origem, $destino, $dia_semana);
$stmt->execute();
$result = $stmt->get_result();
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
		<div>
        <h2>Horarios disponiveis para <?php echo "$origem - $destino"; ?></h2>
        <p>Data: <?php echo date('d/m/Y', strtotime($data)); ?> (<?php echo $dia_semana; ?>)</p>
        
        <?php if(mysqli_num_rows($result) > 0): ?>
            <table class="tabela-horarios">
                <thead>
                    <tr>
                        <th>Partida</th>
                        <th>Chegada</th>
                        <th>Duracao</th>
                        <th>Preco</th>
                        <th>Acao</th>
                    </tr>
                </thead>
                <tbody>
				<?php
echo "<pre>Dados brutos do banco:\n";
while($rota = mysqli_fetch_assoc($result)) {
    print_r($rota);
    // Reseta o ponteiro do resultado para poder usar novamente
    mysqli_data_seek($result, 0);
    break; // Mostra apenas o primeiro resultado
}
echo "</pre>";
?>
					<?php while($rota = mysqli_fetch_assoc($result)): ?>
						<?php
							// Extrai horas, minutos e segundos da duracao
							list($h, $m, $s) = explode(':', $rota['duracao_estimada']);
							
							// Calcula hora de chegada corretamente (usando os valores reais da bd)
							$hora_partida = strtotime($rota['hora_partida']);
							$hora_chegada = date('H:i', $hora_partida + $h*3600 + $m*60 + $s);
							
							// Formata a duracao para exibição (HHh MMmin)
							$duracao_formatada = ($h > 0 ? $h.'h ' : '').$m.'min';
						?>
						<tr>
							<td><?php echo date('H:i', $hora_partida); ?></td>
							<td><?php echo $hora_chegada; ?></td>
							<td><?php echo $duracao_formatada; ?></td>
							<td>€<?php echo number_format($rota['preco_base'], 2); ?></td>
							<td>
								<form method="POST" action="processa_compra.php">
									<input type="hidden" name="id_viagem" value="<?php echo $rota['id_viagem']; ?>">
									<input type="hidden" name="id_rota" value="<?php echo $rota['id_rota']; ?>">
									<input type="hidden" name="data_viagem" value="<?php echo $data; ?>">
									<input type="hidden" name="hora_partida" value="<?php echo $rota['hora_partida']; ?>">
									<button type="submit" class="btn-comprar">Comprar</button>
								</form>
							</td>
						</tr>
					<?php endwhile; ?>
				</tbody>
            </table>
        <?php else: ?>
            <p class="sem-resultados">Nao ha horarios disponiveis para esta rota na data selecionada.</p>
            <a href="rotas.php" class="btn-voltar">Voltar a pesquisa</a>
        <?php endif; ?>
    </div>
		<footer>localizacao, contactos, horarios de funcionamento</footer>
	</body>
</html>