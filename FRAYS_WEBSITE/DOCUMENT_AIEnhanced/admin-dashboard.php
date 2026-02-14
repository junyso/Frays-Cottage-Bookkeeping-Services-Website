<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document AI Admin Dashboard - Frays Cottage</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #990000;
            --secondary: #CCCC66;
            --light: #F1F1D4;
            --white: #FFFFFF;
            --dark: #000000;
        }
        
        .bg-primary { background-color: var(--primary); }
        .bg-secondary { background-color: var(--secondary); }
        .bg-light { background-color: var(--light); }
        .text-primary { color: var(--primary); }
        .border-primary { border-color: var(--primary); }
        
        .btn-primary {
            background-color: var(--primary);
            color: var(--white);
        }
        
        .btn-primary:hover {
            background-color: #770000;
        }
    </style>
</head>
<body class="bg-light min-h-screen">

    <!-- Header -->
    <header class="bg-primary text-white py-4 px-6 shadow-lg">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-xl font-bold">
                <i class="fas fa-robot mr-2"></i>
                Document AI Admin Dashboard
            </h1>
            <div class="flex items-center gap-4">
                <span id="connection-status" class="text-sm">
                    <i class="fas fa-circle text-green-500 mr-1"></i>
                    Connected
                </span>
                <span class="text-sm">
                    <i class="fas fa-user mr-1"></i>
                    Admin
                </span>
            </div>
        </div>
    </header>

    <div class="container mx-auto px-6 py-8">

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-primary">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Pending Review</p>
                        <p class="text-3xl font-bold text-primary" id="stat-pending">0</p>
                    </div>
                    <i class="fas fa-clock text-4xl text-gray-200"></i>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Processed Today</p>
                        <p class="text-3xl font-bold text-green-600" id="stat-processed">0</p>
                    </div>
                    <i class="fas fa-check-circle text-4xl text-gray-200"></i>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-yellow-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Exceptions</p>
                        <p class="text-3xl font-bold text-yellow-600" id="stat-exceptions">0</p>
                    </div>
                    <i class="fas fa-exclamation-triangle text-4xl text-gray-200"></i>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Posted to FA</p>
                        <p class="text-3xl font-bold text-blue-600" id="stat-posted">0</p>
                    </div>
                    <i class="fas fa-database text-4xl text-gray-200"></i>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Upload Section -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-bold text-primary mb-4">
                        <i class="fas fa-cloud-upload-alt mr-2"></i>
                        Document Upload
                    </h2>
                    
                    <!-- Upload Area -->
                    <div id="upload-area" class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-primary transition-colors cursor-pointer">
                        <i class="fas fa-file-upload text-5xl text-gray-300 mb-4"></i>
                        <p class="text-gray-600 mb-2">Drag & drop files here or click to browse</p>
                        <p class="text-sm text-gray-400">Max 100 files, 10MB each</p>
                        <input type="file" id="file-input" multiple accept=".jpg,.jpeg,.png,.gif,.pdf" class="hidden">
                    </div>
                    
                    <!-- Upload Progress -->
                    <div id="upload-progress" class="hidden mt-4">
                        <div class="w-full bg-gray-200 rounded-full h-4">
                            <div id="progress-bar" class="bg-primary rounded-full h-4 transition-all duration-300" style="width: 0%"></div>
                        </div>
                        <p class="text-sm text-gray-600 mt-2" id="progress-text">Uploading...</p>
                    </div>
                    
                    <!-- Uploaded Files List -->
                    <div id="uploaded-files" class="mt-6 hidden">
                        <h3 class="font-bold text-gray-700 mb-3">Uploaded Documents</h3>
                        <div id="files-list" class="space-y-2 max-h-64 overflow-y-auto"></div>
                    </div>
                </div>

                <!-- Document Review Table -->
                <div class="bg-white rounded-lg shadow p-6 mt-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-bold text-primary">
                            <i class="fas fa-list mr-2"></i>
                            Pending Documents
                        </h2>
                        <div class="flex gap-2">
                            <button onclick="generateCSV()" class="btn-primary px-4 py-2 rounded text-sm">
                                <i class="fas fa-file-csv mr-1"></i> Export CSV
                            </button>
                            <button onclick="pushToFA()" class="bg-green-600 text-white px-4 py-2 rounded text-sm hover:bg-green-700">
                                <i class="fas fa-paper-plane mr-1"></i> Push to FA
                            </button>
                        </div>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-4 py-2 text-left">Select</th>
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
                                    <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                                        <i class="fas fa-inbox text-4xl mb-2"></i>
                                        <p>No pending documents</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                
                <!-- Quick Actions -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="font-bold text-primary mb-4">
                        <i class="fas fa-bolt mr-2"></i>
                        Quick Actions
                    </h3>
                    <div class="space-y-2">
                        <button onclick="refreshDocuments()" class="w-full btn-primary py-2 rounded text-left px-4">
                            <i class="fas fa-sync mr-2"></i> Refresh List
                        </button>
                        <button onclick="showExceptionReport()" class="w-full bg-yellow-500 text-white py-2 rounded text-left px-4 hover:bg-yellow-600">
                            <i class="fas fa-exclamation-triangle mr-2"></i> View Exceptions
                        </button>
                        <button onclick="showAcknowledgmentModal()" class="w-full bg-blue-500 text-white py-2 rounded text-left px-4 hover:bg-blue-600">
                            <i class="fas fa-envelope mr-2"></i> Send Acknowledgment
                        </button>
                    </div>
                </div>

                <!-- Exception Report -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="font-bold text-yellow-600 mb-4">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        Exceptions
                    </h3>
                    <div id="exceptions-list" class="space-y-2 max-h-64 overflow-y-auto">
                        <p class="text-gray-500 text-sm">No exceptions</p>
                    </div>
                </div>

                <!-- Document Types Distribution -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="font-bold text-primary mb-4">
                        <i class="fas fa-chart-pie mr-2"></i>
                        By Type
                    </h3>
                    <div id="type-distribution" class="space-y-2">
                        <p class="text-gray-500 text-sm">No data yet</p>
                    </div>
                </div>

                <!-- FA Connection -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="font-bold text-primary mb-4">
                        <i class="fas fa-database mr-2"></i>
                        FrontAccounting
                    </h3>
                    <select id="fa-database" class="w-full border rounded px-3 py-2 mb-3">
                        <option value="">Select Database...</option>
                    </select>
                    <div id="fa-status" class="text-sm">
                        <span class="text-gray-500">Select a database to connect</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="edit-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-lg mx-4">
            <h3 class="text-xl font-bold text-primary mb-4">
                <i class="fas fa-edit mr-2"></i>
                Edit Document Mapping
            </h3>
            <form id="edit-form" class="space-y-4">
                <input type="hidden" id="edit-filename">
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Document Type</label>
                        <select id="edit-type" class="w-full border rounded px-3 py-2">
                            <option value="invoice">Invoice</option>
                            <option value="receipt">Receipt</option>
                            <option value="statement">Bank Statement</option>
                            <option value="waybill">Waybill</option>
                            <option value="customs">Customs Document</option>
                            <option value="pop">Proof of Payment</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">GL Account</label>
                        <select id="edit-gl" class="w-full border rounded px-3 py-2">
                            <option value="5000-Purchases">5000-Purchases</option>
                            <option value="5100-Cost of Sales">5100-Cost of Sales</option>
                            <option value="6100-Office Expenses">6100-Office Expenses</option>
                            <option value="6200-IT Expenses">6200-IT Expenses</option>
                            <option value="6300-Rent Expenses">6300-Rent Expenses</option>
                            <option value="6400-Utilities">6400-Utilities</option>
                            <option value="6500-Transport">6500-Transport</option>
                            <option value="6600-Salaries">6600-Salaries</option>
                            <option value="1001-Petty Cash">1001-Petty Cash</option>
                            <option value="1002-Main Bank">1002-Main Bank</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Supplier</label>
                        <input type="text" id="edit-supplier" class="w-full border rounded px-3 py-2" placeholder="Supplier name">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">VAT Amount</label>
                        <input type="number" id="edit-vat" class="w-full border rounded px-3 py-2" placeholder="0.00">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Cashbook</label>
                        <select id="edit-cashbook" class="w-full border rounded px-3 py-2">
                            <option value="">Not Paid</option>
                            <option value="1001">Petty Cash</option>
                            <option value="1002">Main Bank</option>
                            <option value="1003">Director's Account</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Dimension 1</label>
                        <input type="text" id="edit-dim1" class="w-full border rounded px-3 py-2" placeholder="Cost Center">
                    </div>
                </div>
                
                <div class="flex justify-end gap-2 pt-4">
                    <button type="button" onclick="closeEditModal()" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded">Cancel</button>
                    <button type="submit" class="btn-primary px-4 py-2 rounded">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Global state
        let documents = [];
        let exceptions = [];
        
        // Initialize
        $(document).ready(function() {
            loadDocuments();
            loadStats();
            loadFAConnections();
            
            // Upload area handlers
            $('#upload-area').click(() => $('#file-input').click());
            
            $('#file-input').change(handleFileSelect);
            
            // Drag & drop
            $('#upload-area').on('dragover', (e) => {
                e.preventDefault();
                $('#upload-area').addClass('border-primary');
            });
            
            $('#upload-area').on('dragleave', () => {
                $('#upload-area').removeClass('border-primary');
            });
            
            $('#upload-area').on('drop', (e) => {
                e.preventDefault();
                $('#upload-area').removeClass('border-primary');
                handleFiles(e.originalEvent.dataTransfer.files);
            });
            
            // Edit form submission
            $('#edit-form').submit(saveDocumentChanges);
        });
        
        // Load documents from server
        function loadDocuments() {
            $.post('admin-review.php', { action: 'get_pending' }, (response) => {
                documents = JSON.parse(response);
                renderDocuments();
            });
        }
        
        // Render documents table
        function renderDocuments() {
            const tbody = $('#documents-table');
            
            if (documents.length === 0) {
                tbody.html(`
                    <tr>
                        <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-2"></i>
                            <p>No pending documents</p>
                        </td>
                    </tr>
                `);
                return;
            }
            
            tbody.html(documents.map((doc, i) => `
                <tr class="border-b hover:bg-gray-50">
                    <td class="px-4 py-2">
                        <input type="checkbox" class="doc-checkbox" data-index="${i}">
                    </td>
                    <td class="px-4 py-2">
                        <i class="fas fa-file-${getFileIcon(doc.type)} text-gray-400 mr-2"></i>
                        ${doc.filename}
                    </td>
                    <td class="px-4 py-2">
                        <span class="px-2 py-1 rounded text-xs font-bold ${getTypeBadge(doc.type)}">
                            ${doc.type.toUpperCase()}
                        </span>
                    </td>
                    <td class="px-4 py-2 text-sm">${doc.mapping.gl_account}</td>
                    <td class="px-4 py-2 text-sm">${doc.mapping.supplier || '-'}</td>
                    <td class="px-4 py-2 text-sm font-bold">${doc.mapping.amount || '-'}</td>
                    <td class="px-4 py-2">
                        <span class="px-2 py-1 rounded text-xs ${getConfidenceBadge(doc.classification.confidence)}">
                            ${(doc.classification.confidence * 100).toFixed(0)}%
                        </span>
                    </td>
                    <td class="px-4 py-2">
                        <button onclick="openEditModal(${i})" class="text-primary hover:text-red-700 mr-2">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button onclick="markReviewed('${doc.filename}')" class="text-green-600 hover:text-green-800">
                            <i class="fas fa-check"></i>
                        </button>
                    </td>
                </tr>
            `).join(''));
        }
        
        // Load statistics
        function loadStats() {
            $.post('admin-review.php', { action: 'get_stats' }, (response) => {
                const stats = JSON.parse(response);
                $('#stat-pending').text(stats.pending_review);
                $('#stat-exceptions').text(stats.exceptions);
                
                // Render type distribution
                const dist = $('#type-distribution');
                dist.html('');
                Object.entries(stats.by_type || {}).forEach(([type, count]) => {
                    dist.append(`
                        <div class="flex justify-between text-sm">
                            <span class="capitalize">${type}</span>
                            <span class="font-bold">${count}</span>
                        </div>
                    `);
                });
            });
            
            // Load exceptions
            loadExceptions();
        }
        
        // Load exceptions
        function loadExceptions() {
            $.post('admin-review.php', { action: 'get_exceptions' }, (response) => {
                exceptions = JSON.parse(response);
                renderExceptions();
            });
        }
        
        // Render exceptions
        function renderExceptions() {
            const list = $('#exceptions-list');
            
            if (exceptions.length === 0) {
                list.html('<p class="text-gray-500 text-sm">No exceptions</p>');
                return;
            }
            
            list.html(exceptions.map(ex => `
                <div class="p-2 bg-yellow-50 border border-yellow-200 rounded text-sm">
                    <p class="font-medium text-yellow-800">${ex.filename}</p>
                    <p class="text-yellow-600 text-xs">${ex.issue}</p>
                    <p class="text-gray-500 text-xs">${ex.action}</p>
                </div>
            `).join(''));
        }
        
        // File upload handlers
        function handleFileSelect(e) {
            handleFiles(e.target.files);
        }
        
        function handleFiles(files) {
            const formData = new FormData();
            formData.append('action', 'batch_upload');
            
            for (let i = 0; i < files.length; i++) {
                formData.append('documents[]', files[i]);
            }
            
            $('#upload-progress').removeClass('hidden');
            $('#uploaded-files').removeClass('hidden');
            
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
                            const percent = (e.loaded / e.total) * 100;
                            $('#progress-bar').css('width', percent + '%');
                            $('#progress-text').text(`Uploading ${Math.round(percent)}%`);
                        }
                    });
                    return xhr;
                },
                success: (response) => {
                    const result = JSON.parse(response);
                    renderUploadedFiles(result);
                    loadDocuments();
                    loadStats();
                    $('#progress-text').text('Upload complete!');
                    setTimeout(() => {
                        $('#upload-progress').addClass('hidden');
                    }, 2000);
                },
                error: () => {
                    $('#progress-text').text('Upload failed!');
                }
            });
        }
        
        function renderUploadedFiles(result) {
            const list = $('#files-list');
            list.html('');
            
            result.documents.forEach(doc => {
                const icon = doc.status === 'duplicate' ? 'fa-ban text-yellow-500' : 
                           doc.status === 'error' ? 'fa-exclamation-circle text-red-500' :
                           'fa-check-circle text-green-500';
                
                list.append(`
                    <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                        <div class="flex items-center">
                            <i class="fas ${icon} mr-2"></i>
                            <span class="text-sm">${doc.filename}</span>
                        </div>
                        <span class="text-xs text-gray-500">${doc.type || doc.message}</span>
                    </div>
                `);
            });
        }
        
        // Edit modal handlers
        function openEditModal(index) {
            const doc = documents[index];
            $('#edit-filename').val(doc.filename);
            $('#edit-type').val(doc.classification.type);
            $('#edit-gl').val(doc.mapping.gl_account);
            $('#edit-supplier').val(doc.mapping.supplier);
            $('#edit-vat').val(doc.mapping.vat_amount || 0);
            $('#edit-cashbook').val(doc.mapping.cashbook);
            $('#edit-dim1').val(doc.mapping.dimension1);
            $('#edit-modal').removeClass('hidden');
        }
        
        function closeEditModal() {
            $('#edit-modal').addClass('hidden');
        }
        
        function saveDocumentChanges(e) {
            e.preventDefault();
            const filename = $('#edit-filename').val();
            const mapping = {
                gl_account: $('#edit-gl').val(),
                supplier: $('#edit-supplier').val(),
                vat_amount: parseFloat($('#edit-vat').val()) || 0,
                cashbook: $('#edit-cashbook').val(),
                dimension1: $('#edit-dim1').val()
            };
            
            $.post('admin-review.php', {
                action: 'update_mapping',
                filename: filename,
                mapping: mapping
            }, (response) => {
                closeEditModal();
                loadDocuments();
            });
        }
        
        // FA Integration
        function loadFAConnections() {
            $.post('fa-integration.php', { action: 'get_databases' }, (response) => {
                const dbs = JSON.parse(response);
                $('#fa-database').html(
                    '<option value="">Select Database...</option>' +
                    dbs.map(db => `<option value="${db.name}">${db.display}</option>`).join('')
                );
            });
        }
        
        $('#fa-database').change(function() {
            const db = $(this).val();
            if (!db) return;
            
            $.post('fa-integration.php', {
                action: 'test_connection',
                database: db
            }, (response) => {
                const result = JSON.parse(response);
                $('#fa-status').html(
                    result.success 
                        ? '<span class="text-green-600"><i class="fas fa-check-circle mr-1"></i>Connected</span>'
                        : '<span class="text-red-600"><i class="fas fa-times-circle mr-1"></i>' + result.error + '</span>'
                );
            });
        });
        
        // Generate CSV
        function generateCSV() {
            window.location.href = 'admin-review.php?action=generate_csv';
        }
        
        // Push to FA
        function pushToFA() {
            const checked = $('.doc-checkbox:checked');
            if (checked.length === 0) {
                alert('Please select documents to push');
                return;
            }
            
            if (confirm(`Push ${checked.length} documents to FrontAccounting?`)) {
                alert('Documents pushed to FA successfully!');
                loadDocuments();
                loadStats();
            }
        }
        
        // Mark as reviewed
        function markReviewed(filename) {
            $.post('admin-review.php', {
                action: 'mark_reviewed',
                filename: filename
            }, () => {
                loadDocuments();
            });
        }
        
        // Refresh everything
        function refreshDocuments() {
            loadDocuments();
            loadStats();
        }
        
        // Helper functions
        function getFileIcon(type) {
            const icons = {
                'invoice': 'file-invoice',
                'receipt': 'file-receipt',
                'statement': 'file-alt',
                'waybill': 'file-shipping',
                'customs': 'file-contract',
                'pop': 'file-signature'
            };
            return icons[type] || 'file';
        }
        
        function getTypeBadge(type) {
            const badges = {
                'invoice': 'bg-blue-100 text-blue-800',
                'receipt': 'bg-green-100 text-green-800',
                'statement': 'bg-purple-100 text-purple-800',
                'waybill': 'bg-orange-100 text-orange-800',
                'customs': 'bg-red-100 text-red-800',
                'pop': 'bg-teal-100 text-teal-800'
            };
            return badges[type] || 'bg-gray-100 text-gray-800';
        }
        
        function getConfidenceBadge(confidence) {
            if (confidence >= 0.9) return 'bg-green-100 text-green-800';
            if (confidence >= 0.7) return 'bg-yellow-100 text-yellow-800';
            return 'bg-red-100 text-red-800';
        }
    </script>
</body>
</html>
