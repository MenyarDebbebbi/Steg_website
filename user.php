<?php
include 'db_connect.php';
header('Content-Type: application/json');

// Fonction pour lire les utilisateurs
function readUsers()
{
    global $pdo;

    try {
        $query = "SELECT * FROM utilisateurs WHERE role = 'user'";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($users);
    } catch (Exception $e) {
        echo json_encode(['error' => 'Erreur lors de la récupération des utilisateurs']);
    }
}

// Fonction pour rechercher les utilisateurs
function searchUsers($searchTerm)
{
    global $pdo;

    try {
        $query = "SELECT * FROM utilisateurs WHERE (nom LIKE :searchTerm OR telephone LIKE :searchTerm) AND role = 'user'";
        $stmt = $pdo->prepare($query);
        $stmt->execute(['searchTerm' => '%' . $searchTerm . '%']);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($users);
    } catch (Exception $e) {
        echo json_encode(['error' => 'Erreur lors de la recherche des utilisateurs']);
    }
}

// Fonction pour créer un utilisateur
function createUser($data)
{
    global $pdo;

    try {
        // Vérification des champs requis
        if (!isset($data['name'], $data['surname'], $data['email'], $data['Tel'], $data['Matricule'], $data['password'])) {
            throw new Exception('Données manquantes');
        }

        // Requête d'insertion
        $query = "INSERT INTO utilisateurs (nom, prenom, email, telephone, fixe, division_id, service_id, matricule, mot_de_passe, role) 
                  VALUES (:nom, :prenom, :email, :telephone, :fixe, :division_id, :service_id, :matricule, :mot_de_passe, 'user')";
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            'nom' => $data['name'],
            'prenom' => $data['surname'],
            'email' => $data['email'],
            'telephone' => $data['Tel'],
            'fixe' => $data['Poste'],
            'division_id' => $data['division'],
            'service_id' => $data['service'],
            'matricule' => $data['Matricule'],
            'mot_de_passe' => password_hash($data['password'], PASSWORD_DEFAULT) // Correction de l'erreur
        ]);
        echo json_encode(['success' => true, 'message' => 'Gestionnaire créé avec succès']);
    } catch (Exception $e) {
        echo json_encode(['error' => 'Erreur lors de la création du gestionnaire', 'details' => $e->getMessage()]);
    }
}

// Fonction pour supprimer un utilisateur
function deleteUser($id)
{
    global $pdo;

    try {
        if (empty($id)) {
            throw new Exception('ID manquant');
        }

        $query = "DELETE FROM utilisateurs WHERE id = :id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Utilisateur supprimé avec succès']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Échec de la suppression']);
        }
    } catch (Exception $e) {
        echo json_encode(['error' => 'Erreur lors de la suppression de l\'utilisateur', 'details' => $e->getMessage()]);
    }
}

// Gestion des requêtes
$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action === 'search') {
    $searchTerm = isset($_GET['q']) ? $_GET['q'] : '';
    searchUsers($searchTerm);
} elseif ($action === 'delete') {
    $id = isset($_GET['id']) ? $_GET['id'] : '';
    deleteUser($id);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (isset($data['name']) && isset($data['surname'])) {
        createUser($data);
    } else {
        echo json_encode(['error' => 'Données manquantes']);
    }
} else {
    readUsers();
}
