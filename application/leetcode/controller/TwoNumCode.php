<?php
/**
 * Created by PhpStorm.
 * User: shangwushe
 * Date: 7/13/19
 * Time: 3:10 PM
 */
namespace  app\leetcode\controller;

class TwoNumCode{
    /**
     * two number sum
     * @param $nums array
     * @param $target int
     * @return array
     */
    public function solution1($nums, $target) {
        $result = [];
        $nums_length = count($nums);
        foreach($nums as $first_key => $first_value){
            for($j = $first_key+1;$j<$nums_length;$j++){
                if($target==$first_value+$nums[$j]){
                    $result = [$first_key,$j];
                    break;
                }
            }
        }
        return $result;
    }
    public function solution2($nums, $target) {
        $tmp = [];
        foreach($nums as $key => $value){
            if(isset($tmp[$target-$value])){
                return [$tmp[$target-$value],$key];
            }
            $tmp[$value] = $key;
        }
    }
    public function solution3($nums, $target) {
        $result = [];
        $tmp = array_reverse($nums);
        $length = count($nums);
        foreach($nums as $key => $value){
            if(false!==array_search($target-$value,$tmp)&&$length-array_search($target-$value,$tmp)!=$key+1){
                $result = [$key,$length-array_search($target-$value,$tmp)-1];
                break;
            }
        }
        return $result;
    }

    public function test(){
        dump($this->solution3([2,7,5,11,9],9));
        dump($this->solution3([3,2,4],6));
        dump($this->solution3([3,3],6));
    }
}
