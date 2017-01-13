<?php
class ChaoshiCartModel extends RelationModel
{
    const HJL_USER_NO_EXISTS=330;
    const HJL_ADD_SUCSS=200;
    const HJL_ADD_ERR=201;
    const HJL_SAVE_SUCSS=202;
    const HJL_SAVE_ERR=203;
    const HJL_GOODS_NO_EXISTS=101;

    protected $_link=array(
        'product_info'=>array(
                    'mapping_type'  => BELONGS_TO,
                    'class_name'    => 'ChaoshiProduct',
                    'foreign_key'   => 'product_id',
                    
            ),
    );

    public function check_product($user_id,$product_id){
        $res = $this->where(array('user_id'=>$user_id,'product_id'=>$product_id))->find();
        if ($res){
            return $res['id'];
        }else{
            return false;
        }
    }
    //获得购物车信息
    public function get_store_cart_info($uid,$store_id){
        $where['user_id'] = $uid;
        $where['store_id'] = $store_id;
        $list = $this->where($where)->relation('product_info')->order('id asc')->select();
        $total=0;
        foreach ($list as $k => &$v) {
    //        $v['product_info']['photo_path']=get_remote_file_path($v['product_info']['photo']);
            $total+= $v['total_price'];
        }

        $cart_info['list'] = $list;
        $cart_info['total'] = $total;

        return $cart_info;
    }
    //加入购物车
    public function cart_add($product_id,$num=1,$opt="+",$uid){
     
        if(!$uid){
            return self::HJL_USER_NO_EXISTS;
        }
        $product_mod = M('chaoshi_product');
        $product_info = $product_mod->find($product_id);
        if(!$product_info){
            return self::HJL_GOODS_NO_EXISTS;
        }
        
        //购物车检测
        $cart_info = $this->where(array('product_id'=>$product_id,'user_id'=>$uid))->find();
        if($cart_info){
            if($opt=="+"){
                $num = $cart_info['num']+$num;
            }else{
                $num = $cart_info['num']-$num;
            }
            if($num>0){
                $update['num'] = $num;
                $update['total_price']=$num*$cart_info['price'];
                $update['id']=$cart_info['id'];
                $status = $this->save($update);
                if($status){
                    return self::HJL_SAVE_SUCSS;
                }else{
                    return self::HJL_SAVE_ERR;
                }
            }else{
               $this->delete($cart_info['id']);
               return self::HJL_SAVE_SUCSS; 
            }
        }else{
            $data['user_id'] = $uid;
            $data['product_id'] = $product_id;
            $data['store_id'] = $product_info['store_id'];
            $data['num'] = $num;
            $data['price'] = $product_info['price'];
            $data['total_price'] = $data['price']*$data['num'];
            $status = $this->add($data);
            if($status){
                return self::HJL_ADD_SUCSS;
            }else{
                return self::HJL_ADD_ERR;
            }

        }

    } 
    //更新购物车
    public function update_cart($cart_id,$num){
       $cart_info = $this->find($cart_id);
       $data['id']=$cart_id; 
       $data['num']=$num; 
       $data['total_price']=$num*$cart_info['price']; 
       return $this->save($data);
    }

    
    //商品删除时清空购物车对应的商品
    public function clearByProductId($productId)
    {
    	return $this->where(array('product_id' => $productId))->delete();
    }
    
    //店铺删除时清空购物车对应的商品
    public function clearByStoreId($storeId)
    {
    	return $this->where(array('store_id' => $storeId))->delete();
    }
}