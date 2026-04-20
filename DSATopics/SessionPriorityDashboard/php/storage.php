<?php

//SAVE SESSION DATA
declare(strict_types=1);

function getSessionFolderPath(): string
{
    return __DIR__ . '/../storage/sessions';
}

function getSessionFilePath(string $sessionId): string
{
    return getSessionFolderPath() . '/' . $sessionId . '.json';

    //CONVERTS SESSION ID TO JSON FILE PATH
}

function createSessionFile(string $sessionId): void
{

//NO RETURN WE NEED IN THIS, JUST CREATE
    $folderPath = getSessionFolderPath();

    if (!is_dir($folderPath)) 
        {
            mkdir($folderPath, 0777, true);
        }
        //0777--PERMISSIONS
        //TRUE FOR RECURSIVE CREATION LIKE IF PARENT FOLDERS IS NOT THERE, U CAN ALSO CREATE IT

    $sessionData = 
    [
        'sessionId' => $sessionId,
        'orderedTaskIds' => []
    ];

    file_put_contents
    (
        getSessionFilePath($sessionId),
        json_encode($sessionData, JSON_PRETTY_PRINT)
    );
}

function savePriorityForSession(string $sessionId, array $orderedTaskIds): bool
{
    $filePath = getSessionFilePath($sessionId);

    if (!file_exists($filePath)) {
        return false;
    }

    $sessionData = [
        'sessionId' => $sessionId,
        'orderedTaskIds' => $orderedTaskIds
    ];

    file_put_contents(
        $filePath,
        json_encode($sessionData, JSON_PRETTY_PRINT)
    );

    return true;
}

function readSessionFile(string $sessionId): ?array
{
    $filePath = getSessionFilePath($sessionId);

    if (!file_exists($filePath)) {
        return null;
    }

    $content = file_get_contents($filePath);

    if ($content === false) {
        return null;
    }

    $decodedData = json_decode($content, true);

    if (!is_array($decodedData)) {
        return null;
    }

    return $decodedData;
}

//WILL HAVE TO CHECK WHETHER STORAGE FOLDER IS THERE OR NOT, OR REATE DEFAULT SESSION SSTRUCTURE, EMPTY PQ AT BEGINNING, CONVERT TO JSON AND SAVE