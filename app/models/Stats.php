<?php
namespace App\Model;

class Stats extends \Illuminate\Database\Eloquent\Model
{
    protected $guarded = ['id'];
    protected $hidden = ['id','user_uuid'];

    public function user()
    {
        return $this->hasOne('App\Model\User','uuid','user_uuid');
    }

    /**
     * @param string $remoteId
     * @return \App\Model\User
     */
    public static function getByUniqueId($uniqueId)
    {
        return Stats::query()->where('user_uuid', '=', $uniqueId)->first();
    }

    /* Mutators and accessors */
    public function getCitiesAttribute($value){
        return unserialize($value);
    }

    public function setCitiesAttribute($value){
        $this->attributes['cities'] = serialize($value);
    }

    public function getProductsAttribute($value){
        return unserialize($value);
    }

    public function setProductsAttribute($value){
        uasort($value, function($a, $b) {
            return $b['count'] - $a['count'];
        });
        $this->attributes['products'] = serialize($value);
    }

}