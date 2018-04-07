<?php

namespace Forum9000\CompactId;

use StephenHill\Base58;

trait RepositoryTrait {
    public function findByCompactId(string $id) {
        $raw_uuid = (new Base58())->decode($id);
        $uuid = "";

        foreach (str_split($raw_uuid) as $char) {
            $hexit = strtoupper(dechex(ord($char)));
            while (strlen($hexit) < 2) $hexit = "0" . $hexit;
            $uuid .= strrev($hexit);

            if (strlen($uuid) == 8) $uuid .= "-";
            else if (strlen($uuid) == 13) $uuid .= "-";
            else if (strlen($uuid) == 18) $uuid .= "-";
            else if (strlen($uuid) == 23) $uuid .= "-";
        }

        return $this->find($uuid);
    }
}