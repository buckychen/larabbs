<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/1
 * Time: 17:26
 */
namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use Spatie\Permission\Models\Role;

class RoleTransformer extends TransformerAbstract{
    public function transform(Role $role){
        return [
            'id' => $role->id,
            'name' => $role->name,
        ];
    }
}