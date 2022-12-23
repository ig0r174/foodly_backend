<?php

namespace App\Http\Resources\Supplements;

use App\Http\Resources\ProductResource;
use App\Models\Supplement;

class SupplementInspect
{

    private $composition;

    public function __construct($composition)
    {
        $this->composition = $composition;
    }

    public function inspectSupplements(): array
    {
        $result = [];
        $utf8Composition = mb_convert_encoding($this->composition, "cp1251", "utf-8");
        $supplements = Supplement::all();

        preg_match_all("/E\d+\w?/", $utf8Composition, $matches, PREG_OFFSET_CAPTURE);

        $supplementsData = [];
        foreach ($supplements as $supplement){

            if ($pos = mb_strpos($this->composition, mb_strtolower($supplement->getAttribute('full_name')))){
                if( in_array(mb_substr($this->composition, $pos - 1, 1), [" ", ")", "("]) ){
                    $matches[0][$pos] = [mb_strtolower($supplement->getAttribute('full_name')), $pos];
                }
            }

            foreach (["full_name", "eurocode"] as $field){
                $supplementsData[$field == "full_name" ? mb_strtolower($supplement->getAttribute($field)) : $supplement->getAttribute($field)] = [
                    "level" => $supplement->getAttribute('level'),
                    "id" => $supplement->getAttribute('id')
                ];
            }
        }

        foreach ($matches[0] as $match) {
            $result[] = [
                "name" => $match[0],
                "offset" => $match[1],
                "length" => mb_strlen($match[0]),
                "data" => $supplementsData[$match[0]] ?? null
            ];
        }

        return $result;
    }

}
