<?php

namespace App\Entity;
class RechercherPrix
{
    public ?float $prix_min = null;
    public ?float $prix_max = null;
 
    public function getPrixMin(): ?float
    {
        return $this->prix_min;
    }
    public function getPrixMax(): ?float
    {
        return $this->prix_max;
    }
}