<?php

namespace App\CompactId;

use StephenHill\Base58;

trait EntityTrait {
    public function getCompactId() {
        $packed_id = pack("h*", str_replace('-', '', $this->getId()));
        return (new Base58())->encode($packed_id);
    }
}