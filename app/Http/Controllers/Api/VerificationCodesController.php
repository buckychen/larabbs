<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\VerificationCodeRequest;
use Illuminate\Http\Request;
use Overtrue\EasySms\EasySms;


class VerificationCodesController extends Controller
{
    //
    public function store(VerificationCodeRequest $request, EasySms $easySms)
    {
        $captchaData = \Cache::get($request->captcha_key);

        if(!$captchaData){
            return $this->response->error('图片验证码已失效',422);
        }

        if(!hash_equals($captchaData['code'],$request->captcha_code)){
            //验证码错误就清楚缓存
            \Cache::forget($request->captcha_key);
            return $this->response->errorUnauthorized('验证码错误');
        }

        $phone = $captchaData['phone'];

        //若非生产环境
        if(!app()->environment('production')){
            $code = '1234';
        }else{
            // 生成4位随机数，左侧补0
            $code = str_pad(random_int(1, 9999), 4, 0, STR_PAD_LEFT);

            try {
                //利用easysms发送短信
                $result = $easySms->send($phone, [
                    'content'  =>  "【陈伟权】您的验证码是{$code}。如非本人操作，请忽略本短信"
                ]);
            } catch (\Overtrue\EasySms\Exceptions\NoGatewayAvailableException $exception) {
                $message = $exception->getException('yunpian')->getMessage();
                return $this->response->errorInternal($message ?: '短信发送异常');
            }
        }

        //生成key
        $key = 'verificationCode_'.str_random(15);
        //生成验证码产生时间 10分钟过期。
        $expiredAt = now()->addMinutes(10);
        // 缓存验证码
        \Cache::put($key, ['phone' => $phone, 'code' => $code], $expiredAt);

        //清除图片验证码缓存
        \Cache::forget($request->captcha_key);
        return $this->response->array([
            'key' => $key,
            'expired_at' => $expiredAt->toDateTimeString(),
        ])->setStatusCode(201);
    }

}
