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

    private String $name;
    private String $address;
    private ?string $propertyType = null;
    private ?array $fields = null;

    public function getPropertyType(): ?string
    {
        return $this->propertyType;
    }

    public function getPropertyFields(): ?array
    {
        return $this->fields;
    }

}
