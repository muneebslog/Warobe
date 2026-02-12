<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Recent wear penalty (days)
    |--------------------------------------------------------------------------
    | Penalty applied if item was worn in last N days.
    */
    'recent_days_penalty' => (int) env('WARDROBE_RECENT_DAYS_PENALTY', 3),

    /*
    |--------------------------------------------------------------------------
    | Rotation days
    |--------------------------------------------------------------------------
    | Avoid suggesting the same combination within this many days.
    */
    'rotation_days' => (int) env('WARDROBE_ROTATION_DAYS', 5),

    /*
    |--------------------------------------------------------------------------
    | Unused clothes threshold (days)
    |--------------------------------------------------------------------------
    | Notify if item not worn in this many days.
    */
    'unused_days_threshold' => (int) env('WARDROBE_UNUSED_DAYS_THRESHOLD', 30),

    /*
    |--------------------------------------------------------------------------
    | Neutral colors (match with anything)
    |--------------------------------------------------------------------------
    */
    'neutral_colors' => array_map('strtolower', [
        'white', 'black', 'beige', 'navy', 'grey', 'gray', 'brown', 'khaki', 'cream',
    ]),

    /*
    |--------------------------------------------------------------------------
    | Enable AI stylist
    |--------------------------------------------------------------------------
    */
    'enable_ai' => (bool) env('WARDROBE_ENABLE_AI', false),

];
