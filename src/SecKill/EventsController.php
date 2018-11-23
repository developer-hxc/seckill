<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-11-22
 * Time: 11:35
 */

namespace HXC\SecKill;


Trait EventsController
{
    /**
     * 购买
     * @param array $params uid:会员id，gid:商品id
     * @return mixed
     */
    public function buy($params = [])
    {
        $redis = self::$redis;
        $key = self::$prefix."goods_{$params['gid']}";
        if(!$redis->exists("{$key}_stock")){
            return ['code' => 0,'msg' => '秒杀商品不存在'];
        }
        //此处+1是因为创建对应集合时有一个默认成员
        if($redis->scard($key) >= ($redis->get("{$key}_stock")+1)){
            return ['code' => 0,'msg' => '商品已被抢空'];
        }
        $res = $redis->sadd($key,$params['uid']);
        if($res == 0){
            return ['code' => 0,'msg' => '每人限购一件'];
        }else{
            return ['code' => 1,'msg' => '商品秒杀成功','uid' => $params['uid']];
        }
    }

    /**
     * 关闭秒杀
     * @param $gid 关闭单个商品的秒杀
     */
    public function close($gid = '')
    {
        $redis = self::$redis;
        if($gid){
            $key = self::$prefix."goods_{$gid}";
            if($redis->exists($key)){//秒杀存在
                $redis->del($key);
                $redis->del($key.'_stock');
                return ['code' => 1,'msg' => '该商品秒杀已关闭'];
            }else{
                return ['code' => 0,'msg' => '秒杀商品不存在'];
            }
        }else{
            $redis->flushall();
            return ['code' => 1,'msg' => '秒杀全部关闭'];
        }
            
    }
}