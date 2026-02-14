<?php
/**
 * Document AI Main Entry Point
 * Unified dashboard for all Document AI operations
 */

require_once __DIR__ . '/../includes/config.php';

// Initialize components
$processor = new DocumentProcessor();
$learningEngine = new AutoSuggestLearningEngine();
$vatEngine = new VATIntelligence();
$bankProcessor = new BankStatementProcessor();

// Handle AJAX requests
if (isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        // ========== DOCUMENT PROCESSING ==========
        case 'process_document':
            $result = $processor->processDocument(
                $_FILES['document']['tmp_name'],
                $_FILES['document']['name']
            );
            echo json_encode($result);
            break;
            
        case 'batch_upload':
            $result = $processor->batchProcess($_FILES['documents'] ?? []);
            echo json_encode($result);
            break;
            
        case 'get_pending':
            echo json_encode($processor->getPendingDocuments());
            break;
            
        // ========== LEARNING ENGINE ==========
        case 'learn_correction':
            $original = json_decode($_POST['original'] ?? '{}', true);
            $correction = json_decode($_POST['correction'] ?? '{}', true);
            echo json_encode($learningEngine->learnFromCorrection($original, $correction));
            break;
            
        case 'get_prediction':
            $data = json_decode($_POST['data'] ?? '{}', true);
            echo json_encode($learningEngine->getPrediction($data) ?: ['message' => 'No match']);
            break;
            
        case 'get_learning_stats':
            echo json_encode($learningEngine->getStatistics());
            break;
            
        // ========== VAT PROCESSING ==========
        case 'extract_vat':
            $text = $_POST['text'] ?? '';
            $filename = $_POST['filename'] ?? '';
            echo json_encode($vatEngine->extractVAT($text, $filename));
            break;
            
        case 'vat_report':
            $documents = json_decode($_POST['documents'] ?? '[]', true);
            echo json_encode($vatEngine->generateVATReport($documents));
            break;
            
        // ========== BANK STATEMENTS ==========
        case 'process_statement':
            $result = $bankProcessor->processStatement(
                $_FILES['statement']['tmp_name'],
                $_FILES['statement']['name']
            );
            echo json_encode($result);
            break;
            
        case 'match_transactions':
            $transactions = json_decode($_POST['transactions'] ?? '[]', true);
            $database = $_POST['database'] ?? '';
            echo json_encode($bankProcessor->matchToInvoices($transactions, $database));
            break;
            
        // ========== GENERAL ==========
        case 'get_stats':
            echo json_encode([
                'pending' => count($processor->getPendingDocuments()),
                'learned_rules' => $learningEngine->getStatistics()['total_corrections'] ?? 0
            ]);
            break;
            
        default:
            echo json_encode(['error' => 'Unknown action']);
    }
    exit;
}

// For GET requests, show the main dashboard
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document AI Portal - Frays Cottage</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #990000;
            --secondary: #CCCC66;
            --light: #F1F1D4;
            --white: #FFFFFF;
        }
        .bg-primary { background-color: var(--primary); }
        .bg-secondary { background-color: var(--secondary); }
        .bg-light { background-color: var(--light); }
        .text-primary { color: var(--primary); }
        .border-primary { border-color: var(--primary); }
    </style>
</head>
<body class="bg-light min-h-screen">

    <!-- Navigation -->
    <nav class="bg-primary text-white shadow-lg">
        <div class="container mx-auto px-6 py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center">
                    <i class="fas fa-robot text-2xl mr-3"></i>
                    <h1 class="text-xl font-bold">Document AI Portal</h1>
                </div>
                <div class="flex gap-4">
                    <span class="text-sm">Frays Cottage Bookkeeping</span>
                    <a href="../" class="btn-secondary px-4 py-2 rounded text-sm">
                        <i class="fas fa-home mr-2"></i>Home
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-6 py-8">
        
        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-primary">
                <p class="text-gray-500 text-sm">Pending Review</p>
                <p class="text-3xl font-bold text-primary" id="stat-pending">0</p>
            </div>
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
                <p class="text-gray-500 text-sm">Processed Today</p>
                <p class="text-3xl font-bold text-green-600">24</p>
            </div>
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-yellow-500">
                <p class="text-gray-500 text-sm">Learning Rules</p>
                <p class="text-3xl font-bold text-yellow-600" id="stat-rules">0</p>
            </div>
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
                <p class="text-gray-500 text-sm">Posted to FA</p>
                <p class="text-3xl font-bold text-blue-600">18</p>
            </div>
        </div>

        <!-- Main Tabs -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="flex border-b">
                <button onclick="showTab('upload')" class="tab-btn px-6 py-4 text-primary border-b-2 border-primary font-medium">
                    <i class="fas fa-cloud-upload-alt mr-2"></i>Upload
                </button>
                <button onclick="showTab('review')" class="tab-btn px-6 py-4 text-gray-500 hover:text-primary">
                    <i class="fas fa-tasks mr-2"></i>Review
                </button>
                <button onclick="showTab('bank')" class="tab-btn px-6 py-4 text-gray-500 hover:text-primary">
                    <i class="fas fa-university mr-2"></i>Bank Statements
                </button>
                <button onclick="showTab('learning')" class="tab-btn px-6 py-4 text-gray-500 hover:text-primary">
                    <i class="fas fa-brain mr-2"></i>Learning Engine
                </button>
                <button onclick="showTab('settings')" class="tab-btn px-6 py-4 text-gray-500 hover:text-primary">
                    <i class="fas fa-cog mr-2"></i>Settings
                </button>
            </div>
        </div>

        <!-- Tab Contents -->
        
        <!-- Upload Tab -->
        <div id="tab-upload" class="tab-content">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Upload Area -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-bold text-primary mb-4">
                        <i class="fas fa-file-upload mr-2"></i>Document Upload
                    </h2>
                    <div id="upload-area" class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-primary cursor-pointer">
                        <i class="fas fa-cloud-upload-alt text-5xl text-gray-300 mb-4"></i>
                        <p class="text-gray-600">Drag & drop files here or click to browse</p>
                        <p class="text-sm text-gray-400">PDF, JPG, PNG (Max 20MB each)</p>
                        <input type="file" id="file-input" multiple class="hidden">
                    </div>
                    <div id="upload-progress" class="hidden mt-4">
                        <div class="w-full bg-gray-200 rounded-full h-4">
                            <div id="progress-bar" class="bg-primary rounded-full h-4 transition-all" style="width: 0%"></div>
                        </div>
                        <p class="text-sm text-gray-600 mt-2" id="progress-text">Processing...</p>
                    </div>
                </div>

                <!-- Guidelines -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-bold text-primary mb-4">
                        <i class="fas fa-info-circle mr-2"></i>Guidelines
                    </h2>
                    <ul class="text-gray-600 space-y-2 text-sm">
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Max 20 files per upload</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Supported: PDF, JPG, PNG, GIF</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>AI auto-detects document type</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>GL accounts suggested automatically</li>
                        <li><i class="fas fa-check text-green-500 mr-2"></i>Duplicates detected & flagged</li>
                    </ul>
                    <div class="mt-6 bg-yellow-50 border border-yellow-200 rounded p-4">
                        <p class="text-yellow-800 text-sm">
                            <i class="fas fa-lightbulb mr-2"></i>
                            <strong>Tip:</strong> AI learns from your corrections! The more you correct, the smarter it gets.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Review Tab -->
        <div id="tab-review" class="tab-content hidden">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold text-primary">
                        <i class="fas fa-list mr-2"></i>Pending Documents
                    </h2>
                    <div class="flex gap-2">
                        <button onclick="refreshDocuments()" class="btn-primary px-4 py-2 rounded text-sm">
                            <i class="fas fa-sync mr-1"></i>Refresh
                        </button>
                        <button onclick="generateCSV()" class="bg-green-600 text-white px-4 py-2 rounded text-sm">
                            <i class="fas fa-file-csv mr-1"></i>Export
                        </button>
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-2 text-left">File</th>
                                <th class="px-4 py-2 text-left">Type</th>
                                <th class="px-4 py-2 text-left">GL Account</th>
                                <th class="px-4 py-2 text-left">Supplier</th>
                                <th class="px-4 py-2 text-left">Amount</th>
                                <th class="px-4 py-2 text-left">Confidence</th>
                                <th class="px-4 py-2 text-left">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="documents-table">
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                    <i class="fas fa-inbox text-4xl mb-2"></i>
                                    <p>No pending documents</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Bank Statements Tab -->
        <div id="tab-bank" class="tab-content hidden">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-bold text-primary mb-4">
                    <i class="fas fa-university mr-2"></i>Bank Statement Processing
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-gray-700 mb-2">Upload Bank Statement</label>
                        <input type="file" id="statement-input" accept=".pdf,.csv" class="w-full border rounded px-4 py-2">
                        <button onclick="processStatement()" class="btn-primary px-4 py-2 rounded mt-4">
                            <i class="fas fa-cogs mr-2"></i>Process Statement
                        </button>
                    </div>
                    <div class="bg-gray-50 rounded p-4">
                        <h3 class="font-bold mb-2">Processed Statements</h3>
                        <p class="text-gray-500 text-sm">No statements processed yet.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Learning Engine Tab -->
        <div id="tab-learning" class="tab-content hidden">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-bold text-primary mb-4">
                    <i class="fas fa-brain mr-2"></i>AI Learning Engine
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-blue-50 rounded p-4">
                        <p class="text-3xl font-bold text-blue-600" id="learning-rules">0</p>
                        <p class="text-gray-600">Learned Rules</p>
                    </div>
                    <div class="bg-green-50 rounded p-4">
                        <p class="text-3xl font-bold text-green-600">85%</p>
                        <p class="text-gray-600">Accuracy</p>
                    </div>
                    <div class="bg-yellow-50 rounded p-4">
                        <p class="text-3xl font-bold text-yellow-600">12</p>
                        <p class="text-gray-600">Corrections Today</p>
                    </div>
                </div>
                <div class="mt-6">
                    <h3 class="font-bold mb-2">Top Learned Patterns</h3>
                    <div id="learned-patterns" class="space-y-2">
                        <p class="text-gray-500 text-sm">No patterns learned yet.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Settings Tab -->
        <div id="tab-settings" class="tab-content hidden">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-bold text-primary mb-4">
                    <i class="fas fa-cog mr-2"></i>Settings
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="font-bold mb-2">FrontAccounting Connection</h3>
                        <select id="fa-database" class="w-full border rounded px-4 py-2 mb-3">
                            <option value="">Select Database...</option>
                            <option value="frayscottage">Frays Cottage</option>
                            <option value="dudubrook">Dudubrook</option>
                        </select>
                        <button class="btn-primary px-4 py-2 rounded">Test Connection</button>
                    </div>
                    <div>
                        <h3 class="font-bold mb-2">Email Processing</h3>
                        <div class="flex items-center mb-3">
                            <input type="checkbox" id="email-enabled" class="mr-2">
                            <label for="email-enabled">Enable uploads@bookkeeping.co.bw</label>
                        </div>
                        <p class="text-sm text-gray-500">Documents emailed to this address are auto-processed</p>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-6 mt-8">
        <div class="container mx-auto px-6 text-center">
            <p>&copy; 2026 Frays Cottage Bookkeeping Services. All rights reserved.</p>
            <p class="text-sm text-gray-400 mt-2">
                Powered by AI Document Processing
            </p>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Tab functionality
        function showTab(tabName) {
            $('.tab-content').addClass('hidden');
            $('.tab-btn').removeClass('text-primary border-b-2 border-primary');
            $('.tab-btn').addClass('text-gray-500');
            
            $('#tab-' + tabName).removeClass('hidden');
            event.target.classList.remove('text-gray-500');
            event.target.classList.add('text-primary', 'border-b-2', 'border-primary');
        }

        // File upload handlers
        $('#upload-area').click(() => $('#file-input').click());
        $('#file-input').change(handleFileSelect);
        
        $('#upload-area').on('dragover', (e) => {
            e.preventDefault();
            $('#upload-area').addClass('border-primary');
        });
        
        $('#upload-area').on('dragleave', () => $('#upload-area').removeClass('border-primary'));
        $('#upload-area').on('drop', (e) => {
            e.preventDefault();
            $('#upload-area').removeClass('border-primary');
            handleFiles(e.originalEvent.dataTransfer.files);
        });

        function handleFileSelect(e) {
            handleFiles(e.target.files);
        }

        function handleFiles(files) {
            $('#upload-progress').removeClass('hidden');
            
            const formData = new FormData();
            for (let i = 0; i < files.length; i++) {
                formData.append('documents[]', files[i]);
            }
            formData.append('action', 'batch_upload');

            $.ajax({
                url: 'enhanced-upload.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                xhr: () => {
                    const xhr = new XMLHttpRequest();
                    xhr.upload.addEventListener('progress', (e) => {
                        if (e.lengthComputable) {
                            $('#progress-bar').css('width', (e.loaded / e.total * 100) + '%');
                            $('#progress-text').text('Processing ' + Math.round(e.loaded / e.total * 100) + '%');
                        }
                    });
                    return xhr;
                },
                success: (response) => {
                    const result = JSON.parse(response);
                    $('#progress-text').text('Complete! ' + result.documents?.length + ' files processed.');
                    refreshDocuments();
                    loadStats();
                    setTimeout(() => {
                        $('#upload-progress').addClass('hidden');
                        $('#progress-bar').css('width', '0%');
                    }, 3000);
                }
            });
        }

        function refreshDocuments() {
            $.post('index.php', { action: 'get_pending' }, (response) => {
                const docs = JSON.parse(response);
                renderDocuments(docs);
            });
        }

        function renderDocuments(docs) {
            const tbody = $('#documents-table');
            if (docs.length === 0) {
                tbody.html('<tr><td colspan="7" class="px-4 py-8 text-center text-gray-500"><i class="fas fa-inbox text-4xl mb-2"></i><p>No pending documents</p></td></tr>');
                return;
            }
            tbody.html(docs.map((doc, i) => `
                <tr class="border-b hover:bg-gray-50">
                    <td class="px-4 py-2">${doc.filename}</td>
                    <td class="px-4 py-2"><span class="px-2 py-1 rounded text-xs bg-blue-100 text-blue-800">${doc.type?.toUpperCase()}</span></td>
                    <td class="px-4 py-2">${doc.mapping?.gl_account || '-'}</td>
                    <td class="px-4 py-2">${doc.mapping?.supplier || '-'}</td>
                    <td class="px-4 py-2">${doc.mapping?.amount || '-'}</td>
                    <td class="px-4 py-2"><span class="px-2 py-1 rounded text-xs ${(doc.classification?.confidence || 0) > 0.8 ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'}">${Math.round((doc.classification?.confidence || 0) * 100)}%</span></td>
                    <td class="px-4 py-2">
                        <button class="text-primary mr-2"><i class="fas fa-edit"></i></button>
                        <button class="text-green-600"><i class="fas fa-check"></i></button>
                    </td>
                </tr>
            `).join(''));
        }

        function loadStats() {
            $.post('index.php', { action: 'get_stats' }, (response) => {
                const stats = JSON.parse(response);
                $('#stat-pending').text(stats.pending || 0);
                $('#stat-rules').text(stats.learned_rules || 0);
            });
        }

        function generateCSV() {
            alert('CSV export would download here');
        }

        function processStatement() {
            alert('Bank statement processing would start here');
        }

        // Load stats on page load
        $(document).ready(() => {
            loadStats();
            loadLearningStats();
        });

        function loadLearningStats() {
            $.post('index.php', { action: 'get_learning_stats' }, (response) => {
                const stats = JSON.parse(response);
                $('#learning-rules').text(stats.total_corrections || 0);
            });
        }
    </script>
</body>
</html>
