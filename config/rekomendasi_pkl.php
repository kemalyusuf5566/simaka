<?php

return [
    'weights' => [
        'kehadiran' => 50,
        'sikap' => 30,
        'bk' => 20,
    ],

    'sikap_scores' => [
        'sangat baik' => 100,
        'baik' => 85,
        'cukup' => 70,
        'perlu bimbingan' => 55,
    ],

    'sikap_default_score' => 60,

    'bk_score_ranges' => [
        ['max' => 20, 'score' => 100],
        ['max' => 40, 'score' => 85],
        ['max' => 75, 'score' => 70],
        ['max' => 120, 'score' => 55],
        ['max' => null, 'score' => 40],
    ],

    'grade_thresholds' => [
        'A' => 85,
        'B' => 75,
        'C' => 65,
        'D' => 50,
    ],

    'grade_labels' => [
        'A' => 'Sangat Direkomendasikan',
        'B' => 'Direkomendasikan',
        'C' => 'Cukup',
        'D' => 'Perlu Pertimbangan',
        'E' => 'Tidak Direkomendasikan',
    ],

    'attendance_default_score_without_data' => 70,

    'roles' => [
        'guru_global_access' => ['pembimbing_pkl'],
        'guru_limited_access' => ['wali_kelas'],
    ],
];
