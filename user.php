<?php
include 'db_connect.php';
header('Content-Type: application/json');

// Fonction pour lire les utilisateurs
function readUsers()
{
    global $pdo;

    try {
        $query = "SELECT u.nom,u.prenom,u.email,u.telephone,u.fixe, d.nom AS division_nom, s.nom AS service_nom, a.nom AS unite_nom
            FROM utilisateurs u
            LEFT JOIN division d ON u.division_id = d.id 
            LEFT JOIN unite a ON u.unite_id = a.id
            LEFT JOIN service s ON u.service_id = s.id
            WHERE u.role = 'user'
        ";
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
// Fonction pour créer un utilisateur
// Fonction pour créer un utilisateur
function createUser($data)
{
    global $pdo;
    if (is_null($data) || !is_array($data)) {
        echo json_encode(['error' => 'Aucune donnée reçue ou format incorrect']);
        return;
    }
    try {
        // Assurez-vous que les clés existent dans les données
        if (!isset($data['name'], $data['surname'], $data['email'], $data['Tel'], $data['Poste'], $data['division'], $data['service'], $data['Matricule'], $data['unite_id'])) {
            echo json_encode(['error' => 'Données manquantes']);
            return;
        }

        $query = "INSERT INTO utilisateurs (nom, prenom, email, telephone, fixe, division_id, service_id, matricule, unite_id, role) 
                  VALUES (:nom, :prenom, :email, :telephone, :fixe, :division_id, :service_id, :matricule, :unite_id, 'user')";
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
            'unite_id' => $data['unite_id'],
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
// Fonction pour modifier le fixe et le téléphone d'un utilisateur
// Fonction pour modifier le fixe et le téléphone d'un utilisateur
function updateUser($id, $data)
{
    global $pdo;

    try {
        // Vérifier si les clés existent dans $data avant de les utiliser
        $fixe = isset($data['fixe']) ? $data['fixe'] : null;
        $telephone = isset($data['telephone']) ? $data['telephone'] : null;

        $query = "UPDATE utilisateurs SET fixe = :fixe, telephone = :telephone WHERE id = :id AND role = 'user'";
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            'fixe' => $fixe,
            'telephone' => $telephone,
            'id' => $id
        ]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Utilisateur mis à jour avec succès']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Aucune mise à jour effectuée']);
        }
    } catch (Exception $e) {
        echo json_encode(['error' => 'Erreur lors de la mise à jour de l\'utilisateur', 'details' => $e->getMessage()]);
    }
}


function getselectData()
{
    global $pdo;
    try {
        $selectData = [];

        $query = "SELECT * FROM division";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $division = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $selectData['division'] = $division;

        $query = "SELECT * FROM service";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $service = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $selectData['service'] = $service;

        $query = "SELECT * FROM unite";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $unite = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $selectData['unite'] = $unite;

        echo json_encode($selectData);
    } catch (Exception $e) {
        echo json_encode(['error' => 'Erreur lors de la récupération des données', 'details' => $e->getMessage()]);
    }
}

// Gestion des requêtes
$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action === 'search') {
    $searchTerm = isset($_GET['q']) ? $_GET['q'] : '';
    searchUsers($searchTerm);
} elseif ($action === 'update') {
    $id = isset($_GET['id']) ? $_GET['id'] : '';
    $data = $_POST;
    updateUser($id, $data);
} elseif ($action === 'delete') {
    $id = isset($_GET['id']) ? $_GET['id'] : '';
    deleteUser($id);
} elseif ($action === 'selectData') {
    getselectData();
} else // Exemple de validation des données reçues
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = $_POST; // ou `file_get_contents('php://input')` si vous utilisez JSON

        createUser($data);
    } else {
        readUsers();
    }