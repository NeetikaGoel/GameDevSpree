<?php

//WILL HAVE TO READ SESSION ID AND THE ORDER OF TASKS SET BY USER FORM FRONTEND
//WILL SAVE IT ALSO
declare(strict_types=1);

require_once __DIR__ . '/../../php/storage.php';

header('Content-Type: application/json');

// Read JSON request body
$orig_input = file_get_contents('php://input');

if ($orig_input === false) 
    {
    echo json_encode([
        'success' => false,
        'message' => 'Could not read input'
    ]); //RETURNS FAILURE COZ CANNOT READ INPUT EVEN
    exit;
}

$data = json_decode($orig_input, true);

if (!is_array($data)) //VALID ARRAY OR NOT
{
    echo json_encode([
        'success' => false,
        'message' => 'Invalid JSON' //WRONG FORMAT
    ]);
    exit;
}

// Extract values safely
$sessionId = isset($data['sessionId']) && is_string($data['sessionId']) ? $data['sessionId'] : ''; //CHECK EXITS OR NOT AND IF YES TAKE IT
$orderedTaskIds = isset($data['orderedTaskIds']) && is_array($data['orderedTaskIds']) ? $data['orderedTaskIds'] : [];

if ($sessionId === '') {
    echo json_encode([
        'success' => false,
        'message' => 'Session ID missing'
    ]);
    exit;
}

if ($orderedTaskIds === []) {
    echo json_encode([
        'success' => false,
        'message' => 'Priority list missing'
    ]);
    exit;
}

// Save to session file
$isSaved = savePriorityForSession($sessionId, $orderedTaskIds);

if (!$isSaved) {
    echo json_encode([
        'success' => false,
        'message' => 'Session not found'
    ]);
    exit;
}

echo json_encode([
    'success' => true,
    'message' => 'Priority saved successfully'
]);