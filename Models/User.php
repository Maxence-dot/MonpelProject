<?php

class User
{
    public int $id;
    public string $email;
    public string $password; // hashed
    public ?string $firstname = null;
    public ?string $lastname = null;
    public ?string $created_at = null;

    /**
     * User constructor.
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
