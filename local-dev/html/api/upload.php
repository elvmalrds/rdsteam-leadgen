<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Configuration
define('UPLOAD_DIR', '../uploads/');
define('PROCESSED_DIR', '../processed/');
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB
define('ALLOWED_EXTENSIONS', ['csv']);

// Create directories if they don't exist
if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
if (!is_dir(PROCESSED_DIR)) mkdir(PROCESSED_DIR, 0755, true);

$response = ['success' => false];

try {
    // Check if file was uploaded
    if (!isset($_FILES['csv_file'])) {
        throw new Exception('No file uploaded');
    }
    
    $file = $_FILES['csv_file'];
    
    // Validate upload
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Upload error: ' . $file['error']);
    }
    
    if ($file['size'] > MAX_FILE_SIZE) {
        throw new Exception('File too large. Maximum size: 10MB');
    }
    
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($file_extension, ALLOWED_EXTENSIONS)) {
        throw new Exception('Invalid file type. Only CSV files allowed');
    }
    
    // Generate unique filename
    $timestamp = date('YmdHis');
    $unique_name = "leads_{$timestamp}_{$file['name']}";
    $upload_path = UPLOAD_DIR . $unique_name;
    
    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
        throw new Exception('Failed to save uploaded file');
    }
    
    // Parse and validate CSV
    $csv_data = parseAndValidateCSV($upload_path);
    
    // Get processing options
    $test_mode = isset($_POST['test_mode']) && $_POST['test_mode'] === 'true';
    $auto_assign = isset($_POST['auto_assign']) && $_POST['auto_assign'] === 'true';
    
    $response = [
        'success' => true,
        'message' => 'File uploaded and validated successfully',
        'data' => [
            'filename' => $unique_name,
            'upload_path' => $upload_path,
            'records_found' => $csv_data['total_records'],
            'valid_records' => $csv_data['valid_records'],
            'high_intent_leads' => $csv_data['high_intent_count'],
            'columns_mapped' => $csv_data['mapped_columns'],
            'test_mode' => $test_mode,
            'auto_assign' => $auto_assign,
            'processing_id' => $timestamp
        ]
    ];
    
    // Save processing metadata
    $metadata = [
        'upload_time' => date('Y-m-d H:i:s'),
        'original_filename' => $file['name'],
        'file_path' => $upload_path,
        'file_size' => $file['size'],
        'processing_options' => [
            'test_mode' => $test_mode,
            'auto_assign' => $auto_assign
        ],
        'csv_analysis' => $csv_data
    ];
    
    file_put_contents(
        PROCESSED_DIR . "metadata_{$timestamp}.json", 
        json_encode($metadata, JSON_PRETTY_PRINT)
    );
    
} catch (Exception $e) {
    http_response_code(400);
    $response = [
        'success' => false,
        'error' => $e->getMessage()
    ];
}

echo json_encode($response);

function parseAndValidateCSV($file_path) {
    $csv_data = [
        'total_records' => 0,
        'valid_records' => 0,
        'high_intent_count' => 0,
        'mapped_columns' => [],
        'sample_records' => [],
        'errors' => []
    ];
    
    if (($handle = fopen($file_path, 'r')) === FALSE) {
        throw new Exception('Cannot read CSV file');
    }
    
    // Read header row
    $headers = fgetcsv($handle);
    if ($headers === FALSE) {
        fclose($handle);
        throw new Exception('Invalid CSV format - cannot read headers');
    }
    
    // Map columns based on common Lead411 field names
    $column_mapping = [
        'company' => findColumn($headers, ['company', 'company_name', 'organization', 'account']),
        'website' => findColumn($headers, ['website', 'url', 'domain']),
        'industry' => findColumn($headers, ['industry', 'sector', 'vertical']),
        'intent_score' => findColumn($headers, ['intent_score', 'bombora_score', 'score']),
        'intent_topics' => findColumn($headers, ['intent_topics', 'topics', 'keywords']),
        'contact_name' => findColumn($headers, ['contact_name', 'name', 'first_name', 'last_name']),
        'email' => findColumn($headers, ['email', 'contact_email', 'primary_email']),
        'phone' => findColumn($headers, ['phone', 'telephone', 'contact_phone']),
        'employee_count' => findColumn($headers, ['employee_count', 'employees', 'company_size'])
    ];
    
    $csv_data['mapped_columns'] = array_filter($column_mapping);
    
    // Validate required columns exist
    $required_columns = ['company', 'intent_score'];
    foreach ($required_columns as $req_col) {
        if (!isset($csv_data['mapped_columns'][$req_col])) {
            throw new Exception("Required column not found: {$req_col}");
        }
    }
    
    // Read and validate data rows
    $row_num = 1;
    while (($row = fgetcsv($handle)) !== FALSE && $row_num <= 1000) { // Limit processing for demo
        $csv_data['total_records']++;
        
        // Extract mapped data
        $record = [];
        foreach ($csv_data['mapped_columns'] as $field => $col_index) {
            $record[$field] = isset($row[$col_index]) ? trim($row[$col_index]) : '';
        }
        
        // Validate record
        $is_valid = true;
        $errors = [];
        
        // Company name required
        if (empty($record['company'])) {
            $is_valid = false;
            $errors[] = "Missing company name";
        }
        
        // Intent score validation
        $intent_score = isset($record['intent_score']) ? intval($record['intent_score']) : 0;
        if ($intent_score < 70) {
            $is_valid = false;
            $errors[] = "Intent score too low: {$intent_score} (minimum: 70)";
        } elseif ($intent_score >= 80) {
            $csv_data['high_intent_count']++;
        }
        
        if ($is_valid) {
            $csv_data['valid_records']++;
        }
        
        // Store sample records for preview
        if (count($csv_data['sample_records']) < 3) {
            $csv_data['sample_records'][] = [
                'row_number' => $row_num,
                'data' => $record,
                'is_valid' => $is_valid,
                'errors' => $errors,
                'intent_score' => $intent_score
            ];
        }
        
        $row_num++;
    }
    
    fclose($handle);
    
    if ($csv_data['valid_records'] === 0) {
        throw new Exception('No valid records found in CSV file');
    }
    
    return $csv_data;
}

function findColumn($headers, $possible_names) {
    foreach ($headers as $index => $header) {
        $header_lower = strtolower(trim($header));
        foreach ($possible_names as $name) {
            if (strpos($header_lower, strtolower($name)) !== false) {
                return $index;
            }
        }
    }
    return null;
}
?>