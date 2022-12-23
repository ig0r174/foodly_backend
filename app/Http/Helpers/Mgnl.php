<?php namespace App\Http\Helpers;

use DiDom\Document;

class Mgnl
{
    private string $url = "https://shop.mgnl.ru";

    public function getCategories(): array
    {
        $categories = [];
        $document = new Document($this->url, true);
        foreach ($document->find('.menu-row .menu-navigation a') as $item){
            if( !$this->isFoodCategory($item->find(".name")[0]->text()) ) continue;
            $categories[] = [
                "link" => $item->getAttribute('href'),
                "name" => $item->find('.name')[0]->text()
            ];
        }

        return $categories;
    }

    public function getProductData($link)
    {
        if( !stristr($link, $this->url) )
            $link = $this->url . $link;

        $document = new Document($link, true);
        foreach ($document->find("#rs_grupper .options li") as $li){
            if( count($li->find("span")) > 0 && $li->find("span")[0]->text() == "Состав" ){
                $composition = str_replace("Состав: ", "", $li->find("b")[0]->text());
                $name = $document->find("#pagetitle")[0]->text();
                if( !$this->isFoodComposition($composition) || !$this->isFoodComposition($name) ) continue;
                return [
                    "barcode" => intval($document->find(".product-info-headnote__article .article__value")[0]->text()),
                    "name" => $name,
                    "composition" => $composition ?? null,
                    "img_source" => sprintf("https://shop.mgnl.ru%s", $document->find("#photo-0 a")[0]->getAttribute('href')),
                    "link" => $link
                ];
            }
        }
    }

    public function getPagesCounts($categoryLink): int
    {
        $startLink = !stristr($categoryLink, $this->url) ? $this->url . $categoryLink : $categoryLink;
        $document = new Document($startLink, true);
        $lastPage = $document->find('.module-pagination .nums .dark_link:last-child');
        return count($lastPage) == 1 ? intval($lastPage[0]->text()) : 1;
    }

    public function getProductsByCategory($categoryLink, $page = 1): array
    {
        $productsLinks = [];
        $startLink = !stristr($categoryLink, $this->url) ? $this->url . $categoryLink : $categoryLink;
        $pageLink = sprintf("%s?PAGEN_1=%d&ajax_get=Y&AJAX_REQUEST=Y&bitrix_include_areas=N", $startLink, $page);

        $document = new Document($pageLink, true);
        foreach ($document->find('.item_block') as $item){
            $productsLinks[] = $item->find(".item-title a")[0]->getAttribute('href');
        }

        return $productsLinks;
    }

    private function isFoodCategory($categoryName): bool
    {
        return !in_array($categoryName, [
            "Системы нагревания табака",
            "Антибактериальные средства и маски",
            "Зоотовары",
            "Бытовая химия и хозтовары",
            "Дом, сад, кухня",
            "Красота, гигиена, аптека",
        ]);
    }

    private function isFoodComposition($text): bool
    {
        $notFoodComponents = [
            "трикотаж",
            "полимерн",
            "пластификатор",
            "металл",
            "сталь",
            "цинк",
            "медь",
            "пластизоль",
            "полиэстер",
            "полистирол",
            "целлюлоз",
            "бумага",
            "Isobutane",
            "Propane",
            "пропиленгликоль",
            "Alcohol",
            "Fluff",
            "хб ",
            "силикон",
            "Текстиль",
            "Алюминий",
            "Картон",
            "стекло",
            "керамика",
            "стекловолокно",
            "Гевея",
            "тефлон",
            "клей",
            "Цинк",
            "вискоза",
            "парафин",
            "Бамбук",
            "Сосна",
            "чугун",
            "Спанбонд",
            "фольга",
            "дерево",
            "пенополиуретан",
            "хлопок",
            "пав",
            "пластизоль",
            "для лепки",
            "Мех искусственный",
            "пластик",
            "aqua",
            "GLYCOL",
            "пвх",
            "INGREDIENTS",
            "water",
            "Полиэтилен",
            "зубная",
            "для тела",
            "для лица",
            "для душа",
            "Butane",
            "Sodium",
            "ацетон",
            "д/тела",
            "латекс",
            "уголь",
            "нейлон",
            "фарфор",
            "abs",
            "фибра",
            "эластан",
            "тарелк",
            "стакан",
            "кружка",
            "кошек",
            "собак",
            "корм ",
            "лосьон"
        ];

        foreach ($notFoodComponents as $notFoodComponent){
            if( mb_stristr($text, $notFoodComponent) ) return false;
        }

        return true;
    }

}
