<?php
namespace App\Model;
class User extends \Illuminate\Database\Eloquent\Model
{
    protected $guarded = ['id'];
    protected $hidden = array('id', 'email');

    /**
     * @param string $remoteId
     * @return \App\Model\User
     */
    public static function getByUniqueId($uniqueId)
    {
        return User::query()->where('uuid', '=', $uniqueId)->first();
    }

}