<?php
   /* Copyright Jun (junthehacker) Zheng All Rights Reserved.
    * Licenced under Apache 2.0
    * Jun Zheng （junthehacker） 版权所有
    * 通过Apache 2.0许可证授权
    */

    namespace Haumea;  

    class EventNotRegisteredException extends \Exception { }
    class InvalidCallableException extends \Exception { }
    class ParamTypeException extends \Exception { }
    class EventAlreadyRegisteredException extends \Exception { }

    class ResponseStatus {
        public static $notFound      = 404;
        public static $ok            = 200;
        public static $created       = 201;
        public static $internalError = 500;
        public static $badRequest    = 400;
        public static $notAuthorized = 401;
        public static $serviceNotAvaliable = 501;
    }

    class XMLSerializer {

        public static function generateValidXmlFromObj(stdClass $obj, $node_block='nodes', $node_name='node') {
            $arr = get_object_vars($obj);
            return self::generateValidXmlFromArray($arr, $node_block, $node_name);
        }

        public static function generateValidXmlFromArray($array, $node_block='nodes', $node_name='node') {
            $xml = '<?xml version="1.0" encoding="UTF-8" ?>';
            $xml .= '<' . $node_block . '>';
            $xml .= self::generateXmlFromArray($array, $node_name);
            $xml .= '</' . $node_block . '>';
            return $xml;
        }

        private static function generateXmlFromArray($array, $node_name) {
            $xml = '';
            if (is_array($array) || is_object($array)) {
                foreach ($array as $key=>$value) {
                    if (is_numeric($key)) {
                        $key = $node_name;
                    }
                    $xml .= '<' . $key . '>' . self::generateXmlFromArray($value, $node_name) . '</' . $key . '>';
                }
            } else {
                $xml = htmlspecialchars($array, ENT_QUOTES);
            }
            return $xml;
        }
    }

    /* (callable) -> int
     * Count parameters for callable object
     */
    function paramCount($callable) {
        $CReflection = \is_array($callable) ? 
        new \ReflectionMethod($callable[0], $callable[1]) : 
        new \ReflectionFunction($callable);
        return $CReflection->getNumberOfParameters();
    }


    class App {
        public $router;

        public $event_handlers = array(
            "will_add_route" => array(),
            "added_route" => array(),
            "will_respond" => array(),
            "responded" => array()
        );

        // HTTP response body
        public $responseBody = array(
            "status"  => "not found",
            "message" => "",
            "data"    => null
        );

        public $responseSent = false;
        public $jsonPretty = false;
        public $virtualMode = false;

        /* __construct() -> App
         * Initialize app instance
         */
        function __construct(){
            $this->router = new \Haumea\Router($this);
        }

        /* get(string, function) -> null
         * Add a new get route to application.
         * Route expression format:
         * If referring to root directory, use "/"
         * If referring to sub directories, do not start and/or end with "/", for example "subdir/subsubdir"
         * If want to use variables, do this "subdir/%unknowndir/test/%anotherunknown"
         * Callback format:
         * Callback function must accept three parameters: arguments, router and app
         * Arguments are variables in route expression
         * Router is a router instance
         * App is this object
         */
        public function get($route, $callback){
            $this->invoke('will_add_route', array("GET", $route, $callback));
            $this->router->get($route, $callback);
            $this->invoke('added_route', array("GET", $route, $callback));
        }

        /* post(string, function) -> null
         * Add a new post route to application.
         * Route expression and callback format: refer to get method
         */
        public function post($route, $callback){
            $this->invoke('will_add_route', array("POST", $route, $callback));
            $this->router->post($route, $callback);
            $this->invoke('added_route', array("POST", $route, $callback));
        }

        /* put(string, function) -> null
         * Add a new put route to application.
         * Route expression and callback format: refer to get method
         */
        public function put($route, $callback){
            $this->invoke('will_add_route', array("PUT", $route, $callback));
            $this->router->put($route, $callback);
            $this->invoke('added_route', array("PUT", $route, $callback));
        }

        /* delete(string, function) -> null
         * Add a new delete route to application.
         * Route expression and callback format: refer to get method
         */
        public function delete($route, $callback){
            $this->invoke('will_add_route', array("DELETE", $route, $callback));
            $this->router->delete($route, $callback);
            $this->invoke('added_route', array("DELETE", $route, $callback));
        }

        /* setJsonPrettyPrint(bool) -> null
         * Set if JSON output should be pretty printed.
         */
        public function setJsonPrettyPrint($bool){
            $this->jsonPretty = $bool;
        }

        /* setResponseStatus(\Haumea\ResponseStatus) -> App
         * Set HTTP response status code and message.
         * Return true if http response status is set, false otherwise.
         * Requirement: status must be valid, now only support 404, 200, 500 and 401
         */
        public function setResponseStatus($status){
            if ($status == \Haumea\ResponseStatus::$notFound) {
                \http_response_code(404);
                $this->responseBody["status"] = "not found";
            } else if ($status == \Haumea\ResponseStatus::$ok) {
                \http_response_code(200);
                $this->responseBody["status"] = "ok";
            } else if ($status == \Haumea\ResponseStatus::$internalError) {
                \http_response_code(500);
                $this->responseBody["status"] = "internal error";
            } else if ($status == \Haumea\ResponseStatus::$notAuthorized) {
                \http_response_code(401);
                $this->responseBody["status"] = "not authorized";
            } else if ($status == \Haumea\ResponseStatus::$badRequest) {
                \http_response_code(400);
                $this->responseBody["status"] = "bad request";
            } else if ($status == \Haumea\ResponseStatus::$created) {
                \http_response_code(201);
                $this->responseBody["status"] = "created";
            } else if ($status == \Haumea\ResponseStatus::$serviceNotAvaliable) {
                \http_response_code(501);
                $this->responseBody["status"] = "service not avaliable";
            }
            return $this;
        }

        /* setRspStat(int) -> App
         * Alias for SetResponseStatus
         */
        public function setRspStat($status){
            return $this->setResponseStatus($status);
        }

        /* setResponseMessage(string) -> App
         * Set HTTP response message.
         */
        public function setResponseMessage($message){
            $this->responseBody["message"] = $message;
            return $this;
        }

        /* setRspMsg(string) -> App
         * Alias for setResponseMessage.
         */
        public function setRspMsg($message){
            return $this->setResponseMessage($message);
        }

        /* setResponseData(object) -> App
         * Set HTTP response data.
         */
        public function setResponseData($data){
            $this->responseBody["data"] = $data;
            return $this;
        }

        /* setRspData(object) -> App
         * Alias for setResponseData.
         */
        public function setRspData($data){
            return $this->setResponseData($data);
        }

        /* respond() -> null
         * Send HTTP response. This method can only be called once.
         */
        public function respond(){
            $this->invoke('will_respond', array());
            if(!$this->responseSent){
                // Set content type to be json
                $request_headers = $this->router->getRequestHeaders();
                $type = "json";
                if(isset($request_headers["Accept"]) || isset($request_headers["accept"])){
                    if(isset($request_headers["Accept"])){
                        $accepts = explode(",", $request_headers["Accept"]);
                    } else {
                        $accepts = explode(",", $request_headers["accept"]);
                    }
                    if(in_array("application/xml", $accepts)){
                        $type = "xml";
                    }
                }
                if($type === "xml"){
                    header('Content-type: application/xml');
                    echo XMLSerializer::generateValidXmlFromArray($this->responseBody);
                } else {
                    header('Content-type: application/json');
                    // Check if we should pretty print JSON
                    if($this->jsonPretty){
                        echo \json_encode($this->responseBody, JSON_PRETTY_PRINT);
                    } else {
                        echo \json_encode($this->responseBody);
                    }
                    
                }
                $this->responseSent = true;
            }
            $this->invoke('responded', array());
        }

        /* (string, callable) -> null
         * Registers an new event with an event handler
         * req: Event name must not be a registered event name in event_handlers
         *      func must be a callable with two parameters
         * inv: this->event_handlers will have one more entry
         *      callable will be registered under event_name
         */
        public function register($event_name, $func){
            // Detect fatal errors
            if(isset($this->event_handlers[$event_name])){
                throw new EventAlreadyRegisteredException("Event already registered"); die();
            }
            // Register new event
            $this->event_handlers[$event_name] = array();
            $this->on($event_name, $func);
        }

        /* (string, object) -> mixed
         * Manually invoke an event.
         * req: Event name must be a registered event name in event_handlers
         * inv: All handlers will be called if no fatal exception is raised.
         */
        public function invoke($event_name, $args){
            // Check for fatal errors
            if(!in_array($event_name, array_keys($this->event_handlers))){
                throw new EventNotRegisteredException("Event name " . $event_name . " not registered"); die();
            }
            $handlers = $this->event_handlers[$event_name];
            foreach($handlers as $handler){
                return $handler($args, $this);
            }
        }

        /* (string, callable) -> null
         * Bind a new handler to specified event.
         * req: Event name must be a registered event name in event_handlers
         *      func must be a callable with two parameters
         * inv: this->event_handlers total child += 1
         *      func will be added to fire queue
         */
        public function on($event_name, $func){
            // Check for fatal errors
            if(!in_array($event_name, array_keys($this->event_handlers))){
                throw new EventNotRegisteredException("Event name " . $event_name . " not registered"); die();
            }
            if(!is_callable($func)){
                throw new ParamTypeException("Callback not callable"); die();
            }
            if (paramCount($func) !== 2){
                throw new InvalidCallableException("Callable takes in exactly 2 arguments"); die();
            }
            // Push new event handler
            array_push($this->event_handlers[$event_name], $func);
        }

        /* start() -> null
         * Start the application
         */
        public function start(){
            if(!$this->virtualMode){
                $this->router->route();
            }
        }

        public function setVirtualMode($bool){
            $this->virtualMode = $bool;
        }

        public function virtualSimpleGet($route){
            $this->router->virtualPath = $route;
            return $this->router->route();
        }

    }

    class Router {
        public $routes = array(
            "GET"=>array(),
            "POST"=>array(),
            "PUT"=>array(),
            "GET"=>array()
        );

        public $virtualPath = "/";
        public $virtualHeader = array();
        public $virtualBody = array();
        public $virtualMethod = "GET";

       /* __construct() -> Router
        * Initialize router instance
        */
        function __construct($app){
            $this->app = $app;
        }

       /* getRequestPath() -> string
        * Get current request path, for example:
        * http://localhost/haumea_framework/test/something/user
        * Will return
        * something/user
        */
        public function getRequestPath(){
            if($this->app->virtualMode){
                return $this->virtualPath;
            }
            $request_uri = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
            $script_name = explode('/', trim($_SERVER['SCRIPT_NAME'], '/'));
            $parts = array_diff_assoc($request_uri, $script_name);
            if (empty($parts))
            {
                return '/';
            }
            $path = implode('/', $parts);
            if (($position = strpos($path, '?')) !== FALSE)
            {
                $path = substr($path, 0, $position);
            }
            return $path;
        }

       /* getRequestMethod() -> string
        * Get current request method, or http verb
        */
        public function getRequestMethod(){
            if($this->app->virtualMode){
                return $this->virtualMethod;
            }
            return $_SERVER['REQUEST_METHOD'];
        }

       /* getRequestHeaders() -> array of string
        * Get request headers, returns an array of string containing all request headers
        */
        public function getRequestHeaders(){
            if($this->app->virtualMode){
                return $this->virtualHeaders;
            }
            return getallheaders();
        }

       /* getRequestBody() -> array of string
        * Get request body, or raw payload.
        */
        public function getRequestBody(){
            if($this->app->virtualMode){
                return $this->virtualBody;
            }
            if(empty($this->requestBody)){
                parse_str(file_get_contents("php://input"),$body);
                $this->requestBody = $body;
            }
            return $this->requestBody;
        }

       /* get(string, function) -> null
        * Add a new get route to application.
        * Route expression and callback format: refer to app->get method
        */
        public function get($path,$callback){
            $this->routes["GET"][$path] = $callback;
        }

       /* post(string, function) -> null
        * Add a new post route to application.
        * Route expression and callback format: refer to app->get method
        */
        public function post($path,$callback){
            $this->routes["POST"][$path] = $callback;
        }

       /* put(string, function) -> null
        * Add a new put route to application.
        * Route expression and callback format: refer to app->get method
        */
        public function put($path,$callback){
            $this->routes["PUT"][$path] = $callback;
        }

       /* delete(string, function) -> null
        * Add a new delete route to application.
        * Route expression and callback format: refer to app->get method
        */
        public function delete($path,$callback){
            $this->routes["DELETE"][$path] = $callback;
        }

       /* routeExists(array of routes, string) -> false or array
        * Takes in an array of routes, and a path string
        * Check if a route exists, if a route exist return array(path expression, arguments parsed from path)
        * If route does not exist, return false
        */
        public static function routeExists($arr,$path){
            foreach($arr as $k=>$v){
                $routeArgs = self::matchPath($k,$path);
                if($routeArgs){
                    return array($k,$routeArgs[1]);
                }
            }
            return false;
        }

       /* matchPath(string, string) -> false or array
        * Takes in a route expression and a path string, return array(1, arguments parsed from path) if match
        * If they does not match, return false
        */
        public static function matchPath($exp,$path){
            $exp = explode("/",$exp);
            $path = explode("/",$path);
            $variables = array();
            if(count($exp) == count($path)){
                for ($i = 0; $i < count($exp); $i++){
                    if(!empty($exp[$i][0]) && $exp[$i][0] == "%"){
                        $variables[substr($exp[$i],1,strlen($exp[$i]) - 1)] = $path[$i];
                    } else {
                        if($exp[$i] != $path[$i]){
                            return false;
                        }
                    }
                }
            } else {
                return false;
            }

            return array(1,$variables);
        }

       /* reroute(string, string) -> null
        * Reroute to another route.
        * Throws an exception if route is not found.
        */
        public function reroute($method,$route){
            $routeCheck = $this->routeExists($this->routes[$method],$route);
            if($routeCheck){
                $this->routes[$method][$routeCheck[0]]($routeCheck[1],$this->app);
            } else {
                throw new \Exception(ErrorCodes\ROUTE_NOT_EXIST_ERROR);
            }
        }

       /* route() -> null
        * Do an automatic routing, it is recommended to only call this once.
        */
        public function route(){
            $route_check = $this->routeExists($this->routes[self::getRequestMethod()],$this->getRequestPath());
            if($route_check){
                // We check to see if the route is a chain or a single callable
                $callbacks = $this->routes[self::getRequestMethod()][$route_check[0]];
                // If callbacks is not a chain, we build a one element chain
                if(!is_array($callbacks)){
                    $callbacks = array($callbacks);
                }
                // Otherwise, execute the chain
                foreach($callbacks as $callback){
                    if(is_string($callback)){
                        $result = $this->app->invoke($callback, array());
                    } else if (is_callable($callback)){
                        $result = $callback($route_check[1], $this->app);
                    } else {
                        // We have an error
                    }
                    if (is_array($result) && count($result) === 3){
                        if($this->app->virtualMode){
                            return $result;
                        }
                        $this->app->setRspStat($result[0])
                                  ->setRspMsg($result[1])
                                  ->setRspData($result[2])
                                  ->respond();
                        break;
                    }
                }
            } else {
                // If not found route is also not found. Then respond with default not found behavior
                if(!$this->app->virtualMode){
                    if(empty($this->routes[self::getRequestMethod()]["notfound"])){
                        $this->app->setResponseStatus(\Haumea\ResponseStatus::$notFound);
                        $this->app->setResponseMessage("This is a default not found message from haumea framework.");
                        $this->app->respond();
                    } else {
                        $result = $this->routes[self::getRequestMethod()]["notfound"](array(),$this->app);
                        $this->app->setRspStat($result[0])
                                  ->setRspMsg($result[1])
                                  ->setRspData($result[2])
                                  ->respond();
                    }
                } else {
                    return false;
                }
            }
        }

    }
?>