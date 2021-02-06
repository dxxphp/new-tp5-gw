<?php

/*
 * 首页相关基本调用
 */
namespace app\home\controller;
use think\Lang;
class Index extends BaseMall
{
    const rows = 15;
    const page = 1;

    // 音乐默认值
    const for_one = 1;  //列表循环播放 默认
    const for_two = 2;  //单曲播放
    const for_three = 3;  //随机播放

    const syns_no = 1;  //关闭音乐同步 默认
    const syns_yes = 2;  //开启音乐同步

    public function _initialize()
    {
        parent::_initialize();
        Lang::load(APP_PATH . 'home/lang/'.config('default_lang').'/index.lang.php');
    }

    public function index()
    {
        $this->redirect('index/show');
        $this->assign('cases_list', $this->_get_cases_list());
        $this->assign('product_list', $this->_get_product_list());
        $this->assign('news_column', $this->_get_news_column());
        
        $this->_assign_seo();
        
        return $this->fetch($this->template_dir . 'index');
    }

    /**
     *  音乐列表
     *
     *
     * @method getMode
     */

    public function show(){

        $curpage = input('page') ? input('page') : self::page;//当前第x页，

        $data = model('music')->set();

        if($data['syns'] == self::syns_yes){

            $this->syns();

        }

        $music =  model('music')->musicPage();

        $seach = trim($this->request->get('seach'));

        $musicAll =  model('music')->musicAll();

        if(!empty($seach)){

            $musicFind =  model('music')->musicFind(['artist' => $seach]);

            $list = [];

            switch ($data['fores']){

                case self::for_one:

                    foreach($musicAll as $key => $val){

                        if($val['id'] <= $musicFind['id']){
                            $list[$key]['title'] = $val['title'];
                            $list[$key]['artist'] = $val['artist'];
                            $list[$key]['mp3'] = $val['mp3'];
                        }
                    }


                    break;
                case self::for_two:

                    for ($i=1; $i<=count($musicAll); $i++)
                    {
                        $list[$i]['title'] = $musicFind['title'];
                        $list[$i]['artist'] = $musicFind['artist'];
                        $list[$i]['mp3'] = $musicFind['mp3'];
                    }


                    break;

                case self::for_three:

                    shuffle($musicAll);
                    foreach($musicAll as $key => $val){

                        $list[$key]['title'] = $val['title'];
                        $list[$key]['artist'] = $val['artist'];
                        $list[$key]['mp3'] = $val['mp3'];
                    }

                    array_unshift($list,$musicFind);
                    break;

            }


        }else{

            switch ($data['fores']){

                case self::for_one:

                    foreach($musicAll as $key => $val){

                        $list[$key]['title'] = $val['title'];
                        $list[$key]['artist'] = $val['artist'];
                        $list[$key]['mp3'] = $val['mp3'];

                    }
                    break;

                case self::for_two:



                    for ($i=1; $i<=count($musicAll); $i++)
                    {
                        $list[$i]['title'] = $musicAll[0]['title'];
                        $list[$i]['artist'] = $musicAll[0]['artist'];
                        $list[$i]['mp3'] = $musicAll[0]['mp3'];
                    }
                    break;

                case self::for_three:

                    shuffle($musicAll);
                    foreach($musicAll as $key => $val){

                        $list[$key]['title'] = $val['title'];
                        $list[$key]['artist'] = $val['artist'];
                        $list[$key]['mp3'] = $val['mp3'];

                    }
                    break;

            }
        }

        $this->assign('fors', $data['fores']);
        $this->assign('syns', $data['syns']);
        $this->assign('plist', $music);

        $this->assign('page', $curpage);

        $this->assign('json',  json_encode(array_values($list),JSON_UNESCAPED_SLASHES));//json 格式化

        return $this->fetch($this->template_dir . 'ind');
    }


    //修改播放模式和同步开关
    public function status(){

        $type = $this->request->post('type');


        if($type == 1){

            $fores = $this->request->post('fores');

            $music =  model('music')->edit(1,['fores' => $fores]);


        }else{

            $syns = $this->request->post('syns');

            $music =  model('music')->edit(1,['syns' => $syns]);

        }

        if($music){

            return json("200");
        }

    }


    //从文件夹中同步音乐到 数据库
    public function syns(){


        $file_path="uploads/music";

        $data = $this->folder_list($file_path);//遍历当前目录

        foreach($data as $key => $val){

            $artist = trim(current(explode('.',$val['artist'])));

            $val['artist'] = $artist;

            model('music')->music_Insert($val);


        }

    }


    //遍历出目录
    public function folder_list($dir){
        $dir .= substr($dir, -1) == '/' ? '' : '/';
        $dirInfo = array();
        foreach (glob($dir.'*') as $v) {
            $vs = explode("/",$v);

            $music = explode("-",$vs[2]);

            $dirInfo[] = ['title' => $music[0],'artist' =>$music[1] ,'mp3' => 'http://'.$_SERVER['SERVER_NAME'] .'/'.$v ,'poster' =>'' ];
            if(is_dir($v)){
                $dirInfo = array_merge($dirInfo, $this->folder_list($v));
            }
        }
        return $dirInfo;
    }










    /**
     * 获取案例列表
     * @return array
     */
    public function _get_cases_list()
    {
        $key = 'home_cases';
        $hoem_cases_list = rcache($key);
        if (empty($hoem_cases_list)) {
            //获取案例列表（8条）
            $hoem_cases_list = model('cases')->getCasesList(array(), '*', 0, 8);
            wcache($key, $hoem_cases_list, '', 36000);
        }
        return $hoem_cases_list;
    }

    /**
     * 获取产品列表
     * @return array
     */
    public function _get_product_list()
    {
        $key = 'home_product';
        $home_product_list = rcache($key);
        if (empty($home_product_list)) {
            //获取产品列表（8条）
            $home_product_list = model('product')->getProductList(array(), '*', 0, 8);
            wcache($key, $home_product_list, '', 36000);
        }
        return $home_product_list;
    }

    /**
     * 获取新闻栏目列表
     * @return array
     */
    public function _get_news_column()
    {
        $condition = array();
        $where = array();
        $condition['column_module'] = COLUMN_NEWS;
        $condition['parent_id'] = 0;
        $key = 'home_column_news';
        $home_column_news_list = rcache($key);
        if (empty($home_column_news_list)) {
            $home_column_news_list = model('column')->getColumnList($condition, 3);
            foreach ($home_column_news_list as $key => $news_list) {
                //获取新闻栏目列表
                $where['column_id'] = $news_list['column_id'];
                $home_column_news_list[$key]['news_list'] = model('news')->getNewsList($where, 5);
            }
            wcache($key, $home_column_news_list, '', 36000);
        }
        return $home_column_news_list;
    }
}