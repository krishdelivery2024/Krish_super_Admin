<?php 

class Push {
    
    private $title;
    private $message;
    private $image;
    private $type;
    private $id;
    private $order_type;

    function __construct($title, $message, $image,$type,$id,$order_type='food') {
        $this->title = $title;
        $this->message = $message;
        $this->image = $image;
        $this->type = $type;
        $this->id = $id; 
        $this->order_type = $order_type;
    }
    
    
    public function getPush() {
        $res = array();
        $res['data']['title'] = $this->title;
        $res['data']['message'] = $this->message;
        $res['data']['body'] = $this->message;
        $res['data']['image'] = $this->image;
        $res['data']['type'] = $this->type;
        $res['data']['id'] = $this->id;
        $res['data']['order_type'] = $this->order_type;
       // print_r(json_encode($res));
        return $res;
    }
 
}