// Additional JavaScript functions for RDS Lead Generation Dashboard

function displayUploadResults(data) {
    const resultsDiv = document.getElementById('upload-results');
    const resultsContent = document.getElementById('results-content');
    
    if (!resultsDiv || !resultsContent) return;
    
    // Store current file info for processing
    window.currentFileData = data;
    
    const html = `
        <div class="card">
            <div class="card-header bg-success text-white">
                <h6 class="mb-0"><i class="fas fa-check-circle me-2"></i>CSV Analysis Complete</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>File Information</h6>
                        <ul class="list-unstyled">
                            <li><strong>Filename:</strong> ${data.filename}</li>
                            <li><strong>Total Records:</strong> ${data.records_found}</li>
                            <li><strong>Valid Records:</strong> ${data.valid_records}</li>
                            <li><strong>High Intent Leads:</strong> ${data.high_intent_leads} (score ≥80)</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6>Processing Options</h6>
                        <ul class="list-unstyled">
                            <li><strong>Test Mode:</strong> ${data.test_mode ? 'Enabled' : 'Disabled'}</li>
                            <li><strong>Auto Assign:</strong> ${data.auto_assign ? 'Enabled' : 'Disabled'}</li>
                            <li><strong>Processing ID:</strong> ${data.processing_id}</li>
                        </ul>
                    </div>
                </div>
                
                <div class="mt-3">
                    <h6>Column Mapping</h6>
                    <div class="row">
                        ${Object.entries(data.columns_mapped).map(([field, column]) => 
                            `<div class="col-md-4"><span class="badge bg-primary">${field}</span>: ${column}</div>`
                        ).join('')}
                    </div>
                </div>
                
                <div class="mt-4 d-flex gap-2">
                    <button type="button" class="btn btn-primary" onclick="processToVTiger()">
                        <i class="fas fa-database me-2"></i>Process to VTiger
                    </button>
                    <button type="button" class="btn btn-outline-secondary" onclick="showFilePreview()">
                        <i class="fas fa-eye me-2"></i>Preview Data
                    </button>
                </div>
            </div>
        </div>
    `;
    
    resultsContent.innerHTML = html;
    resultsDiv.style.display = 'block';
}

function processToVTiger() {
    if (!window.currentFileData) {
        showAlert('danger', 'No file data available for processing');
        return;
    }
    
    showProgress('Processing leads through automation pipeline...');
    
    // Check if n8n workflows should be used
    const useWorkflows = document.getElementById('use-workflows')?.checked || false;
    
    if (useWorkflows) {
        // Use n8n workflow automation
        triggerN8nWorkflow();
    } else {
        // Direct VTiger processing
        processDirect();
    }
}

function triggerN8nWorkflow() {
    const webhookUrl = 'http://localhost:5678/webhook/csv-upload';
    
    const workflowData = {
        file_path: window.currentFileData.upload_path,
        processing_id: window.currentFileData.processing_id,
        test_mode: window.currentFileData.test_mode,
        auto_assign: window.currentFileData.auto_assign
    };
    
    fetch(webhookUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(workflowData)
    })
    .then(response => response.json())
    .then(data => {
        hideProgress();
        
        if (data.success) {
            displayWorkflowResults(data);
            showAlert('success', 'Automation pipeline completed successfully');
        } else {
            showAlert('warning', 'n8n workflow not available, falling back to direct processing');
            processDirect();
        }
    })
    .catch(error => {
        hideProgress();
        console.log('n8n workflow not available, using direct processing');
        showAlert('info', 'Using direct processing (n8n not available)');
        processDirect();
    });
}

function processDirect() {
    showProgress('Processing leads to VTiger CRM...');
    
    const requestData = {
        action: 'process_csv_file',
        file_path: window.currentFileData.upload_path,
        options: {
            test_mode: window.currentFileData.test_mode,
            auto_assign: window.currentFileData.auto_assign
        }
    };
    
    fetch('./api/vtiger.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(requestData)
    })
    .then(response => response.json())
    .then(data => {
        hideProgress();
        
        if (data.success) {
            displayVTigerResults(data.data);
            showAlert('success', 'VTiger processing completed successfully');
        } else {
            showAlert('danger', 'VTiger processing failed: ' + data.error);
        }
    })
    .catch(error => {
        hideProgress();
        console.error('VTiger processing error:', error);
        showAlert('danger', 'VTiger processing failed: ' + error.message);
    });
}

function displayVTigerResults(results) {
    const resultsDiv = document.getElementById('vtiger-results');
    const resultsContent = document.getElementById('vtiger-results-content');
    
    if (!resultsDiv || !resultsContent) {
        // Create results area if it doesn't exist
        const uploadResults = document.getElementById('upload-results');
        if (uploadResults) {
            const newDiv = document.createElement('div');
            newDiv.id = 'vtiger-results';
            newDiv.className = 'mt-3';
            newDiv.innerHTML = '<div id="vtiger-results-content"></div>';
            uploadResults.appendChild(newDiv);
        }
    }
    
    const html = `
        <div class="card">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0"><i class="fas fa-database me-2"></i>VTiger Processing Results</h6>
            </div>
            <div class="card-body">
                <div class="row text-center mb-3">
                    <div class="col-md-3">
                        <div class="h4 text-primary">${results.processed}</div>
                        <small class="text-muted">Processed</small>
                    </div>
                    <div class="col-md-3">
                        <div class="h4 text-success">${results.created}</div>
                        <small class="text-muted">Created</small>
                    </div>
                    <div class="col-md-3">
                        <div class="h4 text-warning">${results.skipped}</div>
                        <small class="text-muted">Skipped</small>
                    </div>
                    <div class="col-md-3">
                        <div class="h4 text-danger">${results.errors}</div>
                        <small class="text-muted">Errors</small>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Row</th>
                                <th>Status</th>
                                <th>Company</th>
                                <th>Intent Score</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${results.details.slice(0, 10).map(detail => `
                                <tr>
                                    <td>${detail.row}</td>
                                    <td>
                                        <span class="badge bg-${getStatusBadgeColor(detail.status)}">
                                            ${detail.status}
                                        </span>
                                    </td>
                                    <td>${detail.company || '-'}</td>
                                    <td>${detail.intent_score || '-'}</td>
                                    <td>${detail.lead_id || detail.reason || '-'}</td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                    ${results.details.length > 10 ? 
                        `<p class="text-muted">Showing first 10 of ${results.details.length} records</p>` : 
                        ''}
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('vtiger-results-content').innerHTML = html;
    document.getElementById('vtiger-results').style.display = 'block';
}

function getStatusBadgeColor(status) {
    switch (status) {
        case 'created': return 'success';
        case 'error': return 'danger';
        case 'skipped': return 'warning';
        case 'test_mode': return 'info';
        default: return 'secondary';
    }
}

function testVTigerConnection() {
    showProgress('Testing VTiger connection...');
    
    const requestData = {
        action: 'test_connection'
    };
    
    fetch('./api/vtiger.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(requestData)
    })
    .then(response => response.json())
    .then(data => {
        hideProgress();
        
        if (data.success) {
            updateVTigerStatus('success', 'CONNECTED');
            showAlert('success', 'VTiger connection successful!');
            console.log('VTiger connection details:', data.data);
        } else {
            updateVTigerStatus('danger', 'FAILED');
            showAlert('danger', 'VTiger connection failed: ' + data.error);
        }
    })
    .catch(error => {
        hideProgress();
        console.error('VTiger test error:', error);
        updateVTigerStatus('danger', 'ERROR');
        showAlert('danger', 'VTiger test failed: ' + error.message);
    });
}

function updateVTigerStatus(status, text) {
    const badge = document.getElementById('vtiger-badge');
    const card = document.getElementById('vtiger-status-card');
    
    if (badge) {
        badge.className = `badge bg-${status}`;
        badge.textContent = text;
    }
    
    if (card) {
        card.className = `card border-${status}`;
    }
}

function checkN8nStatus() {
    // Check if n8n is running on localhost:5678
    fetch('http://localhost:5678/healthz')
    .then(response => {
        if (response.ok) {
            updateN8nStatus('success', 'RUNNING');
        } else {
            updateN8nStatus('warning', 'NOT RUNNING');
        }
    })
    .catch(() => {
        updateN8nStatus('warning', 'NOT RUNNING');
    });
}

function updateN8nStatus(status, text) {
    const badge = document.getElementById('n8n-badge');
    const card = document.getElementById('n8n-status-card');
    
    if (badge) {
        badge.className = `badge bg-${status}`;
        badge.textContent = text;
    }
    
    if (card) {
        card.className = `card border-${status}`;
    }
}

function updateEnvironmentStatus() {
    // Update environment badges and indicators
    const envBadge = document.getElementById('status-badge');
    if (envBadge) {
        envBadge.textContent = 'LOCAL DEV';
        envBadge.className = 'badge bg-success ms-auto';
    }
}

function showAlert(type, message) {
    // Remove existing alerts
    const existingAlerts = document.querySelectorAll('.alert-dismissible');
    existingAlerts.forEach(alert => alert.remove());
    
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    const container = document.querySelector('.container');
    if (container) {
        container.insertBefore(alertDiv, container.firstChild);
    }
    
    // Auto-dismiss success alerts
    if (type === 'success') {
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    }
}

function showProgress(message) {
    let progressDiv = document.getElementById('progress-indicator');
    
    if (!progressDiv) {
        progressDiv = document.createElement('div');
        progressDiv.id = 'progress-indicator';
        progressDiv.className = 'alert alert-info d-flex align-items-center';
        progressDiv.innerHTML = `
            <div class="spinner-border spinner-border-sm me-2" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <span id="progress-message">${message}</span>
        `;
        
        const container = document.querySelector('.container');
        if (container) {
            container.insertBefore(progressDiv, container.firstChild);
        }
    } else {
        document.getElementById('progress-message').textContent = message;
        progressDiv.style.display = 'flex';
    }
}

function hideProgress() {
    const progressDiv = document.getElementById('progress-indicator');
    if (progressDiv) {
        progressDiv.style.display = 'none';
    }
}

function showFilePreview() {
    if (!window.currentFileData) {
        showAlert('warning', 'No file data available for preview');
        return;
    }
    
    // This would show a modal or section with sample data
    showAlert('info', 'File preview feature coming soon. Check the console for file data.');
    console.log('Current file data:', window.currentFileData);
}

function displayWorkflowResults(data) {
    const resultsDiv = document.getElementById('vtiger-results');
    const resultsContent = document.getElementById('vtiger-results-content');
    
    if (!resultsDiv || !resultsContent) {
        // Create results area if it doesn't exist
        const uploadResults = document.getElementById('upload-results');
        if (uploadResults) {
            const newDiv = document.createElement('div');
            newDiv.id = 'vtiger-results';
            newDiv.className = 'mt-3';
            newDiv.innerHTML = '<div id="vtiger-results-content"></div>';
            uploadResults.appendChild(newDiv);
        }
    }
    
    const html = `
        <div class="card">
            <div class="card-header bg-success text-white">
                <h6 class="mb-0"><i class="fas fa-cogs me-2"></i>Automation Pipeline Results</h6>
            </div>
            <div class="card-body">
                <div class="row text-center mb-3">
                    <div class="col-md-4">
                        <div class="h4 text-success">${data.processing_summary?.created || 0}</div>
                        <small class="text-muted">Leads Created</small>
                    </div>
                    <div class="col-md-4">
                        <div class="h4 text-warning">${data.high_intent_leads || 0}</div>
                        <small class="text-muted">Hot Leads (≥90)</small>
                    </div>
                    <div class="col-md-4">
                        <div class="h4 text-info">${data.processing_summary?.processed || 0}</div>
                        <small class="text-muted">Total Processed</small>
                    </div>
                </div>
                
                <div class="alert alert-info">
                    <h6><i class="fas fa-rocket me-2"></i>Automation Pipeline Active</h6>
                    <ul class="mb-0">
                        <li>✅ VTiger leads created successfully</li>
                        <li>✅ GoHighLevel contacts synced</li>
                        <li>✅ Marketing campaigns assigned</li>
                        ${data.high_intent_leads > 0 ? '<li>🔥 Hot leads notification sent to Slack</li>' : ''}
                    </ul>
                </div>
                
                <div class="mt-3">
                    <small class="text-muted">
                        <i class="fas fa-clock me-1"></i>Completed: ${new Date(data.timestamp).toLocaleString()}
                    </small>
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('vtiger-results-content').innerHTML = html;
    document.getElementById('vtiger-results').style.display = 'block';
}

// Auto-refresh status indicators every 30 seconds
setInterval(() => {
    checkN8nStatus();
}, 30000);