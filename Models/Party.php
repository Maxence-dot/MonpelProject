<?php

class Party
{
    public int $id;
    public ?int $game_type_id = null;
    public ?string $theme = null;
    public ?string $synopsis = null;
    public string $status = 'draft';
    public ?string $created_at = null;
    public ?string $updated_at = null;

    /**
     * Party constructor.
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
