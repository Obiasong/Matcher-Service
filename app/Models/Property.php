<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    use HasFactory;
    protected $fillable = ["name", "address", "propertyType", "fields"];
    protected $casts = [
        "fields"=>'array'
    ];
    /**
     * @var mixed
     */
    private String $propertyType;
    /**
     * @var mixed
     */
    private array $fields;

    public static function getPropertyType($property_id){
        return Property::where('id', $property_id)->select('propertyType')->get();
    }

    public static function getPropertyFields($property_id){
        return Property::where('id', $property_id)->select('fields')->get();
    }

    public static function getProperty($property_id){
        return Property::find($property_id);
    }
}
