<?php

class VTigerClient {
    private $url;
    private $username;
    private $accessKey;
    private $sessionName;
    private $challengeToken;
    
    public function __construct($url, $username, $accessKey) {
        $this->url = rtrim($url, '/');
        $this->username = $username;
        $this->accessKey = $accessKey;
        $this->sessionName = null;
        $this->challengeToken = null;
    }
    
    /**
     * Get challenge token from VTiger
     */
    public function getChallenge() {
        $url = $this->url . '/webservice.php?operation=getchallenge&username=' . urlencode($this->username);
        
        $response = $this->makeRequest($url, 'GET');
        
        if (!$response['success']) {
            throw new Exception('Failed to get challenge token: ' . $response['error']['message']);
        }
        
        $this->challengeToken = $response['result']['token'];
        return $this->challengeToken;
    }
    
    /**
     * Login to VTiger with challenge token
     */
    public function login($challengeToken = null) {
        if ($challengeToken) {
            $this->challengeToken = $challengeToken;
        }
        
        if (!$this->challengeToken) {
            $this->getChallenge();
        }
        
        // Create access key hash
        $accessKeyHash = md5($this->challengeToken . $this->accessKey);
        
        $postData = [
            'operation' => 'login',
            'username' => $this->username,
            'accessKey' => $accessKeyHash
        ];
        
        $url = $this->url . '/webservice.php';
        $response = $this->makeRequest($url, 'POST', $postData);
        
        if (!$response['success']) {
            throw new Exception('Login failed: ' . $response['error']['message']);
        }
        
        $this->sessionName = $response['result']['sessionName'];
        return $this->sessionName;
    }
    
    /**
     * Ensure we have a valid session
     */
    private function ensureSession() {
        if (!$this->sessionName) {
            $this->login();
        }
    }
    
    /**
     * Create a new record
     */
    public function create($module, $data) {
        $this->ensureSession();
        
        $postData = [
            'operation' => 'create',
            'sessionName' => $this->sessionName,
            'elementType' => $module,
            'element' => json_encode($data)
        ];
        
        $url = $this->url . '/webservice.php';
        $response = $this->makeRequest($url, 'POST', $postData);
        
        if (!$response['success']) {
            throw new Exception('Create operation failed: ' . ($response['error']['message'] ?? 'Unknown error'));
        }
        
        return $response;
    }
    
    /**
     * Execute a query
     */
    public function query($query) {
        $this->ensureSession();
        
        $url = $this->url . '/webservice.php?operation=query&sessionName=' . 
               urlencode($this->sessionName) . '&query=' . urlencode($query);
        
        $response = $this->makeRequest($url, 'GET');
        
        if (!$response['success']) {
            throw new Exception('Query failed: ' . ($response['error']['message'] ?? 'Unknown error'));
        }
        
        return $response;
    }
    
    /**
     * Retrieve a record by ID
     */
    public function retrieve($id) {
        $this->ensureSession();
        
        $url = $this->url . '/webservice.php?operation=retrieve&sessionName=' . 
               urlencode($this->sessionName) . '&id=' . urlencode($id);
        
        $response = $this->makeRequest($url, 'GET');
        
        if (!$response['success']) {
            throw new Exception('Retrieve failed: ' . ($response['error']['message'] ?? 'Unknown error'));
        }
        
        return $response;
    }
    
    /**
     * Update a record
     */
    public function update($data) {
        $this->ensureSession();
        
        $postData = [
            'operation' => 'update',
            'sessionName' => $this->sessionName,
            'element' => json_encode($data)
        ];
        
        $url = $this->url . '/webservice.php';
        $response = $this->makeRequest($url, 'POST', $postData);
        
        if (!$response['success']) {
            throw new Exception('Update failed: ' . ($response['error']['message'] ?? 'Unknown error'));
        }
        
        return $response;
    }
    
    /**
     * Delete a record
     */
    public function delete($id) {
        $this->ensureSession();
        
        $postData = [
            'operation' => 'delete',
            'sessionName' => $this->sessionName,
            'id' => $id
        ];
        
        $url = $this->url . '/webservice.php';
        $response = $this->makeRequest($url, 'POST', $postData);
        
        if (!$response['success']) {
            throw new Exception('Delete failed: ' . ($response['error']['message'] ?? 'Unknown error'));
        }
        
        return $response;
    }
    
    /**
     * List all available modules
     */
    public function listModules() {
        $this->ensureSession();
        
        $url = $this->url . '/webservice.php?operation=listtypes&sessionName=' . 
               urlencode($this->sessionName);
        
        $response = $this->makeRequest($url, 'GET');
        
        if (!$response['success']) {
            throw new Exception('List modules failed: ' . ($response['error']['message'] ?? 'Unknown error'));
        }
        
        return $response;
    }
    
    /**
     * Describe a module (get fields and metadata)
     */
    public function describe($module) {
        $this->ensureSession();
        
        $url = $this->url . '/webservice.php?operation=describe&sessionName=' . 
               urlencode($this->sessionName) . '&elementType=' . urlencode($module);
        
        $response = $this->makeRequest($url, 'GET');
        
        if (!$response['success']) {
            throw new Exception('Describe failed: ' . ($response['error']['message'] ?? 'Unknown error'));
        }
        
        return $response;
    }
    
    /**
     * Make HTTP request to VTiger API
     */
    private function makeRequest($url, $method = 'GET', $data = null) {
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_USERAGENT => 'RDS Lead Generation Automation 1.0'
        ]);
        
        if ($method === 'POST' && $data) {
            curl_setopt($ch, CURLOPT_POST, true);
            if (is_array($data)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            } else {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            }
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        if ($error) {
            throw new Exception('cURL error: ' . $error);
        }
        
        if ($httpCode >= 400) {
            throw new Exception('HTTP error: ' . $httpCode);
        }
        
        $decoded = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON response: ' . json_last_error_msg());
        }
        
        return $decoded;
    }
    
    /**
     * Logout and end session
     */
    public function logout() {
        if (!$this->sessionName) {
            return true;
        }
        
        try {
            $postData = [
                'operation' => 'logout',
                'sessionName' => $this->sessionName
            ];
            
            $url = $this->url . '/webservice.php';
            $this->makeRequest($url, 'POST', $postData);
            
        } catch (Exception $e) {
            // Ignore logout errors
        }
        
        $this->sessionName = null;
        $this->challengeToken = null;
        
        return true;
    }
    
    /**
     * Get current session name
     */
    public function getSessionName() {
        return $this->sessionName;
    }
}
?>