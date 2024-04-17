<?php

// Connessione al database
$servername = "localhost";
$username = "utente2";
$password = "1234";
$dbname = "server_web";

$conn = new mysqli($servername, $username, $password, $dbname);

// Verifica della connessione
if ($conn->connect_error) {
    die("Connessione fallita: " . $conn->connect_error);
}

// verifico che le stringhe esistano e siano corrette
function validateData($data) {
    return (
        isset($data['nome']) && is_string($data['nome']) &&
        isset($data['cognome']) && is_string($data['cognome']) &&
        isset($data['email']) && filter_var($data['email'], FILTER_VALIDATE_EMAIL) &&
        isset($data['eta']) && is_numeric($data['eta']) &&
        isset($data['data_iscrizione']) && strtotime($data['data_iscrizione'])
    );
}

// Controllo del metodo HTTP
$array = explode('/', $_SERVER['REQUEST_URI']); 
$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'GET') {
    if (isset($_GET['id'])) {
        // Se è specificato un ID nella richiesta GET
        $id = $_GET['id'];
        $sql = "SELECT * FROM dati WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            echo json_encode($row);
        } else {
            echo "Nessun risultato trovato con ID $id";
        }
    } else {
        // Se non è specificato un ID nella richiesta GET
        $sql = "SELECT * FROM dati";
        $result = $conn->query($sql);
        
        if ($result->num_rows > 0) {
            $rows = array();
            while ($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
            echo json_encode($rows);
        } else {
            echo "Nessun risultato trovato nella tabella.";
        }
    }
} elseif ($method == 'POST') {
    // Inserimento dei dati
    $data = json_decode(file_get_contents("php://input"), true);

    if (!empty($data) && validateData($data)) {
        $sql = "INSERT INTO dati (nome, cognome, email, eta, data_iscrizione) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssis", $data['nome'], $data['cognome'], $data['email'], $data['eta'], $data['data_iscrizione']);

        if ($stmt->execute()) {
            echo "Dati inseriti con successo.";
        } else {
            echo "Errore durante l'inserimento dei dati.";
        }
    } else {
        echo "Dati non validi.";
    }
} elseif ($method == 'PUT') {
    // Aggiornamento dei dati
    parse_str(file_get_contents("php://input"), $data);
    $id = isset($_GET['id']) ? $_GET['id'] : null;

    if (!is_null($id) && validateData($data)) {
        $sql = "UPDATE dati SET nome=?, cognome=?, email=?, eta=?, data_iscrizione=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssi", $data['nome'], $data['cognome'], $data['email'], $data['eta'], $data['data_iscrizione'], $id);

        if ($stmt->execute()) {
            echo "Dati aggiornati con successo.";
        } else {
            echo "Errore durante l'aggiornamento dei dati.";
        }
    } else {
        echo "ID non specificato o dati non validi.";
    }
} elseif ($method == 'DELETE') {
    // Cancellazione dei dati
    $id = isset($_GET['id']) ? $_GET['id'] : null;

    if (!is_null($id)) {
        $sql = "DELETE FROM dati WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            echo "Dati cancellati con successo.";
        } else {
            echo "Errore durante la cancellazione dei dati.";
        }
    } else {
echo "ID non specificato.";
    }
} else {
    // Se il metodo HTTP non è supportato
    http_response_code(405); 
    echo "Metodo non consentito";
}

$conn->close();

?>