<?php
session_start(); // Démarrer la session

// Détruire toutes les variables de session
$_SESSION = array();

// Si vous utilisez un cookie pour la session, le supprimer
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
}

// Détruire la session
session_destroy();

// Rediriger l'utilisateur vers la page de connexion ou d'accueil
header("Location: connexion.html");
exit();