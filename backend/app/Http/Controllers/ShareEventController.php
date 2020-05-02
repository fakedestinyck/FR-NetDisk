<?php

namespace App\Http\Controllers;

use App\Helpers\Rsa;
use App\Item;
use App\ShareEntry;
use App\ShareEvent;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator;

class ShareEventController extends Controller
{

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'ids' => 'required|array',
            'ids.*' => 'required|integer',
            'expired_at' => 'required|numeric'
        ]);
        //password_confirmation must be included in this string
    }

    protected function sharedValidator(array $data)
    {
        return Validator::make($data, [
            't' => 'required|regex:/^[0-9]+$/i',
            'share_event_id' => 'required|regex:/^[0-9]+$/i',
            'token' => 'required|regex:/^[A-Za-z0-9%]+$/i'
        ]);
        //password_confirmation must be included in this string
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->success(ShareEvent::where('user_id',auth('api')->user()->id)->with('entries.item')->get());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = $this->validator($request->all());
        if ($validator->fails()) {
            return response()->error($validator->errors(), 422);
        }
        $expired_at = Carbon::createFromTimestamp($request->expired_at);

        DB::beginTransaction();
        // 先创建shareEvent
        $shareEvent = ShareEvent::create([
            'user_id' => auth('api')->user()->id,
            'expired_at' => $expired_at
        ]);

        // 拿到shareEventId之后再创建shareEntries
        // 先验证文件存在，且是自己拥有的
        $fileids = $request->input('ids');
        $items = Item::whereIn('id', $fileids)->get();
        if (count($items) != count($fileids)) {
            DB::rollBack();
            return response()->error('一个或多个文件不存在或已被删除', 404);
        }
        foreach ($items as $item) {
            if ($item->user_id != auth('api')->user()->id) {
                DB::rollBack();
                return response()->error('文件权限查询失败，可能是登陆过期，请退出重新登录', 403);
            }
        }
        // 再创建shareEntries
        $entries = array_map(function($value) use ($shareEvent) {
            return [
                'item_id' => $value,
                'share_event_id' => $shareEvent->id,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ];
        }, $fileids);
        ShareEntry::insert($entries);
        DB::commit();

        // 创建一个分享链接，用share_event的updated_at, id 和 user_id创建一个分享链接
        return $this->createShareLink($shareEvent->id, auth('api')->user()->id, $shareEvent->updated_at);

    }

    /**
     * Display the specified resource.
     *
     * @param  Request $request
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $validator = $this->sharedValidator($request->all());
        if ($validator->fails()) {
            return response()->error('找不到分享的资源', 404);
        }
        $share_event_id = $request->share_event_id;
        $t = $request->t;
        $token = $request->token;
        $share_event = ShareEvent::where('id',$share_event_id)->where('updated_at',Carbon::createFromTimestamp($t))->get();
        if (count($share_event) == 0) {
            return response()->error('找不到分享的资源', 404);
        }

        $check_result = $this->retrieveShareLink($share_event_id, $share_event[0]['updated_at'], $share_event[0]['user_id'], $token);
        if (gettype($check_result) != 'string') {
            return $check_result;
        }

        // 下面是返回所有下载地址

        return response()->success('成功');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\ShareEvent  $shareEvent
     * @return \Illuminate\Http\Response
     */
    public function edit(ShareEvent $shareEvent)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ShareEvent  $shareEvent
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ShareEvent $shareEvent)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\ShareEvent  $shareEvent
     * @return \Illuminate\Http\Response
     */
    public function destroy(ShareEvent $shareEvent)
    {
        //
    }

    private function createShareLink($id, $user_id, $updated_at) {
        $md5_id = md5($id);
        $md5_user_id = md5($user_id);
        $t = $updated_at->timestamp;
        $md5_t = md5($t);

        $private_key = '../app/Helpers/Luanqibazao/rsa_private_key.pem'; // 私钥路径
        $public_key = '../app/Helpers/Luanqibazao/rsa_public_key.pem'; // 公钥路径
        try {
            $rsa = new Rsa($private_key, $public_key);
        } catch (FileNotFoundException $e) {
            return response()->error($e->getMessage(), 502);
        } catch (Exception $e) {
            return response()->error('暂时无法分享，请稍后再试E503', 503);
        }

        $origin_data = $md5_id."feng".$md5_user_id."rui".$md5_t;
        $encrypted_data = $rsa->publicEncrypt($origin_data);
        return response()->success([
            'msg' => '成功创建分享链接',
            'data' => $id.'/'.$t.'/'.urlencode($encrypted_data)
        ]);
    }

    private function retrieveShareLink($id, $updated_at, $user_id, $token) {
        $private_key = '../app/Helpers/Luanqibazao/rsa_private_key.pem'; // 私钥路径
        $public_key = '../app/Helpers/Luanqibazao/rsa_public_key.pem'; // 公钥路径
        try {
            $rsa = new Rsa($private_key, $public_key);
            $origin_data = $rsa->privDecrypt(urldecode($token));
        } catch (FileNotFoundException $e) {
            return response()->error($e->getMessage(), 502);
        } catch (Exception $e) {
            return response()->error('暂时无法分享，请稍后再试E503', 503);
        }
        if ($origin_data == null) {
            return response()->error('链接无效', 404);
        }
        $md5_id = md5($id);
        $md5_user_id = md5($user_id);
        $t = $updated_at->timestamp;
        $md5_t = md5($t);

        $correctToken = $md5_id."feng".$md5_user_id."rui".$md5_t;

        if ($origin_data == $correctToken) {
            return '成功';
        } else {
            return response()->error('校验失败', 404);
        }
    }
}
