<?php
include("connector.php");

// Get room parameter
$room = isset($_GET['room']) ? $_GET['room'] : '';

if (empty($room)) {
    echo json_encode(['available' => false, 'message' => 'Room parameter is missing']);
    exit;
}

date_default_timezone_set("Asia/Manila");
$current_time = date('H:i:s');
$current_day = date('N'); // 1 (Monday) through 7 (Sunday)

// Map current day to day_group
$day_map = [
    '1' => 'MW',  // Monday
    '2' => 'TTH', // Tuesday
    '3' => 'MW',  // Wednesday
    '4' => 'TTH', // Thursday
    '5' => 'F',   // Friday
    '6' => 'S',   // Saturday
    '7' => null   // Sunday - closed
];

$day_group = $day_map[$current_day] ?? null;

if (!$day_group) {
    echo json_encode(['available' => false, 'message' => 'Laboratory is closed today']);
    exit;
}

// Find current time slot
$time_slots = [
    ['07:30:00', '09:00:00', '7:30AM-9:00AM'],
    ['09:00:00', '10:30:00', '9:00AM-10:30AM'],
    ['10:30:00', '12:00:00', '10:30AM-12:00PM'],
    ['12:00:00', '13:00:00', '12:00PM-1:00PM'],
    ['13:00:00', '15:00:00', '1:00PM-3:00PM'],
    ['15:00:00', '16:30:00', '3:00PM-4:30PM'],
    ['16:30:00', '18:00:00', '4:30PM-6:00PM'],
    ['18:00:00', '19:30:00', '6:00PM-7:30PM'],
    ['19:30:00', '21:00:00', '7:30PM-9:00PM']
];

$current_slot = null;
foreach ($time_slots as $slot) {
    if ($current_time >= $slot[0] && $current_time <= $slot[1]) {
        $current_slot = $slot[2];
        break;
    }
}

if (!$current_slot) {
    echo json_encode(['available' => false, 'message' => 'Laboratory is currently closed']);
    exit;
}

// Check lab schedule
$query = "SELECT status FROM lab_schedules 
          WHERE room_number = ? 
          AND day_group = ? 
          AND time_slot = ?";

$stmt = $con->prepare($query);
$stmt->bind_param("sss", $room, $day_group, $current_slot);
$stmt->execute();
$result = $stmt->get_result();
$schedule = $result->fetch_assoc();

if ($schedule && $schedule['status'] === 'Occupied') {
    echo json_encode([
        'available' => false,
        'message' => "Room $room is scheduled as Occupied during $current_slot"
    ]);
} else {
    echo json_encode(['available' => true, 'message' => 'Room is available']);
}