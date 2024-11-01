<?php   
    class tinyaiex_openai {
        protected $openai_api = "";
        protected $error = null ;
        protected $openai_organisation_id = null ;
        protected $client;
        protected $license_key = "";
        protected $host = "";
        protected $checker_api_url = "https://construct.pdk.hu/tinyAIEx-checker/checker_api_own.php" ;
        protected $temperature = "";
        private $max_tokens = array("gpt-3.5-turbo" => 4000, "gpt-4-1106-preview" => 64000) ;
        private $model = "" ;

        public function __construct($license_key) {
            $this->license_key = $license_key ;            
            $this->load_vars() ;
        }        

        public function set_temperature($value) {
            $this->temperature = intval($value) ;
        }

        public function set_model($value) {
            $this->model = $value ;
        }

        public function set_api_key($value) {
            $this->openai_api = $value ;
        }        

        private function load_vars() {
/*            $parse = parse_url(get_site_url());
            $this->host = $parse['host'] ;
            $params = ["action" => "request_check", "k" => $this->license_key, "d" => $this->host] ;
            $args = array('body' => $params, 'timeout' => 60, 'httpversion' => '1.1', 'compress' => false, 'decompress' => false);
            $response = wp_safe_remote_post($this->checker_api_url, $args);
            $response = $response["body"] ;
            
            if (isset($response) && strlen($response) > 0) {
                $response_obj = json_decode($response) ;                
                if ($response_obj->status == 1) {
                    $decapi = substr($response_obj->message, 0, strpos($response_obj->message, "-") + 1) ;
                    $api_rest = substr($response_obj->message, strlen($decapi)) ;
                    $api_rest = strrev($api_rest) ;
                    $this->openai_api = $decapi . $api_rest ;    
                    $this->error = null ;    
                } 
                else
                {
                    $this->error = $response_obj ;
                }                               
            }*/
        }

        private function update_account_data($action, $args) {
            switch ($action) {
                case "ut": $params = ["action" => $action, "k" => $this->license_key, "ut" => $args["tc"], "d" => $this->host] ;
                break ;
            }
            $args = array('body' => $params, 'timeout' => 60, 'httpversion' => '1.1', 'compress' => false, 'decompress' => false);
            $response = wp_safe_remote_post($this->checker_api_url, $args);
        }

        public function getAccountData() {
            $params = ["action" => "get_acc_data", "k" => $this->license_key, "d" => $this->host] ;
            $args = array('body' => $params, 'timeout' => 60, 'httpversion' => '1.1', 'compress' => false, 'decompress' => false);
            $response = wp_safe_remote_post($this->checker_api_url, $args);
            $curlResponse = $response["body"] ;
            return $curlResponse ;
        }

        public function getCompletionAnswer($question) {
            $question = strip_tags($question) ;
            $token_array = tinyaiex_gpt_encode($question);
            $max_tokens = $this->max_tokens[$this->model] - count($token_array) ;
            $return = array("code" => 0, "message" => "") ;
            if (isset($this->openai_api) && $this->openai_api != "") {
                if ($question != "") {
                    $headers  = [
                        'Content-Type' => 'application/json',
                        'Authorization' => ' Bearer ' . $this->openai_api
                    ];                    
                    $postData = [
                        'model' => "text-davinci-003",
                        'prompt' => $question,
                        'temperature' => $this->temperature,
                        'max_tokens' => $max_tokens,
                        'top_p' => 1,
                        'frequency_penalty' => 0.0,
                        'presence_penalty' => 0.0,
                    ];                                   
                                

                    $args = array('body' => wp_json_encode($postData), 'timeout' => 60, 'httpversion' => '1.1', 'compress' => false, 'decompress' => false, 'headers' => $headers);
                    $response = wp_safe_remote_post('https://api.openai.com/v1/completions', $args);
                    $response = $response["body"] ;
        
                    $result = json_decode($response) ;                             
                    $this->update_account_data("ut", array("tc" => $result->usage->total_tokens)) ;
                    
                    if (is_array($result->choices)) {
                        foreach ($result->choices as $result) {
                            $return["message"] .= $result->message->content;
                        }                                                           
                        $return["code"] = 1 ;
                    }
                }
            }   
            else
            {
                $return["message"] = "Could not get API key." ;
            }         
            return $return ;
        }

        public function getChatAnswer($question) {
            $question = strip_tags($question) ;
            $token_array = tinyaiex_gpt_encode($question);
            $max_tokens = $this->max_tokens[$this->model] - count($token_array) ;
            $return = array("code" => 0, "message" => "") ;            
            if (isset($this->openai_api) && $this->openai_api != "") {
                if ($question != "") {
                    $headers  = [
                        'Content-Type' => 'application/json',
                        'Authorization' => ' Bearer ' . $this->openai_api
                    ];                    
                    $postData = array(
                        "model" => $this->model,
                        "messages" => array(
                            array(
                                "role" => "user",
                                "content" => $question
                            )
                        ),
                        "max_tokens" => $max_tokens,
                        "temperature" => $this->temperature
                    );                                    

                    $args = array('body' => wp_json_encode($postData), 'timeout' => 60, 'httpversion' => '1.1', 'compress' => false, 'decompress' => false, 'headers' => $headers);
                    $response = wp_safe_remote_post('https://api.openai.com/v1/chat/completions', $args);
                    $response = $response["body"] ;

                    $result = json_decode($response) ;                             
                    $this->update_account_data("ut", array("tc" => $result->usage->total_tokens)) ;

                    if (is_array($result->choices)) {
                        foreach ($result->choices as $result) {
                            $return["message"] .= $result->message->content; 
                        }        
                        $return["code"] = 1 ;
                    }
                    else if (is_object($result->error)) {
                        $return["message"] .= $result->error->message ;
                        $return["code"] = 0 ;
                    }
                }
            }
            else
            {
                $return["message"] = "Could not get API key." ;                      
            }                 
            return $return ;
        }


    }