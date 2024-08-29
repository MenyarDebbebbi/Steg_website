<?php
session_start();
include 'db_connect.php'; // Inclure la connexion à la base de données

// Réinitialiser les erreurs précédentes
$_SESSION['error'] = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $matricule = trim($_POST['matricule']);

    // Vérifie si les champs sont non vides
    if (empty($username) || empty($matricule)) {
        $_SESSION['error'] = "Veuillez remplir tous les champs.";
        header("Location: connexion.html");
        exit();
    }

    // Récupération de l'utilisateur depuis la base de données
    $query = "SELECT * FROM utilisateurs WHERE nom = :nom";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':nom', $username);
    $stmt->execute();

    // Si l'utilisateur existe
    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        // Vérification du mot de passe en texte clair
        if ($matricule === $user['matricule']) {
            // Connexion réussie
            $_SESSION['id'] = $user['id'];
            $_SESSION['nom'] = $user['nom'];
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
