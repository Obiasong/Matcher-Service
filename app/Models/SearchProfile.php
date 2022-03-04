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
    private ?array $searchFields = null;


    public function getSearchProfileFields(): ?array
    {
        return $this->searchFields;
    }

    /**
     * @return String
     */
    public function getName(): string
    {
        return $this->name;
    }

    public function getSearchProfilePropertyType(): string
    {
        return $this->propertyType;
    }
    public static function getPropertyTypeSearchProfiles($propType){
        return SearchProfile::where('propertyType', 'LIKE', $propType)->get();
    }

}
