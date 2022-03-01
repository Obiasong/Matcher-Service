<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SearchProfile extends Model
{
    use HasFactory;
    protected $fillable = ["name", "propertyType", "searchFields"];
    protected $casts = [
        "searchFields"=>'array'
    ];
    private String $propertyType;
    private String $name;
    private array $searchFields;


    public function getSearchProfileFields($profile_id){
        return SearchProfile::where('id', $profile_id)->select('searchFields')->get();
    }

    public static function getPropertyTypeSearchProfiles($prop_type){
        return SearchProfile::where('propertyType', 'LIKE', $prop_type)->get();
    }

}
