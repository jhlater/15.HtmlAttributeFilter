<?php
/** HTML Attribute Filter
*   Date:   2013-09-22
*   Author: fdipzone
*   ver:    1.0
*
*   Func:
*   public  strip              过滤属性
*   public  setAllow           设置允许的属性
*   public  setException       设置特例
*   public  setIgnore          设置忽略的标记
*   private findElements       搜寻需要处理的元素
*   private findAttributes     搜寻属性
*   private removeAttributes   移除属性
*   private isException        判断是否特例
*   private createAttributes   创建属性
*   private protect            特殊字符转义
*/

class HtmlAttributeFilter{ // class start

    private $_str = '';            // 源字符串
    private $_allow = array();     // 允许保留的属性 例如:array('id','class','title')
    private $_exception = array(); // 特例 例如:array('a'=>array('href','class'),'span'=>array('class'))
    private $_ignore = array();    // 忽略过滤的标记 例如:array('span','img')


    /** 处理HTML,过滤不保留的属性
    * @param  String $str 源字符串
    * @return String
    */
    public function strip($str){
        $this->_str = $str;

        if(is_string($this->_str) && strlen($this->_str)>0){ // 判断字符串

            $this->_str = strtolower($this->_str); // 转成小写

            $res = $this->findElements();
            if(is_string($res)){
                return $res;
            }
            $nodes = $this->findAttributes($res);
            $this->removeAttributes($nodes);
        }

        return $this->_str;
    }


    /** 设置允许的属性
    * @param Array $param
    */
    public function setAllow($param=array()){
        $this->_allow = $param;
    }


    /** 设置特例
    * @param Array $param
    */
    public function setException($param=array()){
        $this->_exception = $param;
    }


    /** 设置忽略的标记
    * @param Array $param
    */
    public function setIgnore($param=array()){
        $this->_ignore = $param;
    }


    /** 搜寻需要处理的元素 */
    private function findElements(){
        $nodes = array();
        preg_match_all("/<([^ !\/\>\n]+)([^>]*)>/i", $this->_str, $elements);
        foreach($elements[1] as $el_key => $element){
            if($elements[2][$el_key]){
                $literal = $elements[0][$el_key];
                $element_name = $elements[1][$el_key];
                $attributes = $elements[2][$el_key];
                if(is_array($this->_ignore) && !in_array($element_name, $this->_ignore)){
                    $nodes[] = array('literal'=>$literal, 'name'=>$element_name, 'attributes'=>$attributes);
                }
            }
        }

        if(!$nodes[0]){
            return $this->_str;
        }else{
            return $nodes;
        }
    }


    /** 搜寻属性
    *  @param Array $nodes 需要处理的元素
    */
    private function findAttributes($nodes){
        foreach($nodes as &$node){
            preg_match_all("/([^ =]+)\s*=\s*[\"|']{0,1}([^\"']*)[\"|']{0,1}/i", $node['attributes'], $attributes);
            if($attributes[1]){
                foreach($attributes[1] as $att_key=>$att){
                    $literal = $attributes[0][$att_key];
                    $attribute_name = $attributes[1][$att_key];
                    $value = $attributes[2][$att_key];
                    $atts[] = array('literal'=>$literal, 'name'=>$attribute_name, 'value'=>$value);
                }
            }else{
                $node['attributes'] = null;
            }
            $node['attributes'] = $atts;
            unset($atts);
        }
        return $nodes;
    }


    /** 移除属性
    *  @param Array $nodes 需要处理的元素
    */
    private function removeAttributes($nodes){
        foreach($nodes as $node){
            $node_name = $node['name'];
            $new_attributes = '';
            if(is_array($node['attributes'])){
                foreach($node['attributes'] as $attribute){
                    if((is_array($this->_allow) && in_array($attribute['name'], $this->_allow)) || $this->isException($node_name, $attribute['name'], $this->_exception)){
                        $new_attributes = $this->createAttributes($new_attributes, $attribute['name'], $attribute['value']);
                    }
                }
            }
            $replacement = ($new_attributes) ? "<$node_name $new_attributes>" : "<$node_name>";
            $this->_str = preg_replace('/'.$this->protect($node['literal']).'/', $replacement, $this->_str);
        }
    }


    /** 判断是否特例
    * @param String $element_name   元素名
    * @param String $attribute_name 属性名
    * @param Array  $exceptions     允许的特例
    * @return boolean
    */
    private function isException($element_name, $attribute_name, $exceptions){
        if(array_key_exists($element_name, $this->_exception)){
            if(in_array($attribute_name, $this->_exception[$element_name])){
                return true;
            }
        }
        return false;
    }


    /** 创建属性
    * @param  String $new_attributes
    * @param  String $name
    * @param  String $value
    * @return String
    */
    private function createAttributes($new_attributes, $name, $value){
        if($new_attributes){
            $new_attributes .= " ";
        }
        $new_attributes .= "$name=\"$value\"";
        return $new_attributes;
    }


    /** 特殊字符转义
    * @param  String $str 源字符串
    * @return String
    */
    private function protect($str){
        $conversions = array(
            "^" => "\^", 
            "[" => "\[", 
            "." => "\.", 
            "$" => "\$", 
            "{" => "\{", 
            "*" => "\*", 
            "(" => "\(", 
            "\\" => "\\\\", 
            "/" => "\/", 
            "+" => "\+", 
            ")" => "\)", 
            "|" => "\|", 
            "?" => "\?", 
            "<" => "\<", 
            ">" => "\>" 
        );
        return strtr($str, $conversions);
    }

} // class end

?>