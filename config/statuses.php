<?php
$statuses = [
    [
        'label' => 'Pending',
        'value' => 'pending',
        'color' => '#FFC107',
    ],
    [
        'label' => 'Greige Weaving',
        'value' => 'greige_weaving',
        'color' => '#FF9800',
    ],
    [
        'label' => 'Greige Ready',
        'value' => 'greige_ready',
        'color' => '#FF5722',
    ],
    [
        'label' => 'Colour wise details received/approved',
        'value' => 'colour_details_received',
        'color' => '#FFEB3B',
    ],
    [
        'label' => 'Lab dip prepared',
        'value' => 'lab_dip_prepared',
        'color' => '#CDDC39',
    ],
    [
        'label' => 'Lab dip approved',
        'value' => 'lab_dip_approved',
        'color' => '#8BC34A',
    ],
    [
        'label' => 'Approved for dying',
        'value' => 'approved_for_dying',
        'color' => '#4CAF50',
    ],
    [
        'label' => 'Dying finished',
        'value' => 'dying_finished',
        'color' => '#009688',
    ],
    [
        'label' => 'Process 1 (clear text)',
        'value' => 'process_1',
        'color' => '#00BCD4',
    ],
    [
        'label' => 'Process 2 (clear text)',
        'value' => 'process_2',
        'color' => '#03A9F4',
    ],
    [
        'label' => 'Sample sent for approval',
        'value' => 'sample_sent_for_approval',
        'color' => '#2196F3',
    ],
    [
        'label' => 'Sample approved',
        'value' => 'sample_approved',
        'color' => '#3F51B5',
    ],
];

return [
    'fir_activity' => $statuses,
    'gz_fir_activity' => $statuses,
];
