<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'VTigerClient.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// VTiger configuration
define('VTIGER_URL', 'https://rdsteamglobalpresenceorg.od2.vtiger.com');
define('VTIGER_USERNAME', 'elvis@rdsteam.com');
define('VTIGER_ACCESS_KEY', 'dN9NniLcjQq5tg72');
define('DEFAULT_USER_ID', '19x77'); // Elvis's user ID

$input = json_decode(file_get_contents('php://input'), true);
$response = ['success' => false];

try {
    $action = $input['action'] ?? '';
    
    switch ($action) {
        case 'test_connection':
            $response = testConnection();
            break;
            
        case 'create_lead':
            $response = createLead($input['lead_data']);
            break;
            
        case 'process_csv_file':
            $response = processCsvFile($input['file_path'], $input['options'] ?? []);
            break;
            
        case 'check_duplicate':
            $response = checkDuplicate($input['company'], $input['email'] ?? '');
            break;
            
        default:
            throw new Exception('Invalid action specified');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    $response = [
        'success' => false,
        'error' => $e->getMessage()
    ];
}

echo json_encode($response);

function testConnection() {
    $client = new VTigerClient(VTIGER_URL, VTIGER_USERNAME, VTIGER_ACCESS_KEY);
    
    // Test authentication
    $challenge = $client->getChallenge();
    $session = $client->login($challenge);
    
    // Get user info
    $user_info = $client->query("SELECT * FROM Users WHERE user_name='" . VTIGER_USERNAME . "'");
    
    return [
        'success' => true,
        'message' => 'VTiger connection successful',
        'data' => [
            'session_id' => substr($session, 0, 10) . '...',
            'user_info' => $user_info['result'][0] ?? null,
            'connection_time' => date('Y-m-d H:i:s')
        ]
    ];
}

function createLead($lead_data) {
    $client = new VTigerClient(VTIGER_URL, VTIGER_USERNAME, VTIGER_ACCESS_KEY);
    
    // Prepare lead data for VTiger
    $vtiger_lead = [
        'lastname' => $lead_data['company'] ?? 'Unknown Company',
        'company' => $lead_data['company'] ?? '',
        'firstname' => $lead_data['contact_name'] ?? '',
        'email' => $lead_data['email'] ?? '',
        'phone' => $lead_data['phone'] ?? '',
        'website' => $lead_data['website'] ?? '',
        'industry' => $lead_data['industry'] ?? '',
        'assigned_user_id' => DEFAULT_USER_ID,
        'leadstatus' => 'Not Contacted',
        'leadsource' => 'Lead411 Intent Data',
        'rating' => determineLeadRating($lead_data),
        'description' => buildLeadDescription($lead_data)
    ];
    
    // Add custom fields if available
    if (isset($lead_data['intent_score'])) {
        $vtiger_lead['cf_bombora_score'] = $lead_data['intent_score'];
    }
    
    if (isset($lead_data['intent_topics'])) {
        $vtiger_lead['cf_intent_topics'] = $lead_data['intent_topics'];
    }
    
    if (isset($lead_data['employee_count'])) {
        $vtiger_lead['cf_company_size'] = $lead_data['employee_count'];
    }
    
    $vtiger_lead['cf_import_date'] = date('Y-m-d');
    $vtiger_lead['cf_processing_id'] = uniqid('LEAD411_');
    
    try {
        $result = $client->create('Leads', $vtiger_lead);
        
        return [
            'success' => true,
            'message' => 'Lead created successfully in VTiger',
            'data' => [
                'lead_id' => $result['result']['id'],
                'lead_number' => $result['result']['leadNo'] ?? null,
                'vtiger_data' => $vtiger_lead,
                'created_time' => date('Y-m-d H:i:s')
            ]
        ];
        
    } catch (Exception $e) {
        throw new Exception('VTiger lead creation failed: ' . $e->getMessage());
    }
}

function processCsvFile($file_path, $options = []) {
    if (!file_exists($file_path)) {
        throw new Exception('CSV file not found');
    }
    
    $test_mode = $options['test_mode'] ?? false;
    $max_records = $test_mode ? 5 : 100; // Limit for testing
    
    $results = [
        'processed' => 0,
        'created' => 0,
        'skipped' => 0,
        'errors' => 0,
        'details' => []
    ];
    
    // Read CSV file
    $csv_handle = fopen($file_path, 'r');
    $headers = fgetcsv($csv_handle);
    
    // Map columns
    $column_mapping = [
        'company' => findColumnIndex($headers, ['company', 'company_name']),
        'website' => findColumnIndex($headers, ['website', 'url']),
        'industry' => findColumnIndex($headers, ['industry', 'sector']),
        'intent_score' => findColumnIndex($headers, ['intent_score', 'bombora_score']),
        'intent_topics' => findColumnIndex($headers, ['intent_topics', 'topics']),
        'contact_name' => findColumnIndex($headers, ['contact_name', 'name']),
        'email' => findColumnIndex($headers, ['email', 'contact_email']),
        'phone' => findColumnIndex($headers, ['phone', 'telephone']),
        'employee_count' => findColumnIndex($headers, ['employee_count', 'employees'])
    ];
    
    $row_num = 0;
    while (($row = fgetcsv($csv_handle)) !== FALSE && $row_num < $max_records) {
        $results['processed']++;
        
        try {
            // Extract lead data
            $lead_data = [];
            foreach ($column_mapping as $field => $col_index) {
                if ($col_index !== null && isset($row[$col_index])) {
                    $lead_data[$field] = trim($row[$col_index]);
                }
            }
            
            // Validate minimum requirements
            if (empty($lead_data['company'])) {
                $results['skipped']++;
                $results['details'][] = [
                    'row' => $row_num + 2, // +2 for header and 0-index
                    'status' => 'skipped',
                    'reason' => 'Missing company name'
                ];
                continue;
            }
            
            $intent_score = intval($lead_data['intent_score'] ?? 0);
            if ($intent_score < 70) {
                $results['skipped']++;
                $results['details'][] = [
                    'row' => $row_num + 2,
                    'status' => 'skipped',
                    'reason' => "Intent score too low: {$intent_score}"
                ];
                continue;
            }
            
            // Check for duplicates (basic check)
            if (checkDuplicate($lead_data['company'], $lead_data['email'] ?? '')['is_duplicate']) {
                $results['skipped']++;
                $results['details'][] = [
                    'row' => $row_num + 2,
                    'status' => 'skipped',
                    'reason' => 'Potential duplicate found'
                ];
                continue;
            }
            
            // Create lead if not in test mode
            if (!$test_mode) {
                $create_result = createLead($lead_data);
                if ($create_result['success']) {
                    $results['created']++;
                    $results['details'][] = [
                        'row' => $row_num + 2,
                        'status' => 'created',
                        'lead_id' => $create_result['data']['lead_id'],
                        'company' => $lead_data['company'],
                        'intent_score' => $intent_score
                    ];
                } else {
                    $results['errors']++;
                    $results['details'][] = [
                        'row' => $row_num + 2,
                        'status' => 'error',
                        'reason' => 'VTiger creation failed'
                    ];
                }
            } else {
                $results['details'][] = [
                    'row' => $row_num + 2,
                    'status' => 'test_mode',
                    'company' => $lead_data['company'],
                    'intent_score' => $intent_score,
                    'would_create' => true
                ];
            }
            
        } catch (Exception $e) {
            $results['errors']++;
            $results['details'][] = [
                'row' => $row_num + 2,
                'status' => 'error',
                'reason' => $e->getMessage()
            ];
        }
        
        $row_num++;
    }
    
    fclose($csv_handle);
    
    return [
        'success' => true,
        'message' => 'CSV processing completed',
        'data' => $results
    ];
}

function checkDuplicate($company, $email = '') {
    $client = new VTigerClient(VTIGER_URL, VTIGER_USERNAME, VTIGER_ACCESS_KEY);
    
    $is_duplicate = false;
    $matches = [];
    
    try {
        // Check for company name match
        $company_query = "SELECT id, company, email FROM Leads WHERE company LIKE '%" . 
                        addslashes($company) . "%' LIMIT 5";
        $company_results = $client->query($company_query);
        
        if (!empty($company_results['result'])) {
            $is_duplicate = true;
            $matches = array_merge($matches, $company_results['result']);
        }
        
        // Check for email match if provided
        if (!empty($email)) {
            $email_query = "SELECT id, company, email FROM Leads WHERE email = '" . 
                          addslashes($email) . "' LIMIT 5";
            $email_results = $client->query($email_query);
            
            if (!empty($email_results['result'])) {
                $is_duplicate = true;
                $matches = array_merge($matches, $email_results['result']);
            }
        }
        
    } catch (Exception $e) {
        // If query fails, assume no duplicates to avoid blocking
        $is_duplicate = false;
    }
    
    return [
        'success' => true,
        'is_duplicate' => $is_duplicate,
        'matches' => array_unique($matches, SORT_REGULAR)
    ];
}

function determineLeadRating($lead_data) {
    $intent_score = intval($lead_data['intent_score'] ?? 0);
    
    if ($intent_score >= 90) return 'Hot';
    if ($intent_score >= 80) return 'Warm';
    if ($intent_score >= 70) return 'Cold';
    
    return 'Cold';
}

function buildLeadDescription($lead_data) {
    $description = "Lead generated from Lead411 Bombora Intent Data\n\n";
    
    if (isset($lead_data['intent_score'])) {
        $description .= "Intent Score: " . $lead_data['intent_score'] . "\n";
    }
    
    if (isset($lead_data['intent_topics'])) {
        $description .= "Intent Topics: " . $lead_data['intent_topics'] . "\n";
    }
    
    if (isset($lead_data['employee_count'])) {
        $description .= "Company Size: " . $lead_data['employee_count'] . " employees\n";
    }
    
    $description .= "\nProcessed: " . date('Y-m-d H:i:s') . "\n";
    $description .= "Source: RDS Lead Generation Automation";
    
    return $description;
}

function findColumnIndex($headers, $possible_names) {
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