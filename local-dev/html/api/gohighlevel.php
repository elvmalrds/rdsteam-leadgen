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

// GoHighLevel API Configuration
// NOTE: Replace with actual API credentials
define('GHL_API_KEY', 'your_ghl_api_key_here');
define('GHL_API_BASE', 'https://rest.gohighlevel.com/v1');
define('GHL_LOCATION_ID', 'your_location_id_here'); // Required for GoHighLevel

$input = json_decode(file_get_contents('php://input'), true);
$response = ['success' => false];

try {
    $action = $input['action'] ?? '';
    
    switch ($action) {
        case 'test_connection':
            $response = testGHLConnection();
            break;
            
        case 'create_contact':
            $response = createGHLContact($input['contact_data']);
            break;
            
        case 'assign_to_campaign':
            $response = assignToCampaign($input['contact_id'], $input['campaign_id']);
            break;
            
        case 'process_leads_batch':
            $response = processLeadsBatch($input['leads']);
            break;
            
        case 'list_campaigns':
            $response = listCampaigns();
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

function testGHLConnection() {
    try {
        $response = makeGHLRequest('GET', '/locations/' . GHL_LOCATION_ID);
        
        return [
            'success' => true,
            'message' => 'GoHighLevel connection successful',
            'data' => [
                'location_id' => GHL_LOCATION_ID,
                'location_name' => $response['location']['name'] ?? 'Unknown',
                'connection_time' => date('Y-m-d H:i:s')
            ]
        ];
    } catch (Exception $e) {
        throw new Exception('GoHighLevel connection failed: ' . $e->getMessage());
    }
}

function createGHLContact($contactData) {
    // Prepare contact data for GoHighLevel API
    $ghlContact = [
        'firstName' => $contactData['first_name'] ?? '',
        'lastName' => $contactData['last_name'] ?? '',
        'email' => $contactData['email'] ?? '',
        'phone' => $contactData['phone'] ?? '',
        'companyName' => $contactData['company'] ?? '',
        'website' => $contactData['website'] ?? '',
        'source' => 'Lead411 Intent Data',
        'tags' => $contactData['tags'] ?? ['lead411-import'],
        'customFields' => [
            'intent_score' => (string)($contactData['intent_score'] ?? '0'),
            'vtiger_lead_id' => $contactData['vtiger_lead_id'] ?? '',
            'lead_category' => $contactData['category'] ?? 'qualified',
            'import_date' => date('Y-m-d'),
            'intent_topics' => $contactData['intent_topics'] ?? ''
        ]
    ];
    
    try {
        $response = makeGHLRequest('POST', '/contacts', $ghlContact);
        
        return [
            'success' => true,
            'message' => 'Contact created successfully in GoHighLevel',
            'data' => [
                'contact_id' => $response['contact']['id'],
                'contact_data' => $ghlContact,
                'created_time' => date('Y-m-d H:i:s')
            ]
        ];
        
    } catch (Exception $e) {
        throw new Exception('GoHighLevel contact creation failed: ' . $e->getMessage());
    }
}

function assignToCampaign($contactId, $campaignId) {
    $assignmentData = [
        'contactId' => $contactId,
        'status' => 'active'
    ];
    
    try {
        $response = makeGHLRequest('POST', "/campaigns/{$campaignId}/subscribers", $assignmentData);
        
        return [
            'success' => true,
            'message' => 'Contact assigned to campaign successfully',
            'data' => [
                'contact_id' => $contactId,
                'campaign_id' => $campaignId,
                'assignment_time' => date('Y-m-d H:i:s')
            ]
        ];
        
    } catch (Exception $e) {
        throw new Exception('Campaign assignment failed: ' . $e->getMessage());
    }
}

function processLeadsBatch($leads) {
    $results = [
        'processed' => 0,
        'created' => 0,
        'campaign_assigned' => 0,
        'errors' => 0,
        'details' => []
    ];
    
    // Campaign mapping based on intent scores
    $campaignMapping = [
        'hot' => 'hot-leads-immediate-followup',
        'warm' => 'warm-leads-nurture-sequence',
        'qualified' => 'qualified-leads-general-sequence'
    ];
    
    foreach ($leads as $lead) {
        $results['processed']++;
        
        try {
            // Determine category and campaign
            $category = 'qualified';
            if ($lead['intent_score'] >= 90) $category = 'hot';
            elseif ($lead['intent_score'] >= 80) $category = 'warm';
            
            $campaignId = $campaignMapping[$category];
            
            // Prepare contact data
            $contactData = [
                'first_name' => extractFirstName($lead['company']),
                'last_name' => extractLastName($lead['company']),
                'email' => $lead['email'] ?? '',
                'phone' => $lead['phone'] ?? '',
                'company' => $lead['company'],
                'website' => $lead['website'] ?? '',
                'intent_score' => $lead['intent_score'],
                'vtiger_lead_id' => $lead['lead_id'],
                'category' => $category,
                'intent_topics' => $lead['intent_topics'] ?? '',
                'tags' => [
                    'lead411-import',
                    "intent-score-{$lead['intent_score']}",
                    "category-{$category}",
                    'vtiger-synced'
                ]
            ];
            
            // Create contact
            $contactResult = createGHLContact($contactData);
            
            if ($contactResult['success']) {
                $results['created']++;
                $contactId = $contactResult['data']['contact_id'];
                
                // Assign to campaign
                $campaignResult = assignToCampaign($contactId, $campaignId);
                
                if ($campaignResult['success']) {
                    $results['campaign_assigned']++;
                }
                
                $results['details'][] = [
                    'company' => $lead['company'],
                    'intent_score' => $lead['intent_score'],
                    'category' => $category,
                    'contact_id' => $contactId,
                    'campaign' => $campaignId,
                    'status' => 'success'
                ];
            }
            
        } catch (Exception $e) {
            $results['errors']++;
            $results['details'][] = [
                'company' => $lead['company'] ?? 'Unknown',
                'intent_score' => $lead['intent_score'] ?? 0,
                'status' => 'error',
                'error' => $e->getMessage()
            ];
        }
    }
    
    return [
        'success' => true,
        'message' => 'Batch processing completed',
        'data' => $results
    ];
}

function listCampaigns() {
    try {
        $response = makeGHLRequest('GET', '/campaigns');
        
        return [
            'success' => true,
            'message' => 'Campaigns retrieved successfully',
            'data' => [
                'campaigns' => $response['campaigns'] ?? [],
                'count' => count($response['campaigns'] ?? [])
            ]
        ];
        
    } catch (Exception $e) {
        throw new Exception('Failed to retrieve campaigns: ' . $e->getMessage());
    }
}

function makeGHLRequest($method, $endpoint, $data = null) {
    $url = GHL_API_BASE . $endpoint;
    
    $headers = [
        'Authorization: Bearer ' . GHL_API_KEY,
        'Content-Type: application/json'
    ];
    
    $ch = curl_init();
    
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_USERAGENT => 'RDS Lead Generation Automation 1.0'
    ]);
    
    if ($method === 'POST' || $method === 'PUT') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
    } elseif ($method === 'DELETE') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    curl_close($ch);
    
    if ($error) {
        throw new Exception('cURL error: ' . $error);
    }
    
    if ($httpCode >= 400) {
        $errorData = json_decode($response, true);
        $errorMessage = $errorData['message'] ?? "HTTP error: {$httpCode}";
        throw new Exception($errorMessage);
    }
    
    $decoded = json_decode($response, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON response: ' . json_last_error_msg());
    }
    
    return $decoded;
}

function extractFirstName($companyName) {
    // Extract first word as first name
    $words = explode(' ', trim($companyName));
    return $words[0] ?? 'Company';
}

function extractLastName($companyName) {
    // Use remaining words as last name, or 'Lead' if only one word
    $words = explode(' ', trim($companyName));
    if (count($words) > 1) {
        return implode(' ', array_slice($words, 1));
    }
    return 'Lead';
}
?>