<?php

class Relation
{
    public int $id;
    public int $character_id;
    public int $target_character_id;
    public string $relation_type;
    public ?string $description = null;
    public ?string $created_at = null;

    /**
     * Relation constructor.
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
