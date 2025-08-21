<?php
// Session initialization - must be included at the very beginning of files
if (session_status() === PHP_SESSION_NONE) {
    // Set session timeout to 24 hours (86400 seconds)
    ini_set('session.gc_maxlifetime', 86400);
    ini_set('session.cookie_lifetime', 86400);
    
    // Start session
    session_start();
}
?> 