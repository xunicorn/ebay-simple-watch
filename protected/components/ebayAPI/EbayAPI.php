<?php

    abstract class EbayAPI extends CComponent {
        const ERROR_RESPONSE_EMPTY  = 'response-empty';
        const ERROR_RESPONSE_FAILED = 'response-failed';

        protected $sandbox = false;
        
        protected $debug = false;

        protected $loginURL;
        protected $shoppingURL;
        protected $findingURL;
        protected $tradingURL;

        protected $USER_TOKEN;
        protected $DEV_ID;
        protected $APP_ID;
        protected $CERT_ID;
        protected $RuName;
        
        protected $COMPATIBILITY_LEVEL;
        protected $SHIPPING_COUNTRY = 'US';

        protected $resp;
        protected $xml;

        protected $errors = array(
            EbayAPI::ERROR_RESPONSE_EMPTY  => 'Server response is empty.',
            EbayAPI::ERROR_RESPONSE_FAILED => 'Server response is failed.',
        );

        protected $error_msg = null;

        abstract protected function initVars();
        abstract protected function initErrors();
        abstract public function makeAPICall();

        public function __construct($sandbox = true) {

            //$this->sandbox = $sandbox;
            
            if($this->sandbox) {
                $this->tradingURL  = 'https://api.sandbox.ebay.com/ws/api.dll';
                $this->loginURL    = 'https://signin.sandbox.ebay.com/ws/eBayISAPI.dll';
                $this->shoppingURL = 'http://open.api.sandbox.ebay.com/shopping?';
                $this->findingURL  = 'http://svcs.sandbox.ebay.com/services/search/FindingService/v1?';
                
                $this->USER_TOKEN = 'AgAAAA**AQAAAA**aAAAAA**uBo/Vg**nY+sHZ2PrBmdj6wVnY+sEZ2PrA2dj6wFk4GhCZeGoQWdj6x9nY+seQ**JQYCAA**AAMAAA**y0Qv3Qca4Do6/xpJsCt+ZhspYyrx4LXx7W1eCYW27lRK0uBu/Fs6L10W+qPxdGFYfaEHqPK1Foc+MXg0rrjLHw/x3Ekk2LUHWDDmbX5nJfNpf8kKeROX6x7IcjHr9RczSdNJSMJS/sflwMJ5SGBPbKSeDVTlEioRa75AyOTwhR35dKhiV1Gu1JtoTV8LHABLqvr9WAzyAIx9+4JQHfitX69EfNFvFwETn4sk1pqoXfCxS5y4an/sMKD68sZsMDbJqknCxHGAVMl9Tk2wCxTqpZmXKYJfPRfn5pavZrn+m55DNYqrqer9Zw4o83YG4eykH+nzu7206iNrG/ye09aekbtwXTSbGWVJZCPljh+C0/x3aoahBLVmum3eRRggkfKXxfNGKeceigo+n/NR/9Kz+lRemmAHFnsq9F0GKTstKPNgItM4W7Giu65RuToAqOIkEHXbQpTWCQuR0ZNs5OdNTalTRNDxwnYoJ7SWNlRNYO1GdsnbKTA+wBe63mFZBJulHh6ELwjdOL9k5URpsrKVRzVXKotLmWP0uyiv5EZDgCZLvFcLvXC1kMFPwn9siIhmUDqSBxT0edOk+HbTV4NiwuYe6lAKqbHkhTpG50lEN6BpO8nc1G7TSoLf1m8VzrseW191Ym5hoDYjcZdOr/9Yjih7X31UkFVRSlLkMZmShXN+lCb4M0eiA39/bKY3q3KyYMzPkMFGuCs/TIv2D8lHT3YNXvQyf9IulyAv3dV/ywtvGPHB4KvmhrS/jvZsnoRP';
                $this->DEV_ID     = '9012d999-bcd2-4195-8845-e4a8489145e0';
                $this->APP_ID     = 'AngelaSm-8cd7-49f3-94b8-e871ee952cae';
                $this->CERT_ID    = '6369488f-5120-406d-b03c-229405c1534d';
                $this->RuName     = 'Angela_Smith-AngelaSm-8cd7-4-sjrbu';

                $this->COMPATIBILITY_LEVEL = '927';
            } else {
                $this->tradingURL  = 'https://api.ebay.com/ws/api.dll';
                $this->loginURL    = 'https://signin.ebay.com/ws/eBayISAPI.dll';
                $this->shoppingURL = 'http://open.api.ebay.com/shopping?';
                $this->findingURL  = 'http://svcs.ebay.com/services/search/FindingService/v1?';

                $this->USER_TOKEN  = 'AgAAAA**AQAAAA**aAAAAA**NRk/Vg**nY+sHZ2PrBmdj6wVnY+sEZ2PrA2dj6AFlIenCpOApAqdj6x9nY+seQ**kLEBAA**AAMAAA**q5A7wru4tIESkhfmzpZ/7gDEy2+3YJ6iFtyPdnrALcWOJGR0QhoP629kdsJZ3njjlNY7gzlTgJRSAqgxX3tSPuZ7tQWZvabMQpiSsygmdoOdZ5A24ASgH9oFJI5Hp/Zs0kvVTbi2DmAEB/RLbIRsGqcOjq8kLFbxfijsoJqAZ6gCDJHNDuNE2wQ1i5V0FVhmQoKBirDbs1CKBwgqXBbzbuEjHYahOUJ4VazPgHMF2qsj7Z/e5RUyUfZk8lvWS04LJpIwLkZqXGN8MNu+HJ/omj7JxN0UM5AFBnODPWxG8ftcXMYbCGt6IN2aV+e/w0w9PdfGg59GSuaD+Lj42dx8eHOvGuEtWt7UvOY6w8eb0egerrQh6q8ZIDLYvR/LWhLi+81m9BWsF7M06uV8rwOnFcEJ/Gy5n8BWYdLLBJKAFJnCARpjTpjYobaNC3jtHJcN622oO54+PkwYA8JoNq6GM+yFGkJ11lhDKPqKsIjDEYBy96pqdT35N0dR6/8Y582fGy1/exrwRO9Cu+TAoPFzG9BynyWN9d7jS+0PWYeDeP/WR1Pd9S2DsgKdgx11KWfcn+N5M1+H31sTzcq/O4n8WQpEcNj0q5NJpF6STBIF4Mj7Wf9mde9X1TDmfJ6kHCD3Ti8T/EbOJgF7UDyCZtydhc8HfpoMcLQTMji0k4ihCJSjdYs/Zsvvm2r5+guE8p2L41h+5Vf118aOPE0ZCh+gttf7vDQ6rCp+ZljQFQiHH5sdxv4VIafkVLu/04bQxAA1';
                $this->DEV_ID      = '9012d999-bcd2-4195-8845-e4a8489145e0';
                $this->APP_ID      = 'AngelaSm-1b2c-41ef-a425-1a098fd0abbb';
                $this->CERT_ID     = 'f8c97507-1f3c-48e9-8bd8-fd6e98737767';
                $this->RuName      = 'Angela_Smith-AngelaSm-1b2c-4-vlpsop';

                $this->COMPATIBILITY_LEVEL = '803';
            }

            $this->initVars();
            $this->initErrors();
        }
        
        public function getSandboxMode() {
            return (int)$this->sandbox;
        }


        protected function send($url, $headers, $body) {
            
            if(empty($url) || empty($headers) || empty($body)) {
                return false;
            }
            
            $curlHandler = curl_init();
            if (empty($curlHandler)) {
                    return false;
            }

            curl_setopt($curlHandler, CURLOPT_URL, $url);
            curl_setopt($curlHandler, CURLOPT_HEADER, 0);
            curl_setopt($curlHandler, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curlHandler, CURLOPT_CONNECTTIMEOUT, 120);
            curl_setopt($curlHandler, CURLOPT_TIMEOUT, 120);
            curl_setopt($curlHandler, CURLOPT_POST, 1);
            curl_setopt($curlHandler, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curlHandler, CURLOPT_POSTFIELDS, $body);
            curl_setopt($curlHandler, CURLOPT_HTTPHEADER, $headers);

            $result = curl_exec($curlHandler);

            if($this->debug) {
                echo '<pre>';
                print_r(curl_getinfo($curlHandler));
                var_dump($result);
                echo '</pre>';
            }

            curl_close($curlHandler);

            return $result;
        }

        public function getError() {
            return $this->error_msg;
        }

        /*
                Parsing time in eBay format
                $time - string
                return string
        */
        protected function parseTimeLeft($time)
        {

            if($time == "PT0S") {
                $time = "Ended";
            }
            else
            {
                $patt = "#^P([0-9]*)D?T([0-9]*)H?([0-9]*)M?([0-9]*)S?$#U";
                preg_match($patt, $time, $matches);
                $time = '';
                if(isset($matches[1])) {
                    $time .= trim($matches[1]) . " day" ;
                    if(intval($matches[1]) > 1) {
                        $time .= 's';
                    }
                    $time .= ' ';
                    //$time_arr['days'] = intval($matches[1]) - 1;
                }
                if(isset($matches[2])) {
                    $time .= trim($matches[2]) . " hour";
                    if(intval($matches[2]) > 1) {
                        $time .= 's';
                    }
                    $time .= ' ';
                    //$time_arr['hour'] = intval($matches[2]);
                }
                if(isset($matches[3])) {
                    $time .= trim($matches[3]) . " min ";
                    //$time_arr['minutes'] = intval($matches[3]);
                }
                if(isset($matches[4])) {
                    $time .= trim($matches[4]) . " sec ";
                    //$time_arr['seconds'] = intval($matches[4]);
                }
            }

            return $time;
        }
    }

