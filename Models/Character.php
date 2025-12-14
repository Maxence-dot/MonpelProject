<?php

class Character
{
    public int $id;
    public int $party_id;
    public string $firstname;
    public ?string $background = null;
    public ?string $created_at = null;
    public ?string $updated_at = null;

    /**
     * Character constructor.
     * @param array $attributes Associative array of property => value
     */
    public function __construct(array $attributes = [])
    {
        foreach ($attributes as $propertyName => $propertyValue) {
            if (property_exists($this, $propertyName)) {
                $this->$propertyName = $propertyValue;
            }
        }
    }
}
