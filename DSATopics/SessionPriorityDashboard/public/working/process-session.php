<?php

//ACTUAL BACKEND PROCESSING IN THIS

//WILL READ SESSION ID, THEN READ ORDER OF TASKS , THEN ALSO USE PRIORITY QUEUE AND THEN RETURN THE ORDER TOEB PERFORMED

//ONLY SESSION ID WILL BE SENT BY FRONEND, REST CAN BE DONE FROM STORAGE


declare(strict_types=1); //strict type checking

require_once __DIR__ . '/../../php/storage.php';
require_once __DIR__ . '/../../php/tasks.php';

header('Content-Type: application/json');

// Read request body
$orig_input = file_get_contents('php://input');

if ($orig_input === false) {
    echo json_encode([
        'success' => false,
        'message' => 'Could not read input'
    ]);
    exit;
}

$data = json_decode($orig_input, true);

if (!is_array($data)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid JSON'
    ]);
    exit;
}

// Read session ID safely
$sessionId = isset($data['sessionId']) && is_string($data['sessionId']) ? $data['sessionId'] : '';

if ($sessionId === '') {
    echo json_encode([
        'success' => false,
        'message' => 'Session ID missing'
    ]);
    exit;
}

// Verify session exists
$sessionData = readSessionFile($sessionId);

if ($sessionData === null) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid session'
    ]);
    exit;
}

// Read saved task order
$orderedTaskIds = isset($sessionData['orderedTaskIds']) && is_array($sessionData['orderedTaskIds'])
    ? $sessionData['orderedTaskIds']
    : [];

if ($orderedTaskIds === []) 
    {
    echo json_encode([
        'success' => false,
        'message' => 'No priorities saved'
    ]);
    exit;
}


//MAIN PRIOIRTY QUEUE IMPLEMENTATIONS LETS START

// Get task details
$taskDetails = getTaskDetails();
//WILLL GIVE ASSOCIATIVE ARRAY TYPE WITH LABEL, START MSG AND ALSO END DONE MSG

// Create priority queue (heap--based)
$priorityQueue = new SplPriorityQueue();// WILL RETURN HIGHEST PRIORITY ITEM

// Highest UI item should get highest priority number
$priorityNumber = count($orderedTaskIds); //START PRIOIRTY VALUE FROM NO OF TASKSS SO 1ST WILL GET NO OF TAKS THATS HIGHEST PROPERTY THEN IT WILL DECREASE BY 1


// Insert tasks into priority queue
foreach ($orderedTaskIds as $taskId) 
    {
        if (!is_string($taskId)) 
            {
                continue;
            }

    if (!isset($taskDetails[$taskId])) 
        {
            continue;
        }

    $priorityQueue->insert($taskId, $priorityNumber);
    $priorityNumber--;
}

// Extract tasks in priority order
$result = [];

while (!$priorityQueue->isEmpty()) 
{
    $taskId = $priorityQueue->extract();

    $result[] = 
    [
        'id' => $taskId,
        'label' => $taskDetails[$taskId]['label'],
        'startMessage' => $taskDetails[$taskId]['startMessage'],
        'doneMessage' => $taskDetails[$taskId]['doneMessage']
    ];
}

// Return ordered tasks
echo json_encode([
    'success' => true,
    'tasks' => $result
]);