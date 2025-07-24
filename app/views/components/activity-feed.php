<?php
/**
 * Activity Feed Component
 * Usage: include with $activities array
 */

$activities = $activities ?? [];
$showUserNames = $showUserNames ?? true;
$maxItems = $maxItems ?? 10;
$emptyMessage = $emptyMessage ?? 'No recent activity';
?>

<div class="bg-white shadow-sm rounded-lg border border-gray-200">
    <div class="p-6 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
            <i class="fas fa-history text-gray-500 mr-2"></i>
            Recent Activity
        </h3>
    </div>
    <div class="p-6">
        <?php if (!empty($activities)): ?>
            <div class="flow-root">
                <ul class="-mb-8">
                    <?php foreach (array_slice($activities, 0, $maxItems) as $index => $activity): ?>
                        <li>
                            <div class="relative pb-8">
                                <?php if ($index < count($activities) - 1 && $index < $maxItems - 1): ?>
                                    <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                <?php endif; ?>
                                <div class="relative flex space-x-3">
                                    <div>
                                        <span class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white
                                            <?php 
                                            switch($activity['action']) {
                                                case 'login': echo 'bg-green-500'; break;
                                                case 'logout': echo 'bg-gray-500'; break;
                                                case 'create_employee': echo 'bg-blue-500'; break;
                                                case 'update_employee': echo 'bg-yellow-500'; break;
                                                case 'process_payroll': echo 'bg-purple-500'; break;
                                                default: echo 'bg-gray-500';
                                            }
                                            ?>">
                                            <i class="fas 
                                                <?php 
                                                switch($activity['action']) {
                                                    case 'login': echo 'fa-sign-in-alt'; break;
                                                    case 'logout': echo 'fa-sign-out-alt'; break;
                                                    case 'create_employee': echo 'fa-user-plus'; break;
                                                    case 'update_employee': echo 'fa-user-edit'; break;
                                                    case 'process_payroll': echo 'fa-calculator'; break;
                                                    default: echo 'fa-circle';
                                                }
                                                ?> text-white text-xs"></i>
                                        </span>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div>
                                            <?php if ($showUserNames && isset($activity['full_name'])): ?>
                                                <div class="text-sm">
                                                    <span class="font-medium text-gray-900">
                                                        <?php echo htmlspecialchars($activity['full_name']); ?>
                                                    </span>
                                                </div>
                                            <?php endif; ?>
                                            <p class="mt-0.5 text-sm text-gray-500">
                                                <?php echo $this->formatActivityMessage($activity); ?>
                                            </p>
                                        </div>
                                        <div class="mt-2 text-sm text-gray-500">
                                            <time datetime="<?php echo $activity['created_at']; ?>">
                                                <?php echo $this->timeAgo($activity['created_at']); ?>
                                            </time>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php else: ?>
            <div class="text-center text-gray-500 py-8">
                <i class="fas fa-history text-3xl mb-3"></i>
                <p class="text-sm"><?php echo htmlspecialchars($emptyMessage); ?></p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
// Helper function to format activity messages
function formatActivityMessage($activity) {
    $messages = [
        'login' => 'Logged into the system',
        'logout' => 'Logged out of the system',
        'create_employee' => 'Created a new employee',
        'update_employee' => 'Updated employee information',
        'delete_employee' => 'Deleted an employee',
        'process_payroll' => 'Processed payroll',
        'create_loan' => 'Created a new loan',
        'loan_payment' => 'Recorded loan payment',
        'mark_attendance' => 'Marked attendance',
        'generate_report' => 'Generated a report',
        'create_backup' => 'Created system backup',
        'update_settings' => 'Updated system settings'
    ];
    
    $action = $activity['action'];
    $baseMessage = $messages[$action] ?? ucwords(str_replace('_', ' ', $action));
    
    if ($activity['table_name'] && $activity['record_id']) {
        $baseMessage .= " (ID: {$activity['record_id']})";
    }
    
    return $baseMessage;
}

// Helper function to format time ago
function timeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'just now';
    if ($time < 3600) return floor($time/60) . ' minutes ago';
    if ($time < 86400) return floor($time/3600) . ' hours ago';
    if ($time < 2592000) return floor($time/86400) . ' days ago';
    
    return date('M j, Y', strtotime($datetime));
}
?>