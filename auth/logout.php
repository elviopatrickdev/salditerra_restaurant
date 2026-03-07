<?php
session_start(); // Inicio da sessão
session_unset(); // Limpa todas as variáveis de sessão
session_destroy(); // Destroi a sessão
header('Location: ../public/login-register.php'); // Redireciona para a página inicial
exit;
