<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

use think\Route;
// 注册路由到index模块的News控制器的read操作
Route::rule('showIndexData','index/Index/showIndexData','POST');
Route::rule('opApplyList','index/Index/opApplyList','POST');
Route::rule('opApply','index/Index/opApply','POST');
Route::rule('siteApplyList','index/Index/siteApplyList','POST');
Route::rule('opNum','index/Index/opNum','GET');
Route::rule('imgsUpload','index/Index/imgsUpload','POST');
Route::rule('getUserInfo','index/Index/getUserInfo','POST');
Route::rule('allCheckInfo','index/Index/allCheckInfo','POST');
Route::rule('goodsLike','index/Index/goodsLike','POST');
Route::rule('checkDetail','index/Index/checkDetail','POST');
Route::rule('throughCheck','index/Index/throughCheck','POST');
Route::rule('rejectRequest','index/Index/rejectRequest','POST');
Route::rule('punishmentStoreOp','index/Index/punishmentStoreOp','POST');
Route::rule('storeLikeList','index/Index/storeLikeList','POST');

//意见反馈
Route::rule('feedback','index/Index/feedback','POST');
Route::rule('ideaList','index/Index/ideaList','POST');

//店铺相关
Route::rule('storeCollect','index/Index/storeCollect','POST');
Route::rule('storeGoods','index/Index/storeGoods','POST');
Route::rule('collectList','index/Index/collectList','POST');
Route::rule('myStoreStatus','index/Index/myStoreStatus','POST');
Route::rule('myAllCheckInfo','index/Index/myAllCheckInfo','POST');
Route::rule('storeLike','index/Index/storeLike','POST');

//店铺菜品
Route::rule('checkGoods','index/Index/checkGoods','POST');
Route::rule('delGoods','index/Index/delGoods','POST');
Route::rule('updateGoods','index/Index/updateGoods','POST');

//公告
Route::rule('announcementList','index/Index/announcementList','POST');
Route::rule('delAnnouncement','index/Index/delAnnouncement','POST');
Route::rule('releaseAnnouncement','index/Index/releaseAnnouncement','POST');
Route::rule('updateAnnouncement','index/Index/updateAnnouncement','POST');
Route::rule('getAnnouncement','index/Index/getAnnouncement','POST');

//登录
Route::rule('sendSms','index/Index/sendSms','POST');
Route::rule('login','index/Index/login','POST');
Route::rule('onLogin','index/Index/onLogin','POST');

//测试
Route::rule('updateToken','index/Index/updateToken','POST');
