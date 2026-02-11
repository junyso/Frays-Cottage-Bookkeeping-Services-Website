<?php
/**
 * ADMIN DASHBOARD - Document Approvals & Management
 * 
 * Admin panel to approve/reject uploaded documents
 * Access: http://localhost:8080/portal/admin.php
 */

require_once __DIR__ . '/../includes/config.php';

// Check if admin
if (!isLoggedIn() || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header('Location: /portal');
    exit;
}

$pageTitle = 'Admin Dashboard - Document Approvals';
$action = $_GET['action'] ?? 'pending';
$message = '';

// Handle approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['document_id'], $_POST['action'])) {
    $docId = (int)$_POST['document_id'];
    $action = $_POST['action'];
    $status = $action === 'approve' ? 'approved' : 'rejected';
    
    $db = getDBConnection();
    $stmt = $db->prepare("UPDATE documents SET status = ?, approved_by = ?, approved_at = NOW() WHERE id = ?");
    $stmt->execute([$status, $_SESSION['user_id'], $docId]);
    
    $message = "Document {$status} successfully!";
}

// Get documents by status
$db = getDBConnection();

$pendingStmt = $db->query("
    SELECT d.*, u.name as uploaded_by_name, u.email as uploaded_by_email
    FROM documents d
    LEFT JOIN users u ON d.uploaded_by = u.id
    WHERE d.status = 'pending'
    ORDER BY d.uploaded_at DESC
");
$pendingDocs = $pendingStmt->fetchAll();

$approvedStmt = $db->query("
    SELECT d.*, u.name as uploaded_by_name, admin.name as approved_by_name
    FROM documents d
    LEFT JOIN users u ON d.uploaded_by = u.id
    LEFT JOIN users admin ON d.approved_by = admin.id
    WHERE d.status = 'approved'
    ORDER BY d.approved_at DESC LIMIT 50
");
$approvedDocs = $approvedStmt->fetchAll();

$rejectedStmt = $db->query("
    SELECT d.*, u.name as uploaded_by_name, admin.name as approved_by_name
    FROM documents d
    LEFT JOIN users u ON d.uploaded_by = u.id
    LEFT JOIN users admin ON d.approved_by = admin.id
    WHERE d.status = 'rejected'
    ORDER BY d.approved_at DESC LIMIT 50
");
$rejectedDocs = $rejectedStmt->fetchAll();

// Stats
$stats = [
    'pending' => count($pendingDocs),
    'approved_today' => $db->query("SELECT COUNT(*) FROM documents WHERE status = 'approved' AND DATE(approved_at) = CURDATE()")->fetchColumn(),
    'rejected_today' => $db->query("SELECT COUNT(*) FROM documents WHERE status = 'rejected' AND DATE(approved_at) = CURDATE()")->fetchColumn(),
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'frays-red': '#990000',
                        'frays-yellow': '#CCCC66',
                        'frays-parchment': '#F1F1D4'
                    }
                }
            }
        }
    </script>
</head>
<body class="min-h-screen bg-gray-100">
    
    <!-- Top Bar -->
    <div class="fixed top-0 left-0 right-0 z-50 bg-frays-red text-white text-xs md:text-sm py-2 md:py-2.5 shadow-md">
        <div class="max-w-7xl mx-auto px-2">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <span>📦 Admin Dashboard</span>
                </div>
                <div class="flex items-center gap-4">
                    <span>Welcome, <?= htmlspecialchars($_SESSION['user_name'] ?? 'Admin') ?></span>
                    <a href="?action=logout" class="hover:text-frays-yellow">Logout</a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="pt-16 md:pt-20 pb-12 px-4">
        <div class="max-w-7xl mx-auto">
            
            <!-- Page Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">📋 Document Approvals</h1>
                    <p class="text-gray-600">Review and approve documents before posting to FA</p>
                </div>
                <a href="/portal" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300">
                    ← Back to Portal
                </a>
            </div>
            
            <!-- Messages -->
            <?php if ($message): ?>
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6 flex items-center gap-2">
                <i class="ri-check-line"></i> <?= htmlspecialchars($message) ?>
            </div>
            <?php endif; ?>
            
            <!-- Stats -->
            <div class="grid md:grid-cols-4 gap-4 mb-8">
                <div class="bg-white rounded-xl shadow p-6">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-full bg-yellow-100 flex items-center justify-center">
                            <i class="ri-time-line text-yellow-600 text-xl"></i>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-gray-800"><?= $stats['pending'] ?></div>
                            <div class="text-sm text-gray-600">Pending Approval</div>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow p-6">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center">
                            <i class="ri-check-double-line text-green-600 text-xl"></i>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-gray-800"><?= $stats['approved_today'] ?></div>
                            <div class="text-sm text-gray-600">Approved Today</div>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow p-6">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center">
                            <i class="ri-close-circle-line text-red-600 text-xl"></i>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-gray-800"><?= $stats['rejected_today'] ?></div>
                            <div class="text-sm text-gray-600">Rejected Today</div>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow p-6">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center">
                            <i class="ri-file-upload-line text-blue-600 text-xl"></i>
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-gray-800">
                                <?= $stats['pending'] + $stats['approved_today'] + $stats['rejected_today'] ?>
                            </div>
                            <div class="text-sm text-gray-600">Total Today</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Tabs -->
            <div class="flex gap-2 mb-6">
                <a href="?action=pending" class="px-4 py-2 rounded-lg <?= $action === 'pending' ? 'bg-frays-red text-white' : 'bg-white shadow' ?>">
                    Pending (<?= count($pendingDocs) ?>)
                </a>
                <a href="?action=approved" class="px-4 py-2 rounded-lg <?= $action === 'approved' ? 'bg-frays-red text-white' : 'bg-white shadow' ?>">
                    Approved
                </a>
                <a href="?action=rejected" class="px-4 py-2 rounded-lg <?= $action === 'rejected' ? 'bg-frays-red text-white' : 'bg-white shadow' ?>">
                    Rejected
                </a>
            </div>
            
            <!-- Pending Documents -->
            <?php if ($action === 'pending'): ?>
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <?php if (empty($pendingDocs)): ?>
                <div class="p-8 text-center text-gray-500">
                    <i class="ri-check-line text-4xl mb-4"></i>
                    <p>No documents pending approval!</p>
                </div>
                <?php else: ?>
                <table class="w-full">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-3 text-left">Reference</th>
                            <th class="px-4 py-3 text-left">Client</th>
                            <th class="px-4 py-3 text-left">Type</th>
                            <th class="px-4 py-3 text-left">Uploaded</th>
                            <th class="px-4 py-3 text-left">File</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pendingDocs as $doc): ?>
                        <tr class="border-t hover:bg-gray-50">
                            <td class="px-4 py-3 font-mono text-sm"><?= htmlspecialchars($doc['reference']) ?></td>
                            <td class="px-4 py-3"><?= htmlspecialchars($doc['client_code']) ?></td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 bg-gray-100 rounded text-sm"><?= htmlspecialchars($doc['doc_type']) ?></span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                <?= date('M d, H:i', strtotime($doc['uploaded_at'])) ?>
                            </td>
                            <td class="px-4 py-3">
                                <a href="<?= htmlspecialchars($doc['file_path']) ?>" target="_blank" 
                                   class="text-frays-red hover:underline text-sm flex items-center gap-1">
                                    <i class="ri-eye-line"></i> View
                                </a>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <form method="POST" class="inline-flex gap-2">
                                    <input type="hidden" name="document_id" value="<?= $doc['id'] ?>">
                                    <input type="hidden" name="action" value="approve">
                                    <button type="submit" class="bg-green-500 text-white px-3 py-1.5 rounded-lg hover:bg-green-600 text-sm flex items-center gap-1">
                                        <i class="ri-check-line"></i> Approve
                                    </button>
                                </form>
                                <form method="POST" class="inline-flex gap-2">
                                    <input type="hidden" name="document_id" value="<?= $doc['id'] ?>">
                                    <input type="hidden" name="action" value="reject">
                                    <button type="submit" class="bg-red-500 text-white px-3 py-1.5 rounded-lg hover:bg-red-600 text-sm flex items-center gap-1">
                                        <i class="ri-close-line"></i> Reject
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <!-- Approved Documents -->
            <?php if ($action === 'approved'): ?>
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <?php if (empty($approvedDocs)): ?>
                <div class="p-8 text-center text-gray-500">
                    <p>No approved documents yet.</p>
                </div>
                <?php else: ?>
                <table class="w-full">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-3 text-left">Reference</th>
                            <th class="px-4 py-3 text-left">Client</th>
                            <th class="px-4 py-3 text-left">Type</th>
                            <th class="px-4 py-3 text-left">Approved By</th>
                            <th class="px-4 py-3 text-left">When</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($approvedDocs as $doc): ?>
                        <tr class="border-t hover:bg-gray-50">
                            <td class="px-4 py-3 font-mono text-sm"><?= htmlspecialchars($doc['reference']) ?></td>
                            <td class="px-4 py-3"><?= htmlspecialchars($doc['client_code']) ?></td>
                            <td class="px-4 py-3"><?= htmlspecialchars($doc['doc_type']) ?></td>
                            <td class="px-4 py-3 text-sm"><?= htmlspecialchars($doc['approved_by_name'] ?? 'System') ?></td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                <?= date('M d, H:i', strtotime($doc['approved_at'])) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <!-- Rejected Documents -->
            <?php if ($action === 'rejected'): ?>
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <?php if (empty($rejectedDocs)): ?>
                <div class="p-8 text-center text-gray-500">
                    <p>No rejected documents.</p>
                </div>
                <?php else: ?>
                <table class="w-full">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-3 text-left">Reference</th>
                            <th class="px-4 py-3 text-left">Client</th>
                            <th class="px-4 py-3 text-left">Type</th>
                            <th class="px-4 py-3 text-left">Rejected By</th>
                            <th class="px-4 py-3 text-left">When</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rejectedDocs as $doc): ?>
                        <tr class="border-t hover:bg-gray-50">
                            <td class="px-4 py-3 font-mono text-sm"><?= htmlspecialchars($doc['reference']) ?></td>
                            <td class="px-4 py-3"><?= htmlspecialchars($doc['client_code']) ?></td>
                            <td class="px-4 py-3"><?= htmlspecialchars($doc['doc_type']) ?></td>
                            <td class="px-4 py-3 text-sm"><?= htmlspecialchars($doc['approved_by_name'] ?? 'System') ?></td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                <?= date('M d, H:i', strtotime($doc['approved_at'])) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
        </div>
    </div>
    
</body>
</html>
