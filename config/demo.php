<?php

return [
    /*
    | If set, every page of the demo asks for this key first (so you can publish the demo on a server without
    | letting anyone fill your collector with errors). Leave it empty for local use.
    */
    'key' => env('DEMO_KEY'),

    // How long the "slow request" button takes (the SDK reports requests over NIGHTGUARDIAN_SLOW_REQUEST_MS, 2 s).
    'slow_request_seconds' => 2.5,

    // Largest burst the "burst" button accepts.
    'burst_max' => 200,
];
