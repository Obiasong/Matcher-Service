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
    private $propertyType;
    /**
     * @var mixed
     */
    private $fields;

    public function getPropertyType(){
        return $this->propertyType;
    }
    public function getPropertyFields(){
        return $this->fields;
    }
}
