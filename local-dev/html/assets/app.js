// RDS Lead Generation Dashboard - Local Development
document.addEventListener('DOMContentLoaded', function() {
    // Initialize dashboard
    initDashboard();
    
    // Setup event listeners
    setupEventListeners();
});

function initDashboard() {
    console.log('RDS Lead Generation Dashboard - Local Development Mode');
    
    // Check n8n status
    checkN8nStatus();
    
    // Update environment indicators
    updateEnvironmentStatus();
}

function setupEventListeners() {
    // CSV upload form
    const uploadForm = document.getElementById('csv-upload-form');
    if (uploadForm) {
        uploadForm.addEventListener('submit', handleCSVUpload);
    }
    
    // Test VTiger button
    const testVTigerBtn = document.getElementById('test-vtiger-btn');
    if (testVTigerBtn) {
        testVTigerBtn.addEventListener('click', testVTigerConnection);
    }
    
    // Open n8n button
    const openN8nBtn = document.getElementById('open-n8n-btn');
    if (openN8nBtn) {
        openN8nBtn.addEventListener('click', function(e) {
            e.preventDefault();
            window.open('http://localhost:5678', '_blank');
        });
    }
}

function handleCSVUpload(e) {
    e.preventDefault();
    
    const fileInput = document.getElementById('csv-file');
    const testMode = document.getElementById('test-mode').checked;
    const autoAssign = document.getElementById('auto-assign').checked;
    const resultsDiv = document.getElementById('upload-results');
    const resultsContent = document.getElementById('results-content');
    
    if (!fileInput.files || fileInput.files.length === 0) {
        showAlert('danger', 'Please select a CSV file to upload');
        return;
    }
    
    const file = fileInput.files[0];
    
    // Validate file type
    if (!file.name.toLowerCase().endsWith('.csv')) {
        showAlert('danger', 'Please select a valid CSV file');
        return;
    }
    
    // Show progress
    showProgress('Uploading and analyzing CSV file...');
    
    // Create form data
    const formData = new FormData();
    formData.append('csv_file', file);
    formData.append('test_mode', testMode);
    formData.append('auto_assign', autoAssign);
    
    // Upload file
    fetch('./api/upload.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        hideProgress();
        
        if (data.success) {
            displayUploadResults(data.data);
            showAlert('success', data.message);
        } else {
            showAlert('danger', 'Upload failed: ' + data.error);
        }
    })
    .catch(error => {
        hideProgress();
        console.error('Upload error:', error);
        showAlert('danger', 'Upload failed: ' + error.message);
    });
    
    if (!fileInput.files[0]) {
        alert('Please select a CSV file');
        return;
    }
    
    // Show processing message
    resultsContent.innerHTML = `
        <div class="alert alert-info">
            <i class="fas fa-spinner fa-spin me-2"></i>
            Processing CSV file: ${fileInput.files[0].name}...
        </div>
    `;
    resultsDiv.style.display = 'block';
    
    // Simulate processing (replace with actual API call later)
    setTimeout(() => {
        const mockResults = {
            filename: fileInput.files[0].name,
            records: 5,
            valid: 4,
            invalid: 1,
            testMode: testMode
        };
        
        displayUploadResults(mockResults);
    }, 2000);
}

function displayUploadResults(results) {
    const resultsContent = document.getElementById('results-content');
    
    resultsContent.innerHTML = `
        <div class="alert alert-success">
            <h6><i class="fas fa-check-circle me-2"></i>Processing Complete</h6>
            <ul class="mb-0">
                <li>File: ${results.filename}</li>
                <li>Total Records: ${results.records}</li>
                <li>Valid Records: ${results.valid}</li>
                <li>Invalid Records: ${results.invalid}</li>
                <li>Mode: ${results.testMode ? 'Test Mode (no leads created)' : 'Live Mode'}</li>
            </ul>
        </div>
        ${results.testMode ? '' : `
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            In live mode, this would create ${results.valid} leads in VTiger
        </div>
        `}
    `;
}

function testVTigerConnection() {
    const button = document.getElementById('test-vtiger-btn');
    const originalText = button.innerHTML;
    
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Testing...';
    button.disabled = true;
    
    // Simulate VTiger test (replace with actual API call later)
    setTimeout(() => {
        button.innerHTML = '<i class="fas fa-check me-2"></i>Connection OK!';
        button.classList.remove('btn-outline-success');
        button.classList.add('btn-success');
        
        // Show success message
        showToast('VTiger API connection successful!', 'success');
        
        // Reset button after 3 seconds
        setTimeout(() => {
            button.innerHTML = originalText;
            button.classList.remove('btn-success');
            button.classList.add('btn-outline-success');
            button.disabled = false;
        }, 3000);
    }, 1500);
}

function checkN8nStatus() {
    // Try to check if n8n is running
    fetch('http://localhost:5678/rest/login', { mode: 'no-cors' })
        .then(() => {
            updateN8nStatus('running');
        })
        .catch(() => {
            updateN8nStatus('stopped');
        });
}

function updateN8nStatus(status) {
    const badge = document.getElementById('n8n-badge');
    const statusRow = document.getElementById('n8n-status-row');
    
    if (status === 'running') {
        badge.innerHTML = '🟢 RUNNING';
        badge.className = 'badge bg-success';
        if (statusRow) {
            statusRow.querySelector('.badge').innerHTML = 'Running';
            statusRow.querySelector('.badge').className = 'badge bg-success';
        }
    } else {
        badge.innerHTML = '🔴 STOPPED';
        badge.className = 'badge bg-danger';
        if (statusRow) {
            statusRow.querySelector('.badge').innerHTML = 'Stopped';
            statusRow.querySelector('.badge').className = 'badge bg-danger';
        }
    }
}

function updateEnvironmentStatus() {
    // Update environment indicators
    const phpStatus = document.getElementById('php-status');
    if (phpStatus && window.location.protocol === 'http:') {
        // We're running in browser, so PHP/Apache is working
        phpStatus.querySelector('.badge').innerHTML = '✅ Running';
    }
}

function showToast(message, type = 'info') {
    // Simple toast notification
    const toast = document.createElement('div');
    toast.className = `alert alert-${type} position-fixed`;
    toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    toast.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check' : 'info'}-circle me-2"></i>
        ${message}
    `;
    
    document.body.appendChild(toast);
    
    // Auto remove after 3 seconds
    setTimeout(() => {
        if (toast.parentNode) {
            toast.parentNode.removeChild(toast);
        }
    }, 3000);
}

// Utility functions for later development
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function formatDateTime(date) {
    return new Date(date).toLocaleString();
}