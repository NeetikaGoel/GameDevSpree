<?php

//FOR TASK VRIABLES VALUES AND MESSAGES
declare(strict_types=1);

function getTaskDetails(): array
{
    return [
        'notifications' => [
            'label' => 'Notifications',
            'startMessage' => 'Notifications are being shown...',
            'doneMessage' => 'Notifications shown successfully!!'
        ],
        'email_update' => [
            'label' => 'Email Update',
            'startMessage' => 'Email update is happening...',
            'doneMessage' => 'Email updated successfully!!'
        ],
        'security_check' => [
            'label' => 'Security Check',
            'startMessage' => 'Security check is happening...',
            'doneMessage' => 'Security check completed successfully!!'
        ],
        'content_suggestions' => [
            'label' => 'Content Suggestions',
            'startMessage' => 'Content suggestions are loading...',
            'doneMessage' => 'Content suggestions loaded successfully!!'
        ],
        'open_apps' => [
            'label' => 'Open Apps',
            'startMessage' => 'Apps are being prepared...',
            'doneMessage' => 'Apps prepared successfully!!'
        ]
    ];
}