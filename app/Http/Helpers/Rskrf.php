<?php namespace App\Http\Helpers;

use DiDom\Document;

class Rskrf
{
    private string $url = "https://rskrf.ru";

    public function getCategories(): array
    {
        $categoriesLinks = [];
        $document = new Document($this->url, true);
        foreach ($document->find('.menu_ratings ul li.active ul li ul a') as $item){
            if( !stristr($item->getAttribute('href'), "/ratings/") ) continue;
            $categoriesLinks[] = $item->getAttribute('href');
        }

        return $categoriesLinks;
    }

    public function getItems($categoryLink): ?array
    {
        $document = new Document($this->url . $categoryLink, true);
        foreach ($document->find('noscript') as $noScript){
            if( stristr($noScript->innerHtml(), "href") ){
                $noScriptHtml = new Document($noScript->innerHtml());
                $itemsLinks = [];
                foreach ($noScriptHtml->find('a') as $item){
                    $itemsLinks[] = $item->getAttribute('href');
                }
                return $itemsLinks;
            }
        }
        return null;
    }

    public function getItemData($itemLink): array
    {
        $document = new Document($this->url . $itemLink, true);

        $barcode = null;
        $research_date = null;
        foreach ($document->find('.properties li') as $property){
            $pText = $property->find('p')[count($property->find('p')) - 1]->text();

            if( $property->first('p')->text() == "Штрихкод" )
                $barcode = intval($pText);

            if( $property->first('p')->text() == "Год исследования" )
                $research_date = intval($pText);
        }

        $research = [];
        foreach ($document->find('.product-rating .rating-item') as $item){
            $research[] = [
                "name" => $item->first("span")->text(),
                "value" => $item->first(".starrating span") ?
                    floatval($item->first(".starrating span")->text()) : floatval($item->first(".word-rating span")->text())
            ];
        }

        $info = [];
        foreach ($document->find('.features ul li') as $item){
            $info[] = [
                "type" => $item->parent()->parent()->classes()->contains('good-features') ? "good" : "bad",
                "value" => $item->text()
            ];
        }

        return [
            "name" => trim($document->first('.product-subtitle')->text()),
            "link" => $this->url . $itemLink,
            "rating" => floatval($document->first('.rating-item.big .starrating span')->text()),
            "is_intruder" => (bool)stristr($document->first('.image-tooltip'), 'Товар-нарушитель'),
            "barcode" => $barcode,
            "research_date" => $research_date,
            "research" => $research,
            "info" => $info
        ];
    }

}
