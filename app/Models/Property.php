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
    private string $propertyType;
    private ?array $fields = null;

    public function getPropertyType()
    {
        return $this->propertyType;
    }

    public function getPropertyFields(): ?array
    {
        return $this->fields;
    }

    /**
     * @return String
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return String
     */
    public function getAddress(): string
    {
        return $this->address;
    }


}
