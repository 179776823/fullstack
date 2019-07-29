<?php

use Illuminate\Database\Seeder;

class MenusTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('menus')->insert([
            ['id' => 1,'name' => '文章管理','guard_name' => 'admin','icon' => 'file-word','pid' => 0,'sort' => 0,'path' => '/article','show'  => 1,'status' => 1],
            ['id' => 2,'name' => '文章列表','guard_name' => 'admin','icon' => '','pid' => 1,'sort' => 0,'path' => '/article/index','show'  => 1,'status' => 1],
            ['id' => 3,'name' => '发布文章','guard_name' => 'admin','icon' => '','pid' => 1,'sort' => 0,'path' => '/article/create','show'  => 1,'status' => 1],
            ['id' => 4,'name' => '我的文章','guard_name' => 'admin','icon' => '','pid' => 1,'sort' => 0,'path' => '/article/myPublished','show'  => 1,'status' => 1],
            ['id' => 5,'name' => '单页管理','guard_name' => 'admin','icon' => 'file-ppt','pid' => 0,'sort' => 0,'path' => '/page','show'  => 1,'status' => 1],
            ['id' => 6,'name' => '单页列表','guard_name' => 'admin','icon' => '','pid' => 5,'sort' => 0,'path' => '/page/index','show'  => 1,'status' => 1],
            ['id' => 7,'name' => '添加单页','guard_name' => 'admin','icon' => '','pid' => 5,'sort' => 0,'path' => '/page/create','show'  => 1,'status' => 1],
            ['id' => 8,'name' => '会员管理','guard_name' => 'admin','icon' => 'user-add','pid' => 0,'sort' => 0,'path' => '/user','show'  => 1,'status' => 1],
            ['id' => 9,'name' => '会员列表','guard_name' => 'admin','icon' => '','pid' => 8,'sort' => 0,'path' => '/user/index','show'  => 1,'status' => 1],
            ['id' =>10,'name' => '添加会员','guard_name' => 'admin','icon' => '','pid' => 8,'sort' => 0,'path' => '/user/create','show'  => 1,'status' => 1],
            ['id' =>11,'name' => '管理员','guard_name' => 'admin','icon' => 'usergroup-add','pid' => 0,'sort' => 0,'path' => '/admin','show'  => 1,'status' => 1],
            ['id' =>12,'name' => '管理员列表','guard_name' => 'admin','icon' => '','pid' => 11,'sort' => 0,'path' => '/admin/user','show'  => 1,'status' => 1],
            ['id' =>13,'name' => '权限列表','guard_name' => 'admin','icon' => '','pid' => 11,'sort' => 0,'path' => '/admin/permission','show'  => 1,'status' => 1],
            ['id' =>14,'name' => '角色列表','guard_name' => 'admin','icon' => '','pid' => 11,'sort' => 0,'path' => '/admin/role','show'  => 1,'status' => 1],
            ['id' =>15,'name' => '广告管理','guard_name' => 'admin','icon' => 'file-word','pid' => 0,'sort' => 0,'path' => '/banner','show'  => 1,'status' => 1],
            ['id' =>16,'name' => '广告列表','guard_name' => 'admin','icon' => '','pid' => 15,'sort' => 0,'path' => '/banner/banner','show'  => 1,'status' => 1],
            ['id' =>17,'name' => '广告位列表','guard_name' => 'admin','icon' => '','pid' => 15,'sort' => 0,'path' => '/banner/bannerCategory','show'  => 1,'status' => 1],
            ['id' =>18,'name' => '应用插件','guard_name' => 'admin','icon' => 'snippets','pid' => 0,'sort' => 0,'path' => '/plugin','show'  => 1,'status' => 1],
            ['id' =>19,'name' => '评论管理','guard_name' => 'admin','icon' => '','pid' => 18,'sort' => 0,'path' => '/plugin/comment','show'  => 1,'status' => 1],
            ['id' =>20,'name' => '友情链接','guard_name' => 'admin','icon' => '','pid' => 18,'sort' => 0,'path' => '/plugin/link','show'  => 1,'status' => 1],
            ['id' =>21,'name' => '打印机管理','guard_name' => 'admin','icon' => '','pid' => 18,'sort' => 0,'path' => '/plugin/printer','show'  => 1,'status' => 1],
            ['id' =>22,'name' => '系统配置','guard_name' => 'admin','icon' => 'setting','pid' => 0,'sort' => 0,'path' => '/system','show'  => 1,'status' => 1],
            ['id' =>23,'name' => '设置管理','guard_name' => 'admin','icon' => '','pid' => 22,'sort' => -2,'path' => '/system/config','show'  => 1,'status' => 1],
            ['id' =>24,'name' => '网站设置','guard_name' => 'admin','icon' => '','pid' => 23,'sort' => 0,'path' => '/system/config/website','show'  => 1,'status' => 1],
            ['id' =>25,'name' => '配置管理','guard_name' => 'admin','icon' => '','pid' => 23,'sort' => 0,'path' => '/system/config/index','show'  => 1,'status' => 1],
            ['id' =>26,'name' => '所有导航','guard_name' => 'admin','icon' => '','pid' => 22,'sort' => 0,'path' => '/system/navigation','show'  => 1,'status' => 1],
            ['id' =>27,'name' => '分类列表','guard_name' => 'admin','icon' => '','pid' => 22,'sort' => 0,'path' => '/system/category','show'  => 1,'status' => 1],
            ['id' =>28,'name' => '短信列表','guard_name' => 'admin','icon' => '','pid' => 22,'sort' => 0,'path' => '/system/sms/index','show'  => 1,'status' => 1],
            ['id' =>29,'name' => '操作日志','guard_name' => 'admin','icon' => '','pid' => 22,'sort' => 0,'path' => '/system/actionLog/index','show'  => 1,'status' => 1],
            ['id' =>30,'name' => '附件空间','guard_name' => 'admin','icon' => 'paper-clip','pid' => 0,'sort' => 0,'path' => '/attachment','show'  => 1,'status' => 1],
            ['id' =>31,'name' => '文件管理','guard_name' => 'admin','icon' => '','pid' => 30,'sort' => 0,'path' => '/attachment/file','show'  => 1,'status' => 1],
            ['id' =>32,'name' => '图片管理','guard_name' => 'admin','icon' => '','pid' => 30,'sort' => 0,'path' => '/attachment/picture','show'  => 1,'status' => 1],
            ['id' =>33,'name' => '我的账号','guard_name' => 'admin','icon' => 'user','pid' => 0,'sort' => 0,'path' => '/account','show'  => 1,'status' => 1],
            ['id' =>34,'name' => '个人设置','guard_name' => 'admin','icon' => '','pid' => 33,'sort' => 0,'path' => '/account/settings','show'  => 1,'status' => 1],
            ['id' =>35,'name' => '菜单管理','guard_name' => 'admin','icon' => '','pid' => 22,'sort' => -1,'path' => '/system/menu/index','show'  => 1,'status' => 1],
            ['id' =>36,'name' => '编辑文章','guard_name' => 'admin','icon' => '','pid' => 1,'sort' => 0,'path' => '','show'  => 0,'status' => -1],
            ['id' =>39,'name' => '控制台','guard_name' => 'admin','icon' => 'home','pid' => 0,'sort' => -2,'path' => '/console','show'  => 1,'status' => 1],
            ['id' =>40,'name' => '基础权限','guard_name' => 'admin','icon' => '','pid' => 39,'sort' => 0,'path' => null,'show'  => 0,'status' => -1],
            ['id' =>41,'name' => '主页','guard_name' => 'admin','icon' => '','pid' => 39,'sort' => 0,'path' => '/console/index?id=1','show'  => 1,'status' => 1],
            ['id' =>45,'name' => '文章编辑','guard_name' => 'admin','icon' => 'bars','pid' => 42,'sort' => 0,'path' => 'article/edit','show'  => 1,'status' => -1],
            ['id' =>46,'name' => '网站编辑','guard_name' => 'admin','icon' => 'desktop','pid' => 42,'sort' => 0,'path' => '/article/create','show'  => 1,'status' => -1]
        ]);
    }
}
