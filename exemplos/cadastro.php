<?php

$login = $_POST["login"];
$senha = MD5($_POST["senha"]);
echo $login;
echo $senha;
// echo phpinfo();
$host = '127.0.0.1'; // ou 'localhost'
$db   = 'gestao-de-perfil-de-usuario';
$user = 'root';
$pass = 'senac';
$charset = 'utf8mb4'; // Recomendado para suporte completo a caracteres (incluindo emojis)

// Configurando o DSN (Data Source Name)
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$pdo = new PDO($dsn, $user, $pass);
    echo "Conexão bem-sucedida com o banco de dados usando PDO! <br>";

    $sql = "SELECT id_usuario, nome_completo, email FROM usuario LIMIT 5";
    $stmt = $pdo->query($sql);
    
    // Pega todos os resultados e guarda em um array
    $usuarios = $stmt->fetchAll(); 
    
    // Percorre o array para exibir na tela
    foreach ($usuarios as $usuario) {
        echo "ID: " . $usuario['id_usuario'] . " | Nome: " . $usuario['nome_completo'] . " | Email: " . $usuario['email'] . "<br>";
    }
// $db = mysql_select_db("nome_do_banco_de_dados");
// $query_select = "SELECT login FROM usuarios WHERE login = "$login"";
// $select = mysql_query($query_select,$connect);
// $array = mysql_fetch_array($select);
// $logarray = $array["login"];

//   if($login == "" || $login == null){
//     echo"<script language="javascript" type="text/javascript">
//     alert("O campo login deve ser preenchido");window.location.href="
//     ../index.html";</script>";

//     }else{
//       if($logarray == $login){

//         echo"<script language="javascript" type="text/javascript">
//         alert("Esse login já existe");window.location.href="
//         ../index.html";</script>";
//         die();

//       }else{
//         $query = "INSERT INTO usuarios (login,senha) VALUES ("$login","$senha")";
//         $insert = mysql_query($query,$connect);

//         if($insert){
//           echo"<script language="javascript" type="text/javascript">
//           alert("Usuário cadastrado com sucesso!");window.location.
//           href="login.html"</script>";
//         }else{
//           echo"<script language="javascript" type="text/javascript">
//           alert("Não foi possível cadastrar esse usuário");window.location
//           .href="../index.html"</script>";
//         }
//       }
//     }
?>