<?php

return [
    'services' => [
        'screening' => ['name' => 'Blood donation screening', 'fee' => 500],
        'blood-typing' => ['name' => 'Blood typing & checkup', 'fee' => 800],
        'plasma' => ['name' => 'Plasma donation', 'fee' => 1200],
        'consultation' => ['name' => 'Doctor consultation', 'fee' => 1500],
    ],
    'payment_methods' => [
        'Cash' => 'Cash',
        'GCash' => 'GCash at clinic',
        'Card' => 'Card at clinic',
    ],
];
