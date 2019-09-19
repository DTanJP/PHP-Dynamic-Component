<?php
/**
 * DynamicComponent.php
 * 
 * @author David Tan
 **/
class DynamicComponent {
    
    /** Constructor **/
    public function __construct() {}
    
    /** Compiles the data array into an string containing html code **/
    public function compile() {
        $output = "";
        extract($this->data);
        $output .= "<$tag"
        .(isset($id) && $id ? " id='$id'" : "");
        
        //Parse class data
        if(isset($class)) {
            if($class) {
                $output .= " class='";
                if(is_array($class)) {
                    foreach($class as $val)
                        $output .= "$val ";
                } else if(is_string($class))
                    $output .= $class;
                $output .= "'";
            }
        }
        
        //Parse element data
        if(isset($data)) {
            if($data) {
                if(is_array($data)) {
                    foreach($data as $key => $value)
                        $output .= " data-$key='$value'";
                }
            }
        }
        
        //Parse attributes
        if(isset($attributes)) {
            if($attributes) {
                if(is_array($attributes)) {
                    foreach($attributes as $key => $value) {
                        if($key && isset($value))
                            $output .= " $key='$value'";
                    }
                }
            }
        }
        
        //Parse interaction events
        if(isset($event)) {
            if($event) {
                if(is_array($event)) {
                    foreach($event as $key => $value)
                        $output .= " $key=\"$value\"";
                }
            }
        }
        
        if($disabled)
            $output .= " disabled";
            
        $output .= ">";
        if(isset($children)) {
            if(is_array($children)) {
                foreach($children as $child)
                    $output .= ($child instanceof DynamicComponent) ? $child->compile() : $child;
            } else 
                $output .= ($children instanceof DynamicComponent) ? $children->compile() : $children;
        }
        if(isset($closingTag) && $closingTag)
            $output .= "</$tag>";
        
        //Save the output to the html variable to prevent unnecessary compile calls
        //Compile once and retrieve the output anywhere again.
        //Compile only once you made all the necessary changes
        $this->html = $output;
        return $output;
    }
    
    //Adds a click event to this component
    public function click($value='') {
        if($value)
            $this->data['event']['onclick'] = $value;
    }
    
    //Adds a class to this component
    public function addClass($value='') {
        if($value)
            $this->data['class'][] = $value;
    }
    
    //Removes a class from this component
    public function removeClass($value) {
        if(in_array($value, $this->data['class']))
            unset($this->data['class'][array_search($value, $this->data['class'])]);
    }
    
    //Attach data to this component
    public function addData($key, $value) {
        if($key && $value)
            $this->data['data'][$key] = $value;
    }
    
    //Remove data from this component
    public function removeData($key) {
        if(isset($this->data['data'][$key]))
            unset($this->data['data'][$key]);
    }
    
    //Attaches a child to this component, it can be text or another component
    public function addChild($child) {
        if($child)
            $this->data['children'][] = $child;
        if($child instanceof DynamicComponent)
            $child->data['parent'] = $this;
    }
    
    //Does a soft reset to this component
    public function reset() {
        $this->data['id'] = "";
        $this->data['class'] = [];
        $this->data['data'] = [];
        $this->data['attributes'] = [];
        $this->data['event'] = [];
        $this->disabled = false;
    }
    
    //Disable/Enable this component
    public function setDisabled($val) {
        $this->data['disabled'] = $val;
    }
    
    //Searches for a child component inside this component and all its childrens
    public function select($query) {
        if(!$query) return false;
        $result = [];
        
        //Example: select(['tag' => 'body']);
        if(is_array($query)) {
            if(!isset($this->data['children'])) return false;
            foreach($this->data['children'] as $child) {
                $count = 0;
                if(!$child) continue;
                if($child instanceof DynamicComponent) {
                    foreach($child->data as $key => $val) {
                        if(isset($query[$key])) {
                            if($query[$key] == $val)
                                $count++;
                        }
                    }
                    //All parameters match
                    if($count == count($query))
                        $result[] = $child;
                    
                    //Search inside the child element as well
                    $add = $child->select($query);
                    if($add) {
                        for($i=0; $i<count($add); $i++)
                            $result[] = $add[$i];
                    }
                }
            }
        } else if(is_string($query)) {
            $identifier = substr($query, 0, 1);
            
            //select("#container");
            if($identifier == "#") {//Search by ID
                $id = substr($query, 1, strlen($query));
                foreach($this->children as $child) {
                    if($child instanceof DynamicComponent) {
                        if(isset($child->data['id'])) {
                            if($child->data['id'] == $id)
                                $result[] = $child;
                        }
                    }
                    $add = $child->select($query);
                    if($add) {
                        for($i=0; $i<count($add); $i++)
                            $result[] = $add[$i];
                    }
                }
            //select(".container");
            } else if($identifier == ".") {//Search by class
                $class = substr($query, 1, strlen($query));
                foreach($this->children as $child) {
                    if($child instanceof DynamicComponent) {
                        if(isset($child->data['class'])) {
                                if(is_array($child->data['class'])) {
                                    foreach($child->data['class'] as $c) {
                                        if($c == $class)
                                            $result[] = $child;
                                    }
                                } else if(is_string($child['class'])) {
                                    if($child['class'] == $class)
                                        $result[] = $child;
                                }
                            }
                        }
                    }
                    $add = $child->select($query);
                    if($add) {
                        for($i=0; $i<count($add); $i++)
                            $result[] = $add[$i];
                    }
                }
            }
            return $result;
        }
    
    /** Variables **/
    public $data = [
        'tag' => 'div',
        'id' => '',
        'class' => [],
        'data' => [],
        'attributes' => [],
        'event' => [],
        'disabled' => false,
        'children' => [],
        'closingTag' => true,
        'parent' => null
    ];
    public $html = "";//compiled output
}
?>
