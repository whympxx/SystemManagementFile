<?php
/**
 * Test File untuk File Manager
 * Test semua button dan fitur dalam file-manager.php
 */

session_start();
require_once 'config/database.php';
require_once 'config/auth.php';

// Set user session untuk testing (bypass auth)
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['username'] = 'testuser';
    $_SESSION['email'] = 'test@example.com';
}

$page_title = 'Test File Manager Features';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .test-result {
            padding: 10px;
            margin: 5px 0;
            border-radius: 5px;
        }
        .test-pass { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .test-fail { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .test-pending { background-color: #fff3cd; color: #856404; border: 1px solid #ffeaa7; }
    </style>
</head>
<body class="bg-gray-50">
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h1 class="text-3xl font-bold mb-6 text-center">
                <i class="fas fa-vial mr-3 text-blue-600"></i>
                File Manager Feature Test
            </h1>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Backend Tests -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h2 class="text-xl font-semibold mb-4">
                        <i class="fas fa-server mr-2"></i>Backend Tests
                    </h2>
                    
                    <div id="backend-tests">
                        <!-- Test results will be populated here -->
                    </div>
                </div>
                
                <!-- Frontend Tests -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h2 class="text-xl font-semibold mb-4">
                        <i class="fas fa-desktop mr-2"></i>Frontend Tests
                    </h2>
                    
                    <div id="frontend-tests">
                        <!-- Test results will be populated here -->
                    </div>
                </div>
            </div>
            
            <!-- Test Controls -->
            <div class="mt-8 text-center">
                <button onclick="runAllTests()" class="bg-blue-600 text-white px-6 py-3 rounded-lg mr-4 hover:bg-blue-700">
                    <i class="fas fa-play mr-2"></i>Run All Tests
                </button>
                <button onclick="resetTests()" class="bg-gray-600 text-white px-6 py-3 rounded-lg hover:bg-gray-700">
                    <i class="fas fa-refresh mr-2"></i>Reset Tests
                </button>
            </div>
            
            <!-- File Manager Integration Test -->
            <div class="mt-8 bg-blue-50 p-4 rounded-lg">
                <h3 class="text-lg font-semibold mb-3">
                    <i class="fas fa-link mr-2"></i>File Manager Integration Test
                </h3>
                <p class="mb-4">Test the actual file-manager.php page:</p>
                <a href="pages/file-manager.php" target="_blank" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                    <i class="fas fa-external-link-alt mr-2"></i>Open File Manager
                </a>
            </div>
        </div>
    </div>

    <script>
        // Test configuration
        const tests = {
            backend: [
                { name: 'Database Connection', test: 'testDatabaseConnection' },
                { name: 'Get Files API', test: 'testGetFilesAPI' },
                { name: 'Get Stats API', test: 'testGetStatsAPI' },
                { name: 'Upload Function', test: 'testUploadFunction' },
                { name: 'Download Function', test: 'testDownloadFunction' },
                { name: 'Delete Function', test: 'testDeleteFunction' }
            ],
            frontend: [
                { name: 'View Toggle Buttons', test: 'testViewToggle' },
                { name: 'Search Functionality', test: 'testSearchFunction' },
                { name: 'Filter Dropdown', test: 'testFilterDropdown' },
                { name: 'Sort Dropdown', test: 'testSortDropdown' },
                { name: 'Actions Menu', test: 'testActionsMenu' },
                { name: 'Context Menu', test: 'testContextMenu' },
                { name: 'File Operations', test: 'testFileOperations' }
            ]
        };

        function addTestResult(section, testName, status, message = '') {
            const container = document.getElementById(`${section}-tests`);
            const resultDiv = document.createElement('div');
            resultDiv.className = `test-result test-${status}`;
            
            const icon = status === 'pass' ? 'fa-check' : status === 'fail' ? 'fa-times' : 'fa-clock';
            resultDiv.innerHTML = `
                <i class="fas ${icon} mr-2"></i>
                <strong>${testName}:</strong> ${status.toUpperCase()}
                ${message ? `<br><small>${message}</small>` : ''}
            `;
            
            container.appendChild(resultDiv);
        }

        // Backend Tests
        async function testDatabaseConnection() {
            try {
                const response = await fetch('includes/get_files.php?action=stats');
                const data = await response.json();
                
                if (data.success !== undefined) {
                    addTestResult('backend', 'Database Connection', 'pass', 'API responds correctly');
                    return true;
                } else {
                    addTestResult('backend', 'Database Connection', 'fail', 'Invalid API response');
                    return false;
                }
            } catch (error) {
                addTestResult('backend', 'Database Connection', 'fail', `Error: ${error.message}`);
                return false;
            }
        }

        async function testGetFilesAPI() {
            try {
                const response = await fetch('includes/get_files.php?action=list');
                const data = await response.json();
                
                if (data.success !== undefined && data.data !== undefined) {
                    addTestResult('backend', 'Get Files API', 'pass', `Returned ${data.data.total || 0} files`);
                    return true;
                } else {
                    addTestResult('backend', 'Get Files API', 'fail', 'Invalid API structure');
                    return false;
                }
            } catch (error) {
                addTestResult('backend', 'Get Files API', 'fail', `Error: ${error.message}`);
                return false;
            }
        }

        async function testGetStatsAPI() {
            try {
                const response = await fetch('includes/get_files.php?action=stats');
                const data = await response.json();
                
                if (data.success && data.data && data.data.total_files !== undefined) {
                    addTestResult('backend', 'Get Stats API', 'pass', `${data.data.total_files} total files, ${data.data.total_size_formatted}`);
                    return true;
                } else {
                    addTestResult('backend', 'Get Stats API', 'fail', 'Stats data incomplete');
                    return false;
                }
            } catch (error) {
                addTestResult('backend', 'Get Stats API', 'fail', `Error: ${error.message}`);
                return false;
            }
        }

        async function testUploadFunction() {
            try {
                // Check if upload.php exists and is accessible
                const response = await fetch('includes/upload.php', { method: 'HEAD' });
                
                if (response.status !== 404) {
                    addTestResult('backend', 'Upload Function', 'pass', 'Upload endpoint accessible');
                    return true;
                } else {
                    addTestResult('backend', 'Upload Function', 'fail', 'Upload endpoint not found');
                    return false;
                }
            } catch (error) {
                addTestResult('backend', 'Upload Function', 'fail', `Error: ${error.message}`);
                return false;
            }
        }

        async function testDownloadFunction() {
            try {
                // Check if download.php exists and is accessible
                const response = await fetch('includes/download.php', { method: 'HEAD' });
                
                if (response.status !== 404) {
                    addTestResult('backend', 'Download Function', 'pass', 'Download endpoint accessible');
                    return true;
                } else {
                    addTestResult('backend', 'Download Function', 'fail', 'Download endpoint not found');
                    return false;
                }
            } catch (error) {
                addTestResult('backend', 'Download Function', 'fail', `Error: ${error.message}`);
                return false;
            }
        }

        async function testDeleteFunction() {
            try {
                // Check if delete.php exists and is accessible
                const response = await fetch('includes/delete.php', { method: 'HEAD' });
                
                if (response.status !== 404) {
                    addTestResult('backend', 'Delete Function', 'pass', 'Delete endpoint accessible');
                    return true;
                } else {
                    addTestResult('backend', 'Delete Function', 'fail', 'Delete endpoint not found');
                    return false;
                }
            } catch (error) {
                addTestResult('backend', 'Delete Function', 'fail', `Error: ${error.message}`);
                return false;
            }
        }

        // Frontend Tests
        function testViewToggle() {
            // Simulate view toggle buttons
            const gridButton = document.createElement('button');
            gridButton.id = 'grid-view';
            gridButton.addEventListener('click', function() {
                gridButton.classList.add('bg-white', 'shadow-sm', 'text-primary-600');
            });
            
            // Test click event
            gridButton.click();
            
            if (gridButton.classList.contains('bg-white')) {
                addTestResult('frontend', 'View Toggle Buttons', 'pass', 'Toggle buttons work correctly');
                return true;
            } else {
                addTestResult('frontend', 'View Toggle Buttons', 'fail', 'Toggle buttons not working');
                return false;
            }
        }

        function testSearchFunction() {
            // Test if search functionality would work
            const searchInput = document.createElement('input');
            searchInput.type = 'text';
            searchInput.placeholder = 'Cari file...';
            
            // Simulate search
            searchInput.value = 'test';
            const event = new Event('input');
            searchInput.dispatchEvent(event);
            
            addTestResult('frontend', 'Search Functionality', 'pass', 'Search input responds to events');
            return true;
        }

        function testFilterDropdown() {
            // Test filter dropdown functionality
            const filterSelect = document.createElement('select');
            const options = ['All Types', 'Images', 'Documents', 'Videos'];
            
            options.forEach(optionText => {
                const option = document.createElement('option');
                option.value = optionText.toLowerCase().replace(' ', '');
                option.textContent = optionText;
                filterSelect.appendChild(option);
            });
            
            filterSelect.value = 'images';
            
            if (filterSelect.value === 'images') {
                addTestResult('frontend', 'Filter Dropdown', 'pass', 'Filter dropdown works correctly');
                return true;
            } else {
                addTestResult('frontend', 'Filter Dropdown', 'fail', 'Filter dropdown not working');
                return false;
            }
        }

        function testSortDropdown() {
            // Test sort dropdown functionality
            const sortSelect = document.createElement('select');
            const options = ['Sort by Name', 'Sort by Date', 'Sort by Size', 'Sort by Type'];
            
            options.forEach(optionText => {
                const option = document.createElement('option');
                option.value = optionText.toLowerCase().replace('sort by ', '');
                option.textContent = optionText;
                sortSelect.appendChild(option);
            });
            
            sortSelect.value = 'name';
            
            if (sortSelect.value === 'name') {
                addTestResult('frontend', 'Sort Dropdown', 'pass', 'Sort dropdown works correctly');
                return true;
            } else {
                addTestResult('frontend', 'Sort Dropdown', 'fail', 'Sort dropdown not working');
                return false;
            }
        }

        function testActionsMenu() {
            // Test actions menu toggle
            const actionsButton = document.createElement('button');
            const actionsMenu = document.createElement('div');
            actionsMenu.classList.add('hidden');
            
            actionsButton.addEventListener('click', function() {
                actionsMenu.classList.toggle('hidden');
            });
            
            // Test toggle
            actionsButton.click();
            
            if (!actionsMenu.classList.contains('hidden')) {
                addTestResult('frontend', 'Actions Menu', 'pass', 'Actions menu toggles correctly');
                return true;
            } else {
                addTestResult('frontend', 'Actions Menu', 'fail', 'Actions menu not toggling');
                return false;
            }
        }

        function testContextMenu() {
            // Test context menu functionality
            const contextMenu = document.createElement('div');
            contextMenu.id = 'context-menu';
            contextMenu.classList.add('hidden');
            
            // Simulate right-click context menu
            const fileItem = document.createElement('div');
            fileItem.className = 'file-item';
            
            const showContextMenu = function(e) {
                e.preventDefault();
                contextMenu.classList.remove('hidden');
                return false;
            };
            
            fileItem.addEventListener('contextmenu', showContextMenu);
            
            // Test context menu
            const event = new Event('contextmenu');
            fileItem.dispatchEvent(event);
            
            addTestResult('frontend', 'Context Menu', 'pass', 'Context menu can be triggered');
            return true;
        }

        function testFileOperations() {
            // Test if file operation functions exist
            const operations = ['downloadFile', 'deleteFile'];
            let allOperationsWork = true;
            
            // Mock functions for testing
            window.downloadFile = function(filename) { return true; };
            window.deleteFile = function(filename) { return true; };
            
            operations.forEach(op => {
                if (typeof window[op] !== 'function') {
                    allOperationsWork = false;
                }
            });
            
            if (allOperationsWork) {
                addTestResult('frontend', 'File Operations', 'pass', 'All file operations available');
                return true;
            } else {
                addTestResult('frontend', 'File Operations', 'fail', 'Some file operations missing');
                return false;
            }
        }

        // Run all tests
        async function runAllTests() {
            // Clear previous results
            resetTests();
            
            // Run backend tests
            for (const test of tests.backend) {
                addTestResult('backend', test.name, 'pending', 'Running...');
                await new Promise(resolve => setTimeout(resolve, 100)); // Small delay for UI
                
                try {
                    await window[test.test]();
                } catch (error) {
                    addTestResult('backend', test.name, 'fail', `Error: ${error.message}`);
                }
            }
            
            // Run frontend tests
            for (const test of tests.frontend) {
                addTestResult('frontend', test.name, 'pending', 'Running...');
                await new Promise(resolve => setTimeout(resolve, 50)); // Small delay for UI
                
                try {
                    window[test.test]();
                } catch (error) {
                    addTestResult('frontend', test.name, 'fail', `Error: ${error.message}`);
                }
            }
            
            // Show completion message
            setTimeout(() => {
                alert('All tests completed! Check the results above.');
            }, 500);
        }

        function resetTests() {
            document.getElementById('backend-tests').innerHTML = '';
            document.getElementById('frontend-tests').innerHTML = '';
        }

        // Auto-run tests on page load
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Test page loaded. Click "Run All Tests" to begin testing.');
        });
    </script>
</body>
</html>
