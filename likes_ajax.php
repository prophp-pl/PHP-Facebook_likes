<?php
header("Content-Type: application/json;charset=utf-8");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'message' => 'Nieprawidłowa metoda. Dozwolone tylko POST.'
    ]);
    return;
}

try {
    $dbh = new PDO('mysql:dbname=test;host=127.0.0.1', 'user', 'hasło',
        [
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
        ]
    );
    $dbh->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'message' => 'Klasa PDO zwróciła wyjątek: ' . $e->getMessage()
    ]);
    return;
}

/**
 * Pobieranie danych o reakcjach z bazy
 * 
 * @param \PDO $dbh Obiekt PDO połączenia z bazą
 * @param int $pageId numer strony
 * @param string $user IP użytkownika
 * @return array
 */
function fetch(\PDO $dbh, $pageId, $user) {
    $sql = 'SELECT `like_status` AS `like` FROM `likes` WHERE `page_id` = ? AND INET_NTOA(`user`) = ? LIMIT 1';
    $sth = $dbh->prepare($sql);
    $sth->execute([
        $pageId,
        $user
    ]);

    $result = $sth->fetch(PDO::FETCH_ASSOC);
    return (!empty($result)) ? $result : [];
}

$pageId = (int) $_POST['pageId'];
$action = isset($_POST['action']) ? $_POST['action'] : null;

if ($action === 'select') {
    try {
        $result = fetch($dbh, $pageId, $_SERVER['REMOTE_ADDR']);
        echo json_encode([
            'data' => $result
        ]);
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'message' => 'Klasa PDO zwróciła wyjątek: ' . $e->getMessage()
        ]);
        return;
    }
}
elseif ($action === 'save') {
    // Lista dozwolonych kolumn w parametrze `element`
    $allowedColumns = [
        'like' => 'like_status'
    ];
    
    $columnName = isset($_POST['element']) ? $_POST['element'] : null;
    if (!array_key_exists($columnName, $allowedColumns)) {
        http_response_code(500);
        echo json_encode([
            'message' => 'Nieprawidłowy wartość dla pola `element`.'
        ]);
        return;
    }
    $columnName = '`' . $allowedColumns[$columnName] . '`';
    
    try {
        $sql = "INSERT INTO `likes` (`page_id`, `user`, $columnName) VALUES (?, INET_ATON(?), 1)";
        $sql.= "ON DUPLICATE KEY UPDATE $columnName = NOT $columnName;";

        $sth = $dbh->prepare($sql);
        $sth->execute([
            $pageId,
            $_SERVER['REMOTE_ADDR']
        ]);
        
        $result = fetch($dbh, $pageId, $_SERVER['REMOTE_ADDR']);
        echo json_encode([
            'data' => $result
        ]);
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'message' => 'Klasa PDO zwróciła wyjątek: ' . $e->getMessage()
        ]);
        return;
    }
} else {
    http_response_code(500);
    echo json_encode([
        'message' => 'Nieprawidłowa akcja'
    ]);
    return;
}
