<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Qcloud;
use App\Item;
use Illuminate\Http\Request;
use App\Helpers\sts;

class ItemController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!isset($_GET['path'])) {
            $path = '';
        } else {
            $path = str_replace('%2F','/',$_GET['path']);
        }
        $items = Item::where('user_id',auth('api')->user()->id)
            ->where('parent',$path)
            ->whereNotNull('absolute_location')
            ->where('absolute_location','!=','')
            ->where('absolute_location','!=','//')
            ->get();
        if (count($items) > 0) {
            return response()->success($items);
        }
        // 如果没有文件，判断文件夹为空还是不存在
        // 如果查询的是根目录，直接返回空数组
        if ($path == '') {
            return response()->success([]);
        }
        $itsParent = substr($path,0,strrpos($path,'/'));
        $itsName = substr(strrchr($path,'/'),1);
        $itself = Item::where('user_id',auth('api')->user()->id)
            ->where('parent',$itsParent)
            ->whereNotNull('absolute_location')
            ->where('absolute_location','!=','')
            ->where('absolute_location','!=','//')
            ->where('name',$itsName)
            ->get();
        if (count($itself) > 0) {
            return response()->success($items);
        }
        return response()->error('文件夹不存在',404);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return void
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
        // TODO: 检查父目录是否存在
        // return response()->error('父目录不存在', 422);
        if (!$request->has('parent') || $request->parent == null ||str_replace(' ','',$request->parent) == '') {
            $parent = '';
        } else {
            $parent = $request->parent;
        }
        if (!isset($request->type) || $request->type != 'file' && $request->type != 'folder') {
            return response()->error('上传数据格式无效', 422);
        }

        // TODO:检查文件名不能重复 但是可以有同名的file和folder
        $existing = Item::where('user_id',auth('api')->user()->id)->where('parent', $parent);
        // 如果是文件夹
        if ($request->type == 'folder') {
            if (!isset($request->name) || str_replace(' ','',$request->name) == '' || strpos($request->name,'/') !== false ) {
                return response()->error('文件名不合法', 422);
            }

            // 检查是否重复
            $existing = $existing->where('type','folder')->pluck('name')->toArray();
            if (in_array($request->name, $existing)) {
                return response()->error('文件夹已存在', 408);
            }

            // 创建文件夹只在业务服务器创建，不在qcloud创建
            $item = Item::create([
                'name' => $request->name,
                'type' => 'folder',
                'user_id' => auth('api')->user()->id,
                'parent' => $parent,
                'absolute_location' => '/' . auth('api')->user()->username . $parent . '/' . $request->name
            ]);
        } else {
            $files = $request->input('files');
            if (!$files || !is_array($files) || count($files) == 0) {
                return response()->error('没有选择任何文件', 422);
            }
            $fileids = array();
            DB::beginTransaction();
            // 检查是否重复
            $existing = $existing->where('type','file')->pluck('name')->toArray();
            foreach ($files as $file) {
                // TODO: 检查用户已上传文件大小是否总和超标
                if (str_replace(' ','',$file['name']) == '' || strpos($file['name'],'/') !== false) {
                    DB::rollBack();
                    return response()->error('文件名不合法', 422);
                }

                if (in_array($file['name'], $existing)) {
                    DB::rollBack();
                    return response()->error('至少已存在一个同名文件', 408);
                }

                $item = Item::create([
                    "name" => $file['name'],
                    "size" => $file['size'],
                    "parent" => $parent,
                    'type' => 'file',
                    'user_id' => auth('api')->user()->id
                ]);
                $fileids[$file['iid']] = $item->id;
            }
            DB::commit();

        }

        if ($request->type == 'file') {
            if (count($fileids) > 0) {
                return response()->success([
                    'msg' => '创建成功',
                    'data' => $fileids
                ]); // 创建文件
            }
        } else if ($item) {
            return response()->success('创建成功'); // 创建文件夹成功
        } else {
            return response()->error('创建文件出错', 595);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Item  $item
     * @return \Illuminate\Http\Response
     */
    public function show(Item $item)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Item  $item
     * @return \Illuminate\Http\Response
     */
    public function edit(Item $item)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  integer  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (!$request->has('absolute_location')) {
            return response()->error('上传失败E422', 422);
        }
        $item = Item::findOrFail($id);
        if ($item->user_id != auth('api')->user()->id) {
            return response()->error('文件权限查询失败，可能是登陆过期，请退出重新登录', 403);
        }
        $item->update(['absolute_location' => $request->absolute_location]);
        return response()->success([
            'msg' => '成功',
            'id' => $item->id
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        if (!$request->has('ids') || !is_array($request->input('ids')) || count($request->input('ids')) == 0) {
            return response()->error('无效的删除操作E422', 422);
        }
//        $items = Item::findOrFail($request->input('ids'));
        $itemsQuery = Item::whereIn('id',$request->input('ids'));
        $items = $itemsQuery->get();
        if (count($items) != count($request->input('ids'))) {
            return response()->error('一个或多个文件不存在或已被删除', 404);
        }
        foreach ($items as $item) {
            if ($item->user_id != auth('api')->user()->id) {
                return response()->error('文件权限查询失败，可能是登陆过期，请退出重新登录', 403);
            }
        }
        $itemsQuery->update(['absolute_location' => '']); // 设置为空字符串表示回收站，设置为'//'表示永久删除
        return response()->success([
            'msg' => '删除成功',
            'id' => $request->input('ids')
        ]);
    }


    /**
     *
     */
    public function getKey() {
        $sts = new STS();
        $config = array(
            'url' => 'https://sts.tencentcloudapi.com/',
            'domain' => 'sts.tencentcloudapi.com',
            'proxy' => '',
            'secretId' => config('app.cos_secret_id'), // 固定密钥
            'secretKey' => config('app.cos_secret_key'), // 固定密钥
            'bucket' => config('app.cos_bucket'), // 换成你的 bucket
            'region' => config('app.cos_region'), // 换成 bucket 所在园区
            'durationSeconds' => 1800, // 密钥有效期
            'allowPrefix' => '*', // 这里改成允许的路径前缀，可以根据自己网站的用户登录态判断允许上传的具体路径，例子： a.jpg 或者 a/* 或者 * (使用通配符*存在重大安全风险, 请谨慎评估使用)
            // 密钥的权限列表。简单上传和分片需要以下的权限，其他权限列表请看 https://cloud.tencent.com/document/product/436/31923
            'allowActions' => array (
                // 简单上传
                'name/cos:PutObject',
                'name/cos:PostObject',
                // 分片上传
                'name/cos:InitiateMultipartUpload',
                'name/cos:ListMultipartUploads',
                'name/cos:ListParts',
                'name/cos:UploadPart',
                'name/cos:CompleteMultipartUpload'
            )
        );

        $tempKeys = $sts->getTempKeys($config);

        return response()->success($tempKeys);
    }
}
