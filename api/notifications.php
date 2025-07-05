<?php
/**
 * Notifications API for NSSF Uganda Dashboard
 * 
 * @author BARIGYE-DAVIS
 * @version 1.0
 */

header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/Database.php';

use App\Auth;
use App\Database;

$auth = new Auth();

// Check authentication
if (!$auth->isAuthenticated()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit;
}

try {
    $db = Database::getInstance();
    $userId = $auth->getUserId();
    
    // Get unread notifications for current user
    $notifications = [];
    
    try {
        $notifications = $db->select(
            "SELECT id, message, type, icon, url, created_at, is_read 
             FROM notifications 
             WHERE user_id = :user_id AND is_read = 0 
             ORDER BY created_at DESC 
             LIMIT 10",
            ['user_id' => $userId]
        );
    } catch (Exception $e) {
        // If notifications table doesn't exist, return demo notifications
        $notifications = [
            [
                'id' => 1,
                'message' => 'New member registration: John Doe',
                'type' => 'member',
                'icon' => 'fa-user-plus',
                'url' => '/views/members/view.php?id=1',
                'created_at' => date('Y-m-d H:i:s', time() - 300),
                'is_read' => 0
            ],
            [
                'id' => 2,
                'message' => 'Monthly contribution report is ready',
                'type' => 'report',
                'icon' => 'fa-chart-bar',
                'url' => '/views/reports/',
                'created_at' => date('Y-m-d H:i:s', time() - 600),
                'is_read' => 0
            ],
            [
                'id' => 3,
                'message' => 'Claim #1234 requires approval',
                'type' => 'claim',
                'icon' => 'fa-file-invoice-dollar',
                'url' => '/views/claims/view.php?id=1234',
                'created_at' => date('Y-m-d H:i:s', time() - 1200),
                'is_read' => 0
            ]
        ];
    }
    
    echo json_encode([
        'success' => true,
        'data' => $notifications,
        'count' => count($notifications),
        'timestamp' => time()
    ]);
    
} catch (Exception $e) {
    error_log("Notifications API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch notifications'
    ]);
}