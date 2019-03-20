<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/1
 * Time: 17:13
 */

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use Spatie\Permission\Models\Permission;

class PermissionTransformer extends TransformerAbstract{
    public function transform(Permission $permission){
        return [
            'id' => $permission->id,
            'name' => $permission->name,
        ];
    }
}