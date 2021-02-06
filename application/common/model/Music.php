<?php

namespace app\common\model;

use think\Model;

class Music extends Model
{
    public $page_info;



    /**
     * 音乐列表添加操作
     *
     * @access music_Insert
     * @author duxinxin
     * @date 2020/04/26
     */
    public function music_Insert($data){

        return   db('music')->insert($data,true);

    }

    /**
     * 用户修改数据操作
     *
     * @access edit
     * @author duxinxin
     * @date 2020/04/26
     */
    public function edit($id, $condition){

        return    db('set')->where('id', $id)->update($condition);


    }

    /**
     * 查询设置
     *
     * @access music_Insert
     * @author duxinxin
     * @date 2020/04/26
     */
    public function set($field = '*'){

        return db('set')->field($field)->where(['id' => 1])->find();

    }

    /**
     * 音乐查询一条
     *
     * @access musicFind
     * @author duxinxin
     * @date 2020/04/26
     */
    public function musicFind($condition){

        return  db('music')->field(['id','title','artist','mp3','poster'])->where($condition)->find();

    }

    /**
     * 查询音乐列表集合
     *
     * @access classPage
     * @author duxinxin
     * @date 2020/04/26
     */
    public function  musicPage(){

        //查询集合并分页
        $news = db('music')
            ->field(['id','title','artist','mp3','poster'])
//            ->whereLike('classname',"%".$condition['classname']."%")
//            ->where($condition['where'])
            ->order('id DESC')->paginate(20,false,['query'=>request()->param()]);

        //查询集合数量
        $num = db('music')
//            ->whereLike('classname',"%".$condition['classname']."%")
//            ->where($condition['where'])
            ->count('id');

        return $show = [
            'page'=>$news->render(),
            'data'=>$news->all(),
            'num' =>$num,
        ];
    }

    /**
     * 查询音乐全部集合
     *
     * @access classPage
     * @author duxinxin
     * @date 2020/04/26
     */
    public function  musicAll($condition = ''){

        //查询集合
        $arr =  db('music')
            ->field(['id','title','artist','mp3','poster'])
            ->where($condition)
            ->order('id DESC')
            ->select();

        return $arr;
    }
}

?>
