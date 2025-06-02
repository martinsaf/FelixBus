<?php
// config.php, arquivo base para todos os outros

// Inicia o sistema de sessoes
session_start();


// Configurações da base de dados
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'felixbus_db';


/*
(Objecto mysqli) Inicia uma conexao persistente com MySQL usando credenciais definidas
Conexao com a base de dados
*/ 
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);


// Verifica se houve erro na conexao com a base de dados
if($conn->connect_error){
	/*
	Encerra a execucao do script (die()) e exibe mensagem de erro contendo:
		- Descricao do erro (connect_error)
		- Detalhes especificos do MySQL
	*/ 
	die("Erro de conexao: " . $conn->connect_error);
}



//Verifica se o utilizador esta autenticado
$isLoggedIn = isset($_SESSION['user_id']);


//Inicializa a variaveis comuns
$saldo=0.00;
$user_id = null;
$user_type = null;
$username = '';

// Se o utilizador estiver autenticado, procura informacoes adicionais
if ($isLoggedIn) {
	$user_id = $_SESSION['user_id'];
	$user_type = $_SESSION['user_type'] ?? null;
	$username = $_SESSION['username'] ?? '';
	
	// Procura o saldo
	$saldo_query = "SELECT saldo FROM carteira WHERE id_utilizador = $user_id";
	$result = $conn->query($saldo_query);
	if($result && $result->num_rows > 0){
		$saldo = $result->fetch_assoc()['saldo'];
	}
	
	// Se nao tem username na sessao, procura do banco
	if(empty($username)){
		$user_query = "SELECT nome FROM utilizador WHERE id_utilizador = $user_id";
		$result = $conn->query($user_query);
		if($result && $result->num_rows > 0){
			$_SESSION['username'] = $result->fetch_assoc()['nome'];
			$username = $_SESSION['username'];
		}
	}
}

// Funcao para gerar intens de menu baseados no tipo de utilizador
function conceberMenuItems($isLoggedIn, $user_type, $saldo){
	$items = [
        'rotas' => ['text' => 'Rotas', 'link' => 'rotas.php'],
        'bilhetes' => ['text' => 'Bilhetes', 'link' => 'bilhetes.php'],
        'viagens' => ['text' => 'Viagens', 'link' => 'viagens.php']
    ];
    
    if (!$isLoggedIn) {
        $items['login'] = ['text' => 'Login', 'link' => 'login.php'];
        $items['registo'] = ['text' => 'Registar', 'link' => 'registo.php'];
    } else {
        $items['perfil'] = ['text' => 'Perfil', 'link' => 'perfil.php'];
        $items['logout'] = ['text' => 'Logout', 'link' => 'logout.php'];

        // Itens para funcionários e administradores
        if ($user_type == 'funcionario' || $user_type == 'administrador') {
            $items['comprar_cliente'] = ['text' => 'Comprar para Cliente', 'link' => 'comprar_cliente.php'];
        }
        
        // Itens específicos para administradores
        if ($user_type == 'administrador') {
            $items['admin'] = ['text' => 'Admin', 'link' => 'menu_admin.php'];
        }
		
		// Item de saldo
        if($user_type == 'cliente' || $user_type == 'funcionario' || $user_type == 'administrador') {
            $items['saldo'] = [
                'text' => 'Saldo: €' . number_format($saldo, 2), 
                'link' => 'carteira.php',
                'class' => 'saldo-menu'
            ];
        }
    }
    
    return $items;
}

// Gera os itens do menu para esta sesao
$menuItems = conceberMenuItems($isLoggedIn, $user_type, $saldo);

// Mapeamento de dias da semana
function getDiaSemana($data){
	$dias_map = [1 => 'Segunda', 2=> 'Terca', 3 => 'Quarta', 4 => 'Quinta', 5=> 'Sexta', 6 => 'Sabado', 7 => 'Domingo'];
	
	return $dias_map[date('N', strtotime($data))] ?? '';
	
}

?>