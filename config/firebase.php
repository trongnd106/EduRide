<?php

return [
    'credentials' => [
        'file' => storage_path('app/firebase/firebase-credentials.json'),
    ],

    'project_id' => env('FIREBASE_PROJECT_ID', 'schoolbustracker-70227'),
    'database_url' => env('FIREBASE_DATABASE_URL'),
];
