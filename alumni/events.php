<?php
// Header
include 'includes/header.php';

// Database connection
require_once '../admin/config/database.php';

// Use the existing $conn variable from database.php
$pdo = $conn;

// Fetch events with search and filter functionality
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$event_type = isset($_GET['event_type']) ? trim($_GET['event_type']) : '';
$location = isset($_GET['location']) ? trim($_GET['location']) : '';

$query = "SELECT * FROM alumni_events WHERE status = 'Published'";
$params = [];

if (!empty($search)) {
    $query .= " AND (event_title LIKE ? OR event_description LIKE ? OR contact_person LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
}

if (!empty($event_type)) {
    $query .= " AND event_type = ?";
    $params[] = $event_type;
}

if (!empty($location)) {
    $query .= " AND (physical_address LIKE ? OR online_link LIKE ?)";
    $locationParam = "%$location%";
    $params[] = $locationParam;
    $params[] = $locationParam;
}

$query .= " ORDER BY start_datetime ASC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$events = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Debug: Check if events are being fetched
if (empty($events)) {
    // Try to get all events regardless of status to see what's in the database
    $debugStmt = $pdo->query("SELECT COUNT(*) as total, status FROM alumni_events GROUP BY status");
    $debugResults = $debugStmt->fetchAll(PDO::FETCH_ASSOC);
    // You can uncomment the next line to see debug info
    // error_log("Debug: " . print_r($debugResults, true));
}

// Fetch unique event types and locations for filters
$typeStmt = $pdo->query("SELECT DISTINCT event_type FROM alumni_events WHERE status = 'Published' AND event_type IS NOT NULL ORDER BY event_type");
$event_types = $typeStmt->fetchAll(PDO::FETCH_COLUMN);

$locStmt = $pdo->query("SELECT DISTINCT physical_address FROM alumni_events WHERE status = 'Published' AND physical_address IS NOT NULL AND physical_address != '' ORDER BY physical_address");
$locations = $locStmt->fetchAll(PDO::FETCH_COLUMN);

// Get counts for stats
$total_events = count($events);
$upcoming_events = 0;
$online_events = 0;
$in_person_events = 0;

foreach ($events as $event) {
    if (strtotime($event['start_datetime']) > time()) {
        $upcoming_events++;
    }
    if (!empty($event['online_link'])) {
        $online_events++;
    }
    if (!empty($event['physical_address'])) {
        $in_person_events++;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events - SLSU-HC Alumni Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#1e40af',
                        secondary: '#3b82f6'
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gradient-to-br from-blue-50 via-white to-indigo-50 min-h-screen">

    <?php include 'includes/navbar.php'; ?>
    <?php include 'includes/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="main-content lg:ml-72 pt-16 min-h-screen">
        <div class="p-6">
            

            <!-- Enhanced Search and Filters -->
            <div class="bg-white rounded-2xl shadow-xl mb-8 border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-r from-blue-600 to-indigo-600 p-6">
                    <h2 class="text-xl font-semibold text-white flex items-center">
                        <i class="fas fa-search text-blue-200 mr-3"></i>
                        Search & Filter Events
                    </h2>
                </div>
                <div class="p-6">
                    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div class="relative">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Search Events</label>
                            <div class="relative">
                                <input type="text" name="search" placeholder="Event title, description, or organizer..." 
                                       value="<?php echo htmlspecialchars($search); ?>"
                                       class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                                <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Event Type</label>
                            <select name="event_type" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                                <option value="">All Types</option>
                                <?php foreach ($event_types as $type): ?>
                                    <option value="<?php echo htmlspecialchars($type); ?>" 
                                            <?php echo $event_type === $type ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($type); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Location</label>
                            <select name="location" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                                <option value="">All Locations</option>
                                <?php foreach ($locations as $loc): ?>
                                    <option value="<?php echo htmlspecialchars($loc); ?>" 
                                            <?php echo $location === $loc ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($loc); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white px-6 py-3 rounded-xl font-medium transition-all duration-200 transform hover:scale-105 shadow-lg">
                                <i class="fas fa-search mr-2"></i>Search
                            </button>
                        </div>
                    </form>
                    
                    <!-- Clear Filters -->
                    <?php if (!empty($search) || !empty($event_type) || !empty($location)): ?>
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <a href="events.php" class="inline-flex items-center text-blue-600 hover:text-blue-800 text-sm font-medium transition-colors duration-200">
                                <i class="fas fa-times mr-2"></i>Clear all filters
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Enhanced Events Listings -->
            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 p-6 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                        <i class="fas fa-calendar-alt text-blue-500 mr-3"></i>
                        Alumni Events
                        <?php if (!empty($search) || !empty($event_type) || !empty($location)): ?>
                            <span class="ml-2 text-sm font-normal text-gray-500 bg-white px-3 py-1 rounded-full">
                                <?php echo count($events); ?> results found
                            </span>
                        <?php endif; ?>
                    </h2>
                </div>
                <div class="p-6">
                    <?php if (empty($events)): ?>
                        <div class="text-center py-16">
                            <div class="w-24 h-24 bg-gradient-to-br from-gray-100 to-gray-200 rounded-full flex items-center justify-center mx-auto mb-6">
                                <i class="fas fa-calendar-times text-gray-400 text-3xl"></i>
                            </div>
                            <h3 class="text-2xl font-bold text-gray-900 mb-3">No events found</h3>
                            <p class="text-gray-600 mb-6 max-w-md mx-auto">
                                <?php if (!empty($search) || !empty($event_type) || !empty($location)): ?>
                                    Try adjusting your search criteria or 
                                    <a href="events.php" class="text-blue-600 hover:text-blue-800 font-medium">clear all filters</a>.
                                <?php else: ?>
                                    No events scheduled at the moment. Check back later for exciting upcoming events!
                                <?php endif; ?>
                            </p>
                            
                            <!-- Debug Information -->
                            <?php if (isset($debugResults)): ?>
                                <div class="mt-8 p-4 bg-gray-50 rounded-lg max-w-md mx-auto">
                                    <h4 class="text-sm font-semibold text-gray-700 mb-2">Database Status:</h4>
                                    <?php foreach ($debugResults as $result): ?>
                                        <p class="text-xs text-gray-600">
                                            Status: <span class="font-medium"><?php echo htmlspecialchars($result['status']); ?></span> - 
                                            Count: <span class="font-medium"><?php echo $result['total']; ?></span>
                                        </p>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="space-y-6">
                            <?php foreach ($events as $event): ?>
                                <?php 
                                $is_upcoming = strtotime($event['start_datetime']) > time();
                                $is_online = !empty($event['online_link']);
                                $event_date = date('M j, Y', strtotime($event['start_datetime']));
                                $event_time = date('g:i A', strtotime($event['start_datetime']));
                                $end_time = date('g:i A', strtotime($event['end_datetime']));
                                ?>
                                <div class="group bg-white border border-gray-200 rounded-2xl p-6 hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1 <?php echo $is_upcoming ? 'ring-2 ring-blue-100 bg-gradient-to-r from-blue-50 to-white' : 'bg-gray-50'; ?>">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1 mr-8">
                                            <!-- Enhanced Event Header -->
                                            <div class="flex items-center justify-between mb-6">
                                                <div class="flex items-center space-x-3">
                                                    <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold <?php echo $is_upcoming ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800'; ?>">
                                                        <i class="fas fa-tag mr-2"></i>
                                                        <?php echo htmlspecialchars($event['event_type']); ?>
                                                    </span>
                                                    <?php if ($is_upcoming): ?>
                                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800 animate-pulse">
                                                            <i class="fas fa-clock mr-1"></i>Upcoming
                                                        </span>
                                                    <?php endif; ?>
                                                    <?php if ($is_online): ?>
                                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-800">
                                                            <i class="fas fa-video mr-1"></i>Online
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="text-right">
                                                    <div class="text-sm text-gray-500 font-medium"><?php echo $event_date; ?></div>
                                                    <div class="text-sm font-bold text-gray-900"><?php echo $event_time; ?> - <?php echo $end_time; ?></div>
                                                </div>
                                            </div>

                                            <!-- Enhanced Event Title -->
                                            <h2 class="text-2xl font-bold text-gray-900 mb-4 group-hover:text-blue-600 transition-colors duration-200">
                                                <?php echo htmlspecialchars($event['event_title']); ?>
                                            </h2>

                                            <!-- Enhanced Event Description -->
                                            <p class="text-gray-600 text-base mb-6 line-clamp-3 leading-relaxed">
                                                <?php echo htmlspecialchars(substr($event['event_description'], 0, 250)) . (strlen($event['event_description']) > 250 ? '...' : ''); ?>
                                            </p>

                                            <!-- Enhanced Event Details -->
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                                <div class="space-y-4">
                                                    <div class="flex items-center text-sm text-gray-600 bg-gray-50 p-3 rounded-lg">
                                                        <i class="fas fa-map-marker-alt mr-3 text-blue-500 w-5"></i>
                                                        <span class="font-medium">
                                                            <?php if (!empty($event['physical_address'])): ?>
                                                                <?php echo htmlspecialchars($event['physical_address']); ?>
                                                            <?php elseif (!empty($event['online_link'])): ?>
                                                                <a href="<?php echo htmlspecialchars($event['online_link']); ?>" target="_blank" class="text-blue-600 hover:text-blue-800 underline">
                                                                    Online Event Link
                                                                </a>
                                                            <?php else: ?>
                                                                Location TBA
                                                            <?php endif; ?>
                                                        </span>
                                                    </div>
                                                    <div class="flex items-center text-sm text-gray-600 bg-gray-50 p-3 rounded-lg">
                                                        <i class="fas fa-user mr-3 text-green-500 w-5"></i>
                                                        <span class="font-medium">Contact: <?php echo htmlspecialchars($event['contact_person']); ?></span>
                                                    </div>
                                                    <div class="flex items-center text-sm text-gray-600 bg-gray-50 p-3 rounded-lg">
                                                        <i class="fas fa-envelope mr-3 text-orange-500 w-5"></i>
                                                        <span class="font-medium"><?php echo htmlspecialchars($event['contact_email']); ?></span>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Enhanced Action Buttons -->
                                            <div class="flex space-x-4">
                                                <button class="flex-1 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white px-6 py-3 rounded-xl font-semibold transition-all duration-200 transform hover:scale-105 shadow-lg">
                                                    <i class="fas fa-calendar-plus mr-2"></i>Register Now
                                                </button>
                                                <button class="px-6 py-3 border-2 border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 hover:border-blue-300 transition-all duration-200 transform hover:scale-105">
                                                    <i class="fas fa-bookmark"></i>
                                                </button>
                                                <button class="px-6 py-3 border-2 border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 hover:border-blue-300 transition-all duration-200 transform hover:scale-105">
                                                    <i class="fas fa-share"></i>
                                                </button>
                                            </div>
                                        </div>
                                        
                                        <!-- Enhanced Event Image Section - Right Side -->
                                        <div class="flex-shrink-0">
                                            <?php if (!empty($event['poster_image'])): ?>
                                                <div class="relative group">
                                                    <div class="w-80 h-56 rounded-2xl overflow-hidden shadow-2xl border-4 border-white transform hover:scale-105 transition-all duration-300">
                                                        <img src="../admin/<?php echo htmlspecialchars($event['poster_image']); ?>" 
                                                             alt="Event Poster" 
                                                             class="w-full h-full object-cover">
                                                    </div>
                                                    <!-- Overlay on hover -->
                                                    <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-2xl flex items-end">
                                                        <div class="p-4 text-white">
                                                            <p class="text-sm font-medium">Click to view full image</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                <div class="w-80 h-56 bg-gradient-to-br from-blue-100 via-indigo-100 to-purple-100 rounded-2xl flex flex-col items-center justify-center shadow-2xl border-4 border-white">
                                                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center mb-4 shadow-lg">
                                                        <i class="fas fa-calendar-image text-white text-2xl"></i>
                                                    </div>
                                                    <p class="text-gray-600 font-medium text-center">Event Poster</p>
                                                    <p class="text-gray-500 text-sm text-center">Coming Soon</p>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <!-- Enhanced Event Document Link -->
                                            <?php if (!empty($event['event_document'])): ?>
                                                <div class="mt-4 text-center">
                                                    <a href="../admin/<?php echo htmlspecialchars($event['event_document']); ?>" 
                                                       target="_blank" 
                                                       class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-xl hover:from-green-600 hover:to-emerald-700 transition-all duration-200 text-sm font-semibold shadow-lg transform hover:scale-105">
                                                        <i class="fas fa-file-pdf mr-2 text-lg"></i>
                                                        View Event Details
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <!-- Event Status Badge -->
                                            <div class="mt-4 text-center">
                                                <?php if ($is_upcoming): ?>
                                                    <div class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-full text-sm font-semibold shadow-lg">
                                                        <i class="fas fa-star mr-2"></i>
                                                        Upcoming Event
                                                    </div>
                                                <?php else: ?>
                                                    <div class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-gray-500 to-gray-600 text-white rounded-full text-sm font-semibold shadow-lg">
                                                        <i class="fas fa-clock mr-2"></i>
                                                        Past Event
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <!-- Real-time Update Script -->
    <script>
        // Real-time update functionality
        let lastUpdateTime = <?php echo time(); ?>;
        let updateInterval;
        let isUpdating = false;

        // Function to fetch and update events
        async function fetchAndUpdateEvents() {
            if (isUpdating) return;
            isUpdating = true;

            try {
                const response = await fetch('fetch_events_api.php', {
                    method: 'GET',
                    headers: {
                        'Cache-Control': 'no-cache',
                        'Pragma': 'no-cache'
                    }
                });

                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }

                const data = await response.json();
                
                // Check if there are new events or updates
                if (data.timestamp > lastUpdateTime) {
                    updateEventsDisplay(data);
                    lastUpdateTime = data.timestamp;
                }
            } catch (error) {
                console.error('Error fetching events:', error);
            } finally {
                isUpdating = false;
            }
        }

        // Function to update the events display
        function updateEventsDisplay(data) {
            const eventsContainer = document.querySelector('.space-y-6');
            const statsCards = document.querySelectorAll('.text-3xl.font-bold');
            
            if (!eventsContainer) return;

            // Update stats
            if (statsCards.length >= 4) {
                statsCards[0].textContent = data.stats.total_events;
                statsCards[1].textContent = data.stats.upcoming_events;
                statsCards[2].textContent = data.stats.online_events;
                statsCards[3].textContent = data.stats.in_person_events;
            }

            // Update events list
            if (data.events.length === 0) {
                eventsContainer.innerHTML = `
                    <div class="text-center py-12">
                        <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-calendar-times text-gray-400 text-3xl"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">No Events Found</h3>
                        <p class="text-gray-600">There are currently no published events available.</p>
                    </div>
                `;
            } else {
                eventsContainer.innerHTML = data.events.map(event => {
                    const is_upcoming = new Date(event.start_datetime) > new Date();
                    const is_online = event.online_link && event.online_link.trim() !== '';
                    const event_date = new Date(event.start_datetime).toLocaleDateString('en-US', { 
                        month: 'short', 
                        day: 'numeric', 
                        year: 'numeric' 
                    });
                    const event_time = new Date(event.start_datetime).toLocaleTimeString('en-US', { 
                        hour: 'numeric', 
                        minute: '2-digit' 
                    });
                    const end_time = new Date(event.end_datetime).toLocaleTimeString('en-US', { 
                        hour: 'numeric', 
                        minute: '2-digit' 
                    });

                    return `
                        <div class="group bg-white border border-gray-200 rounded-2xl p-6 hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1 ${is_upcoming ? 'ring-2 ring-blue-100 bg-gradient-to-r from-blue-50 to-white' : 'bg-gray-50'}">
                            <div class="flex items-start justify-between">
                                <div class="flex-1 mr-8">
                                    <!-- Enhanced Event Header -->
                                    <div class="flex items-center justify-between mb-6">
                                        <div class="flex items-center space-x-3">
                                            <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold ${is_upcoming ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800'}">
                                                <i class="fas fa-tag mr-2"></i>
                                                ${event.event_type}
                                            </span>
                                            ${is_upcoming ? `
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800 animate-pulse">
                                                    <i class="fas fa-clock mr-1"></i>Upcoming
                                                </span>
                                            ` : ''}
                                            ${is_online ? `
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-800">
                                                    <i class="fas fa-video mr-1"></i>Online
                                                </span>
                                            ` : ''}
                                        </div>
                                        <div class="text-right">
                                            <div class="text-sm text-gray-500 font-medium">${event_date}</div>
                                            <div class="text-sm font-bold text-gray-900">${event_time} - ${end_time}</div>
                                        </div>
                                    </div>

                                    <!-- Enhanced Event Title -->
                                    <h2 class="text-2xl font-bold text-gray-900 mb-4 group-hover:text-blue-600 transition-colors duration-200">
                                        ${event.event_title}
                                    </h2>

                                    <!-- Enhanced Event Description -->
                                    <p class="text-gray-600 text-base mb-6 line-clamp-3 leading-relaxed">
                                        ${event.event_description.length > 250 ? event.event_description.substring(0, 250) + '...' : event.event_description}
                                    </p>

                                    <!-- Enhanced Event Details -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                        <div class="space-y-4">
                                            <div class="flex items-center text-sm text-gray-600 bg-gray-50 p-3 rounded-lg">
                                                <i class="fas fa-map-marker-alt mr-3 text-blue-500 w-5"></i>
                                                <span class="font-medium">
                                                    ${event.physical_address ? event.physical_address : 
                                                      event.online_link ? `<a href="${event.online_link}" target="_blank" class="text-blue-600 hover:text-blue-800 underline">Online Event Link</a>` : 
                                                      'Location TBA'}
                                                </span>
                                            </div>
                                            <div class="flex items-center text-sm text-gray-600 bg-gray-50 p-3 rounded-lg">
                                                <i class="fas fa-user mr-3 text-green-500 w-5"></i>
                                                <span class="font-medium">Contact: ${event.contact_person}</span>
                                            </div>
                                            <div class="flex items-center text-sm text-gray-600 bg-gray-50 p-3 rounded-lg">
                                                <i class="fas fa-envelope mr-3 text-orange-500 w-5"></i>
                                                <span class="font-medium">${event.contact_email}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Enhanced Action Buttons -->
                                    <div class="flex space-x-4">
                                        <button class="flex-1 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white px-6 py-3 rounded-xl font-semibold transition-all duration-200 transform hover:scale-105 shadow-lg">
                                            <i class="fas fa-calendar-plus mr-2"></i>Register Now
                                        </button>
                                        <button class="px-6 py-3 border-2 border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 hover:border-blue-300 transition-all duration-200 transform hover:scale-105">
                                            <i class="fas fa-bookmark"></i>
                                        </button>
                                        <button class="px-6 py-3 border-2 border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 hover:border-blue-300 transition-all duration-200 transform hover:scale-105">
                                            <i class="fas fa-share"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- Enhanced Event Image Section - Right Side -->
                                <div class="flex-shrink-0">
                                    ${event.poster_image ? `
                                        <div class="relative group">
                                            <div class="w-80 h-56 rounded-2xl overflow-hidden shadow-2xl border-4 border-white transform hover:scale-105 transition-all duration-300">
                                                <img src="../admin/${event.poster_image}" 
                                                     alt="Event Poster" 
                                                     class="w-full h-full object-cover">
                                            </div>
                                            <!-- Overlay on hover -->
                                            <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-2xl flex items-end">
                                                <div class="p-4 text-white">
                                                    <p class="text-sm font-medium">Click to view full image</p>
                                                </div>
                                            </div>
                                        </div>
                                    ` : `
                                        <div class="w-80 h-56 bg-gradient-to-br from-blue-100 via-indigo-100 to-purple-100 rounded-2xl flex flex-col items-center justify-center shadow-2xl border-4 border-white">
                                            <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center mb-4 shadow-lg">
                                                <i class="fas fa-calendar-image text-white text-2xl"></i>
                                            </div>
                                            <p class="text-gray-600 font-medium text-center">Event Poster</p>
                                            <p class="text-gray-500 text-sm text-center">Coming Soon</p>
                                        </div>
                                    `}
                                    
                                    <!-- Enhanced Event Document Link -->
                                    ${event.event_document ? `
                                        <div class="mt-4 text-center">
                                            <a href="../admin/${event.event_document}" 
                                               target="_blank" 
                                               class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-xl hover:from-green-600 hover:to-emerald-700 transition-all duration-200 text-sm font-semibold shadow-lg transform hover:scale-105">
                                                <i class="fas fa-file-pdf mr-2 text-lg"></i>
                                                View Event Details
                                            </a>
                                        </div>
                                    ` : ''}
                                    
                                    <!-- Event Status Badge -->
                                    <div class="mt-4 text-center">
                                        ${is_upcoming ? `
                                            <div class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-full text-sm font-semibold shadow-lg">
                                                <i class="fas fa-star mr-2"></i>
                                                Upcoming Event
                                            </div>
                                        ` : `
                                            <div class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-gray-500 to-gray-600 text-white rounded-full text-sm font-semibold shadow-lg">
                                                <i class="fas fa-clock mr-2"></i>
                                                Past Event
                                            </div>
                                        `}
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                }).join('');
            }
        }

        // Removed update notification popup

        // Start real-time updates when page loads
        document.addEventListener('DOMContentLoaded', function() {
            // Initial fetch
            fetchAndUpdateEvents();
            
            // Set up periodic updates (every 30 seconds)
            updateInterval = setInterval(fetchAndUpdateEvents, 30000);
            
            // Also update when user becomes active (tab becomes visible)
            document.addEventListener('visibilitychange', function() {
                if (!document.hidden) {
                    fetchAndUpdateEvents();
                }
            });
        });

        // Clean up interval when page unloads
        window.addEventListener('beforeunload', function() {
            if (updateInterval) {
                clearInterval(updateInterval);
            }
        });
    </script>

    <style>
        .line-clamp-3 {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .animate-pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        
        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: .5;
            }
        }
        
        .group:hover .group-hover\:text-blue-600 {
            color: #2563eb;
        }
        
        .transform {
            transition: transform 0.3s ease-in-out;
        }
        
        .hover\:scale-105:hover {
            transform: scale(1.05);
        }
        
        .hover\:-translate-y-1:hover {
            transform: translateY(-0.25rem);
        }
    </style>
</body>
</html> 