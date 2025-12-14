<?php

class Player
{
    public int $id;
    public int $party_id;
    public ?string $firstname = null;
    public ?string $lastname = null;
    public ?string $alias = null;
    public ?string $desired_role = null;
    public ?string $created_at = null;

    /**
     * Player constructor.
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
