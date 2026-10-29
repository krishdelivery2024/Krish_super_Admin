<?php 

class Push {
    
    private $title;
    private $message;
    private $image;
    private $type;
    private $id;
    private $order_type;
    private $custom_data;

    function __construct($title, $message, $image, $type, $id, $order_type='food', $custom_data=array()) {
        $this->title = $title;
        $this->message = $message;
        $this->image = $image;
        $this->type = $type;
        $this->id = $id; 
        $this->order_type = $order_type;
        $this->custom_data = is_array($custom_data) ? $custom_data : array();
    }
    
    public function getPush() {
        $res = array();
        $res['data']['title'] = $this->title;
        $res['data']['message'] = $this->message;
        $res['data']['body'] = $this->message;
        $res['data']['image'] = $this->image;
        $res['data']['type'] = $this->type;
        $res['data']['id'] = (string)$this->id;
        $res['data']['order_id'] = (string)$this->id;
        $res['data']['order_type'] = $this->order_type;

        if (!empty($this->custom_data)) {
            foreach ($this->custom_data as $key => $val) {
                if ($val !== null) {
                    $res['data'][$key] = (string)$val;
                }
            }
        }
        return $res;
    }
}