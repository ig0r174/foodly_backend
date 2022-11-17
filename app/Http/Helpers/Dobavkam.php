<?php namespace App\Http\Helpers;

use DiDom\Document;

class Dobavkam
{
    private string $url = "https://dobavkam.net/views/ajax?_wrapper_format=drupal_ajax";

    public function getCollection($page = 0)
    {
        $collection = [];
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => http_build_query([
                "view_name" => "additives",
                "view_display_id" => "additives",
                "view_args" => "",
                "view_path" => "/additives",
                "view_base_path" => "additives",
                "pager_element" => "0",
                "empty_info" => "0",
                "page" => $page,
                "_drupal_ajax" => "1",
                "ajax_page_state[theme]" => "dob_theme",
                "ajax_page_state[theme_token]" => "",
                "ajax_page_state[libraries]" => "addtoany/addtoany,better_exposed_filters/auto_submit,better_exposed_filters/general,blazy/bio.ajax,dob_theme/additive,dob_theme/base,hierarchical_taxonomy_menu/hierarchical_taxonomy_menu,system/base,views/views.ajax,views/views.module,views_infinite_scroll/views-infinite-scroll"
            ]),
            CURLOPT_HTTPHEADER => array(
                'Referer: https://dobavkam.net/additives',
                'Content-Type: application/x-www-form-urlencoded'
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);

        $html = json_decode($response, true)[$page == 0 ? 2 : 1]['data'];
        $document = new Document($html);
        foreach ($document->find('.views-row .addicon') as $supplement) {
            $collection[] = $supplement->find('.addicon__link')[0]->getAttribute('href');
        }

        if ($page == 0)
            return array_merge($collection, $this->getCollection(1));

        return $collection;
    }

    public function getSupplementData($link)
    {
        $document = new Document($link, true);
        preg_match("/(\w+).(\d+)/", $document->find('.addicon--big')[0]->getAttribute('class'), $matches);

        $data = [
            "eurocode" => $document->find(".addicon__name")[0]->text(),
            "full_name" => trim(preg_replace('/\s+/', ' ', explode(" – ", $document->find('.page-title')[0]->text())[1])),
            "level" => intval($matches[2]),
        ];

        if (!empty($document->find('.field--additive-info p'))) {
            $data["description"] = trim(preg_replace('/\s+/', ' ', $document->find('.field--additive-info p')[0]->text()));
        }

        $infoBlock = $document->find('.field--additive-info *');

        foreach ($infoBlock as $item) {
            if (!empty($lastTag) && $item->tagName() == "h2")
                break;

            if ($item->tagName() == "p" && !empty($lastTag)) {
                if ($lastTag->classes()->contains('good')) {
                    if (isset($data['benefit']))
                        $data['benefit'] .= " " . $item->text();
                    else
                        $data['benefit'] = $item->text();
                } else if ($lastTag->classes()->contains('poor')) {
                    if (isset($data['harm']))
                        $data['harm'] .= " " . $item->text();
                    else
                        $data['harm'] = $item->text();
                }
            }

            if ($item->tagName() == "h3")
                $lastTag = $item;
        }
        return $data;
    }

}
