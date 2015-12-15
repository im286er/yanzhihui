<?php
namespace Api\Model;

class LevelModel extends CommonModel {
    
    public function do_index(){
        $list = M('Level')->select();
        return $list[0];
    }

}