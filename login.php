<?php
session_start();
include 'db_connect.php'; // Inclure la connexion à la base de données

// Réinitialiser les erreurs précédentes
$_SESSION['error'] = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Vérifie si les champs sont non vides
    if (empty($username) || empty($password)) {
        $_SESSION['error'] = "Veuillez remplir tous les champs.";
        header("Location: connexion.html");
        exit();
    }

    // Récupération de l'utilisateur depuis la base de données
    $query = "SELECT * FROM utilisateurs WHERE email = :email";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':email', $username);
    $stmt->execute();

    // Si l'utilisateur existe
    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        // Vérification du mot de passe en texte clair
        if ($password === $user['mot_de_passe']) {
            // Connexion réussie
            $_SESSION['id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            header("Location: repertoire.html"); // Redirection vers la page répertoire
            exit();
        } else {
            $_SESSION['error'] = "Mot de passe incorrect.";
        }
    } else {
        $_SESSION['error'] = "Utilisateur non trouvé.";
    }

    // Redirection vers la page de connexion avec message d'erreur
    header("Location: connexion.html");
    exit();
}
