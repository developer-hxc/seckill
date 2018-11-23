<?php
namespace HXC\SecKill;

class SeckillController
{
    use EventsController;

    protected static $prefix;
    protected static $redis;

    /**
     * 初始化
     * @return \Redis
     */
    public static function init()
    {
        $config = C('SecKill');
        $redis = new \Redis();
        if(!$config['host'] || !$config['port']) throw_exception('秒杀配置有误');
        $prefix = isset($config['prefix'])?$config['prefix']:'';
        self::$prefix = $prefix;
        $connect = $redis->pconnect($config['host'],$config['port']);
        if(!$connect) throw_exception('redis链接失败');
        self::$redis = $redis;
        return new self();
    }

    /**
     * 增加秒杀商品库存
     * @param array $goods id:商品id,num商品库存
     * @return \Redis|void
     */
    public function start($goods = [])
    {
        if(empty($goods)) return throw_exception('秒杀商品不能为空');
        $config = C('SecKill');
        $redis = new \Redis();
        if(!$config['host'] || !$config['port']) throw_exception('秒杀配置有误');
        $prefix = isset($config['prefix'])?$config['prefix']:'';
        self::$prefix = $prefix;
        $connect = $redis->pconnect($config['host'],$config['port']);
        if(!$connect) throw_exception('redis链接失败');
        self::$redis  = $redis;
        foreach ($goods as $k => $v){
            $redis->set(self::$prefix."goods_{$v['id']}",0); //创建默认集合
            $redis->set(self::$prefix."goods_{$v['id']}_stock",$v['num']); //创建产品库存
            $redis->expire(self::$prefix."goods_{$v['id']}_stock",$v['expire']); //设置key有效期
            $redis->expire(self::$prefix."goods_{$v['id']}",$v['expire']); //设置key有效期
        }
        return ['code' => 1,'msg' => '秒杀开启成功'];
    }

}