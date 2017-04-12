<?php
    class AppData {
        public $sessions = array();
        function __construct(){
            $data = json_decode(file_get_contents(__DIR__ . "/../data/sessions.json"), true);
            foreach($data as $i=>$session){
                $this->sessions[] = new Session(
                    $i,
                    $session["speaker"],
                    $session["title"],
                    $session["description"],
                    $session["offered"]
                );
            }
        }
    }

    class Session {
        public $id;
        public $speaker;
        public $title;
        public $description;
        public $offered;

        public function __construct($id, $speaker, $title, $description, $offered){
            $this->id = $id;
            $this->speaker = $speaker;
            $this->title = $title;
            $this->description = $description;
            $this->offered = $offered;
        }
    }
?>