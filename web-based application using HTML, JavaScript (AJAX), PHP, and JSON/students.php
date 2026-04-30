<?php
// students.php — Returns student data as a JSON response

// Set response headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');   // Allow local development from any origin

// ── Student data array ──────────────────────────────────────────────────────
$students = [
    [
        "id"         => "CSE-2201",
        "name"       => "Ayesha Rahman",
        "department" => "CSE",
        "semester"   => "8th",
        "cgpa"       => 3.92
    ],
    [
        "id"         => "EEE-2145",
        "name"       => "Tariq Hossain",
        "department" => "EEE",
        "semester"   => "6th",
        "cgpa"       => 3.67
    ],
    [
        "id"         => "BBA-1998",
        "name"       => "Nusrat Jahan",
        "department" => "BBA",
        "semester"   => "4th",
        "cgpa"       => 3.45
    ],
    [
        "id"         => "ME-2089",
        "name"       => "Rafiul Islam",
        "department" => "Mechanical Eng.",
        "semester"   => "7th",
        "cgpa"       => 3.81
    ],
    [
        "id"         => "CSE-2334",
        "name"       => "Sadia Akter",
        "department" => "CSE",
        "semester"   => "5th",
        "cgpa"       => 3.55
    ],
    [
        "id"         => "PHY-2256",
        "name"       => "Imran Chowdhury",
        "department" => "Physics",
        "semester"   => "3rd",
        "cgpa"       => 3.78
    ]
];

// ── Convert array to JSON and output ───────────────────────────────────────
echo json_encode($students, JSON_PRETTY_PRINT);
?>
