<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\CaptchaRequest;
use Gregwar\Captcha\CaptchaBuilder;
use Illuminate\Http\Request;

class CaptchasController extends Controller
{
    //
    public function store(CaptchaRequest $request, CaptchaBuilder $captchaBuilder){
        $key = 'captcha-'.str_random(15);
        $phone = $request->phone;

        //生成验证码图片
        $captcha = $captchaBuilder->build();
        //设置过期时间
        $expiredAt = now()->addMinutes(2);
        \Cache::put($key,['phone' => $phone,'code' => $captcha->getPhrase()],$expiredAt);

        $result = [
            'cpatcha_key' => $key,
            'expired_at' => $expiredAt->toDateTimeString(),
            'cpatcha_image_content' => $captcha->inline()
        ];

        return $this->response->array($result)->setStatusCode(201);
    }
}