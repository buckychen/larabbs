<?php


namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\UserRequest;
use App\Models\Image;
use App\Models\User;
use App\Transformers\UserTransformer;
use Illuminate\Http\Request;


class UsersController extends Controller
{
    //
    public function store(UserRequest $request){
        //用户注册流程 传参手机号生成图片验证码及其信息 传参图片验证码若相等发送短信验证码 利用短信验证码key验证 若正确 注册用户
        //利用key得到缓存中的信息
        $verifyData = \Cache::get($request->verification_key);

        if (!$verifyData) {
            return $this->response->error('验证码已失效', 422);
        }

        if (!hash_equals($verifyData['code'], $request->verification_code)) {
            // 返回401
            return $this->response->errorUnauthorized('验证码错误');
        }

        $user = User::create([
            'name' => $request->name,
            'phone' => $verifyData['phone'],
            'password' => bcrypt($request->password),
        ]);

        // 清除验证码缓存
        \Cache::forget($request->verification_key);

        //return $this->response->created();
        return $this->response->item($user, new UserTransformer())
            ->setMeta([
                'access_token' => \Auth::guard('api')->fromUser($user),
                'token_type' => 'Bearer',
                'expires_in' => \Auth::guard('api')->factory()->getTTL() * 60
            ])
            ->setStatusCode(201);
    }

    public function me(){
        return $this->response->item($this->user(),new UserTransformer());
    }

    public function update(UserRequest $request){
        $user = $this->user();

        $attributes = $request->only(['name', 'email', 'introduction']);

        if ($request->avatar_image_id) {
            $image = Image::find($request->avatar_image_id);

            $attributes['avatar'] = $image->path;
        }
        $user->update($attributes);

        return $this->response->item($user, new UserTransformer());
    }

    public function activedIndex(User $user){
        return $this->response->collection($user->getActiveUsers(),new UserTransformer());
    }

    public function weappStore(UserRequest $request){
        //缓存中是否存在相应的key
        $verifyData = \Cache::get($request->verification_key);

        if(!$verifyData){
            return $this->response->error('验证码已失效',422);
        }

        //判断验证码是否相等，不相等返回401错误
        if(!hash_equals((string)$verifyData['code'],$request->verification_code)){
            return $this->response->errorUnauthorized('验证码错误');
        }

        //获取微信openid和session_key
        $miniprogram = \EasyWeChat::miniProgram();
        $data = $miniprogram->auth->session($request->code);

        if(isset($data['errcode'])){
            return $this->response->errorUnauthorized('code不正确');
        }

        //如果openid用户已存在报错403
        $user = User::where('weapp_openid',$data['openid'])->first();

        if($user){
            return $this->response->errorForbidden('微信已绑定其他用户，请直接登录');
        }

        //创建用户
        $user = User::create([
            'name' => $request->name,
            'phone' => $verifyData['phone'],
            'password' => bcrypt($request->password),
            'weapp_openid' => $data['openid'],
            'weixin_session_key' => $data['session_key'],
        ]);

        //清楚验证码缓存
        \Cache::forget($request->verification_key);

        //返回token信息
        return $this->response->item($user, new UserTransformer())
            ->setMeta([
                'access_token' => \Auth::guard('api')->fromUser($user),
                'token_type' => 'Bearer',
                'expires_in' => \Auth::guard('api')->factory()->getTTL() * 60
            ])
            ->setStatusCode(201);
    }
}
