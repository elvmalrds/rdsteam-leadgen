<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RDS Lead Generation Automation - Local Development</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary" id="main-navbar">
        <div class="container">
            <a class="navbar-brand" href="#" id="navbar-brand">
                <i class="fas fa-chart-line me-2"></i>RDS Lead Generation
            </a>
            <span class="badge bg-success ms-auto" id="status-badge">LOCAL DEV</span>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Status Cards -->
        <div class="row mb-4" id="status-cards-row">
            <div class="col-md-3">
                <div class="card border-success" id="vtiger-status-card">
                    <div class="card-body text-center">
                        <i class="fas fa-database text-success fa-2x mb-2"></i>
                        <h6 class="card-title">VTiger API</h6>
                        <span class="badge bg-success" id="vtiger-badge">✅ WORKING</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-info" id="n8n-status-card">
                    <div class="card-body text-center">
                        <i class="fas fa-cogs text-info fa-2x mb-2"></i>
                        <h6 class="card-title">n8n Workflows</h6>
                        <span class="badge bg-info" id="n8n-badge">🚀 READY</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-warning" id="lead411-status-card">
                    <div class="card-body text-center">
                        <i class="fas fa-upload text-warning fa-2x mb-2"></i>
                        <h6 class="card-title">Lead411 Data</h6>
                        <span class="badge bg-warning" id="lead411-badge">📋 PENDING</span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-secondary" id="ghl-status-card">
                    <div class="card-body text-center">
                        <i class="fas fa-bullhorn text-secondary fa-2x mb-2"></i>
                        <h6 class="card-title">GoHighLevel</h6>
                        <span class="badge bg-secondary" id="ghl-badge">⏳ CONFIG</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Tabs -->
        <ul class="nav nav-tabs" id="main-tabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="upload-tab" data-bs-toggle="tab" data-bs-target="#upload-pane" type="button" role="tab">
                    <i class="fas fa-upload me-2"></i>CSV Upload
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="dashboard-tab" data-bs-toggle="tab" data-bs-target="#dashboard-pane" type="button" role="tab">
                    <i class="fas fa-chart-bar me-2"></i>Dashboard
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="workflows-tab" data-bs-toggle="tab" data-bs-target="#workflows-pane" type="button" role="tab">
                    <i class="fas fa-project-diagram me-2"></i>Workflows
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="settings-tab" data-bs-toggle="tab" data-bs-target="#settings-pane" type="button" role="tab">
                    <i class="fas fa-cog me-2"></i>Settings
                </button>
            </li>
        </ul>

        <div class="tab-content mt-3" id="main-tab-content">
            <!-- CSV Upload Tab -->
            <div class="tab-pane fade show active" id="upload-pane" role="tabpanel">
                <div class="card" id="upload-card">
                    <div class="card-header" id="upload-card-header">
                        <h5 class="mb-0"><i class="fas fa-file-csv me-2"></i>Lead411 CSV Upload</h5>
                    </div>
                    <div class="card-body" id="upload-card-body">
                        <form id="csv-upload-form" enctype="multipart/form-data">
                            <div class="mb-3" id="file-input-group">
                                <label for="csv-file" class="form-label">Select Lead411 CSV File</label>
                                <input class="form-control" type="file" id="csv-file" accept=".csv" required>
                                <div class="form-text">Upload your Lead411 Bombora intent data CSV file</div>
                            </div>
                            <div class="mb-3" id="options-group">
                                <div class="form-check" id="test-mode-check">
                                    <input class="form-check-input" type="checkbox" id="test-mode" checked>
                                    <label class="form-check-label" for="test-mode">
                                        Test Mode (don't create actual leads)
                                    </label>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary" id="upload-btn">
                                <i class="fas fa-upload me-2"></i>Upload & Process
                            </button>
                        </form>
                        
                        <div class="mt-4" id="upload-results" style="display: none;">
                            <h6>Processing Results:</h6>
                            <div id="results-content"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dashboard Tab -->
            <div class="tab-pane fade" id="dashboard-pane" role="tabpanel">
                <div class="row" id="dashboard-row">
                    <div class="col-md-6">
                        <div class="card" id="recent-uploads-card">
                            <div class="card-header" id="recent-uploads-header">
                                <h6><i class="fas fa-history me-2"></i>Recent Uploads</h6>
                            </div>
                            <div class="card-body" id="recent-uploads-body">
                                <p class="text-muted">No uploads yet - start by uploading a CSV file</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card" id="system-status-card">
                            <div class="card-header" id="system-status-header">
                                <h6><i class="fas fa-heartbeat me-2"></i>System Status</h6>
                            </div>
                            <div class="card-body" id="system-status-body">
                                <div class="d-flex justify-content-between align-items-center mb-2" id="vtiger-status-row">
                                    <span>VTiger API</span>
                                    <span class="badge bg-success">Online</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-2" id="n8n-status-row">
                                    <span>n8n Server</span>
                                    <span class="badge bg-warning">Check Required</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center" id="local-status-row">
                                    <span>Local Environment</span>
                                    <span class="badge bg-success">Running</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Workflows Tab -->
            <div class="tab-pane fade" id="workflows-pane" role="tabpanel">
                <div class="card" id="workflows-card">
                    <div class="card-header" id="workflows-header">
                        <h6><i class="fas fa-project-diagram me-2"></i>n8n Workflow Management</h6>
                    </div>
                    <div class="card-body" id="workflows-body">
                        <div class="alert alert-info" id="n8n-info-alert">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>n8n Interface:</strong> Access your workflows at 
                            <a href="http://localhost:5678" target="_blank" class="alert-link">http://localhost:5678</a>
                        </div>
                        
                        <div class="row" id="workflow-cards-row">
                            <div class="col-md-4">
                                <div class="card border-primary" id="csv-workflow-card">
                                    <div class="card-body text-center">
                                        <i class="fas fa-file-csv text-primary fa-3x mb-3"></i>
                                        <h6>CSV Processing</h6>
                                        <p class="small text-muted">Parse and validate Lead411 data</p>
                                        <span class="badge bg-secondary">Not Created</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card border-success" id="vtiger-workflow-card">
                                    <div class="card-body text-center">
                                        <i class="fas fa-database text-success fa-3x mb-3"></i>
                                        <h6>VTiger Integration</h6>
                                        <p class="small text-muted">Create leads in CRM</p>
                                        <span class="badge bg-secondary">Not Created</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card border-warning" id="ghl-workflow-card">
                                    <div class="card-body text-center">
                                        <i class="fas fa-bullhorn text-warning fa-3x mb-3"></i>
                                        <h6>GoHighLevel Campaigns</h6>
                                        <p class="small text-muted">Trigger marketing automation</p>
                                        <span class="badge bg-secondary">Not Created</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Settings Tab -->
            <div class="tab-pane fade" id="settings-pane" role="tabpanel">
                <div class="card" id="settings-card">
                    <div class="card-header" id="settings-header">
                        <h6><i class="fas fa-cog me-2"></i>Local Development Settings</h6>
                    </div>
                    <div class="card-body" id="settings-body">
                        <div class="row" id="settings-row">
                            <div class="col-md-6">
                                <h6>Environment Status:</h6>
                                <ul class="list-group" id="env-status-list">
                                    <li class="list-group-item d-flex justify-content-between align-items-center" id="php-status">
                                        PHP/Apache
                                        <span class="badge bg-success rounded-pill">✅</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center" id="n8n-install-status">
                                        n8n Installed
                                        <span class="badge bg-success rounded-pill">v1.110.1</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center" id="vtiger-api-status">
                                        VTiger API
                                        <span class="badge bg-success rounded-pill">Working</span>
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6>Quick Actions:</h6>
                                <div class="d-grid gap-2" id="quick-actions">
                                    <a href="http://localhost:5678" target="_blank" class="btn btn-outline-primary btn-sm" id="open-n8n-btn">
                                        <i class="fas fa-external-link-alt me-2"></i>Open n8n Interface
                                    </a>
                                    <button class="btn btn-outline-success btn-sm" id="test-vtiger-btn">
                                        <i class="fas fa-check me-2"></i>Test VTiger Connection
                                    </button>
                                    <button class="btn btn-outline-info btn-sm" id="view-logs-btn">
                                        <i class="fas fa-file-alt me-2"></i>View Processing Logs
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/app.js"></script>
    <script src="assets/functions.js"></script>
</body>
</html>