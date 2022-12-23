<?php namespace App\Http\Helpers;

use DiDom\Document;
use HeadlessChromium\Browser\ProcessAwareBrowser;
use HeadlessChromium\BrowserFactory;
use HeadlessChromium\Page;

class Sbermarket
{
    private ProcessAwareBrowser $browser;
    public string $token;

    public function __construct($withToken = true)
    {
        if ($withToken)
            $this->token = $this->getToken();

        $this->browser = $this->getBrowser();
    }

    private function getBrowser(): ProcessAwareBrowser
    {
        $browserFactory = new BrowserFactory("/usr/bin/google-chrome");

        if (isset($this->browser))
            return $this->browser;

        $this->browser = $browserFactory->createBrowser([
            //'headless' => false,
            'keepAlive' => true,
            'userAgent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/107.0.0.0 Safari/537.36',
            'windowSize' => [1920, 1080],
            //'noSandbox' => true,
            //'customFlags' => ['--disable-setuid-sandbox', '--no-sandbox', '--disable-seccomp-filter-sandbox'],
            'ignoreCertificateErrors' => true,
            //'proxyServer' => '46.232.6.7:8000'
            'debugLogger'     => 'php://stdout',
            'enableImages' => false,
            'customFlags' => [
                '--disable-dev-profile',
                '--disable-web-security',
                '--no-sandbox',
                '--no-zygote'
            ]
        ]);

        return $this->browser;
    }

    public function getSolution($cookies)
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'localhost:3000/getSolution',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($cookies),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        return json_decode($response)->solution;
    }

    private function getHomePage()
    {
        $browser = $this->getBrowser();

        $page = $browser->createPage();
        $page->navigate('https://sbermarket.ru')->waitForNavigation(Page::DOM_CONTENT_LOADED);

//        $cookies = [];
//        foreach (explode("&", $page->getCookies()->getAt(0)->getValue()) as $item){
//            list($k, $v) = explode("=", $item);
//            $cookies[$k] = urldecode($v);
//        }
//
//        $solution = $this->getSolution($cookies);

//        $page->navigate('https://sbermarket.ru')->waitForNavigation(Page::DOM_CONTENT_LOADED);
//

        if( stristr($page->dom()->getText(), "Выполняется проверка вашего") )
            $page->waitForReload(Page::DOM_CONTENT_LOADED);

        $page->dom()->search('form input[data-qa="multisearch_form_input"]');
        $geoInput = $page->dom()->querySelector('input');
        $geoInput->click();
        sleep(1);
        $geoInput->sendKeys('Красная площадь 1, Москва');
        sleep(2);
        $page->keyboard()->typeRawKey('Enter');
        $page->waitUntilContainsElement("header form input");
        return $page;
    }

    public function getCategories()
    {
        if (!empty($this->token)) {
            $result = $this->getApiResponse('https://sbermarket.ru/api/stores/12/categories?depth=2&include=&reset_cache=true');
            $categories = [];
            $goodCategories = [41328, 72088, 43466, 43519, 43504, 58784, 59096, 56259, 43480, 43508, 58701, 68254, 64981, 42546, 70974, 70559, 59335, 69169, 66510];

            foreach ($result->categories as $category) {
                if (in_array($category->id, $goodCategories) && !empty($category->children)) {
                    foreach ($category->children as $item) {
                        if ($item->is_promo || $category->id == 66510 && $item->id !== 66511 || stristr($item->name, "косметика")) continue;
                        $categories[] = [
                            "name" => $item->name,
                            "link" => $item->slug,
                            "parent" => $category->name
                        ];
                    }
                }
            }

            return $categories;
        }

    }

    private function getToken($page = null)
    {
        if (empty($page))
            $page = $this->getHomePage();

        return $page->evaluate('window.dynamicEnvsFromServer.STOREFRONT_API_V3_CLIENT_TOKEN')->getReturnValue();
    }

    public function getShops(): array
    {
        $response = $this->getApiResponse('https://sbermarket.ru/api/retailers?city_id=55&include=shipping_methods%2Clabel_retailer_ids&shipping_method=delivery');
        $shops = [];
        $approvedShops = [1, 15, 8, 122, 298, 283, 9, 3, 299, 107, 277, 137, 82, 287, 237];

        foreach ($response as $item) {
            if (!in_array($item->id, $approvedShops)) continue;
            $shops[] = [
                "id" => $item->id,
                "name" => $item->name,
                "link" => $item->slug
            ];
        }

        return $shops;
    }

    private function getApiResponse($link, $cookies = null)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $link,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'sec-ch-ua: "Google Chrome";v="107", "Chromium";v="107", "Not=A?Brand";v="24"',
                'sec-ch-ua-mobile: ?0',
                'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/107.0.0.0 Safari/537.36',
                'Accept: application/json, text/plain, */*',
                sprintf('client-token: %s', $this->token),
                'api-version: 3.0',
                'is-storefront-ssr: false',
                'sec-ch-ua-platform: "macOS"',
                'Sec-Fetch-Site: same-origin',
                'Sec-Fetch-Mode: cors',
                'Sec-Fetch-Dest: empty',
                'host: sbermarket.ru',
                'Cookie: external_analytics_anonymous_id=db7c8c7b-33f8-4d86-bea5-c95faec75f96'
            ),
        ));

        $response = curl_exec($curl);

        if ($error_n = curl_errno($curl)) {
            $error_msg = curl_error($curl);
        }

        curl_close($curl);

        return json_decode($response);
    }

    public function getProducts($shopLink, $categoryLink, $pageValue)
    {
        $link = sprintf("https://sbermarket.ru/%s/c/%s?page=%d", $shopLink, $categoryLink, $pageValue);
        $browser = $this->getBrowser();
        $page = $browser->createPage();
        $page->navigate($link)->waitForNavigation(Page::DOM_CONTENT_LOADED);

        if( $pageValue == 1 )
            $page->waitForReload(Page::DOM_CONTENT_LOADED);

        $evaluation = $page->callFunction("
    async function checkDOMChange(){
        if( document.querySelector('[data-qa=\"category_ab_flat_products_products_products_grid\"] a') !== null ){
            let products = [];
            let productsChild = document.querySelectorAll('[data-qa=\"category_ab_flat_products_products_products_grid\"] a');
            productsChild.forEach((item) => {
                    products.push({
                        name: item.querySelector(\"h3\").innerText,
                        link: item.getAttribute(\"href\"),
                        image: item.querySelector(\"img\").getAttribute(\"src\")
                    });
                })
                return products;
        } else return new Promise((resolve, reject) => {
                  setTimeout(() => {
                    checkDOMChange();
                    resolve();
                  }, 100)
                });
    }");

        $products = $evaluation->getReturnValue();
        return $products;
    }

    public function close(): void
    {
        $this->browser->close();
    }

    public function getProductData(int $barcode)
    {
        $page = $this->getHomePage();

        $this->token = $this->getToken($page);

        $page->waitUntilContainsElement("header form input");
        sleep(3);
        $page->dom()->querySelector("header form input")->sendKeys($barcode);

        try {
            $lat = '55.755246';
            $lon = '37.617779';

            $params = [
                "q" => $barcode,
                "lat" => $lat,
                "lon" => $lon,
                "include" => [
                    "retailer",
                    "closest_shipping_options"
                ],
                "shipping_method" => "delivery"
            ];

            //$stores = $this->getApiResponse('https://sbermarket.ru/api/multisearches?' . http_build_query($params));

            if( empty($stores) )
                throw new \Exception();

            $firstStoreId = $stores[0]->store_id;

            $searchLink = sprintf('https://sbermarket.ru/api/stores/%s/products?q=%s&page=1&per_page=15', $firstStoreId, $barcode);

            $searchResults = $page->callFunction(sprintf('
        async function get()
        {
        let d = await fetch("%s", {
          "headers": {
            "accept": "application/json, text/plain, */*",
            "accept-language": "ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7",
            "api-version": "3.0",
            "client-token": "%s",
            "if-none-match": "W/\"f596d8def26415803a97ef68c7cc5396\"",
            "is-storefront-ssr": "false",
            "sec-ch-ua": "\"Google Chrome\";v=\"107\", \"Chromium\";v=\"107\", \"Not=A?Brand\";v=\"24\"",
            "sec-ch-ua-mobile": "?0",
            "sec-ch-ua-platform": "\"macOS\"",
            "sec-fetch-dest": "empty",
            "sec-fetch-mode": "cors",
            "sec-fetch-site": "same-origin"
          },
          "referrer": "https://sbermarket.ru/",
          "referrerPolicy": "strict-origin-when-cross-origin",
          "body": null,
          "method": "GET",
          "mode": "cors",
          "credentials": "include"
        }).then((response) => response.json());
           return d;
        }', $searchLink, $this->token))->waitForResponse()->getReturnValue();

            if( empty($searchResults['products']) )
                throw new \Exception();

            $searchResultsProduct = $searchResults['products'][0];

            $productUrl = sprintf('https://sbermarket.ru/api/stores/%s/products/%s', $firstStoreId, $searchResultsProduct['slug']);
            $productData = $page->callFunction(sprintf('
        async function get()
        {
        let d = await fetch("%s", {
  "headers": {
    "accept": "application/json, text/plain, */*",
    "accept-language": "ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7",
    "if-none-match": "W/\"70e869e1f56dfbcdd65b2c658aafd137\"",
    "sec-ch-ua": "\"Google Chrome\";v=\"107\", \"Chromium\";v=\"107\", \"Not=A?Brand\";v=\"24\"",
    "sec-ch-ua-mobile": "?0",
    "sec-ch-ua-platform": "\"macOS\"",
    "sec-fetch-dest": "empty",
    "sec-fetch-mode": "cors",
    "sec-fetch-site": "same-origin"
  },
  "referrer": "https://sbermarket.ru/",
  "referrerPolicy": "strict-origin-when-cross-origin",
  "body": null,
  "method": "GET",
  "mode": "cors",
  "credentials": "include"
}).then((response) => response.json());
            console.log(d);
           return d;
        }', $productUrl))->waitForResponse()->getReturnValue();

            if( empty($productData['product']) )
                throw new \Exception();

            foreach ($productData['product_properties'] as $property){
                if( $property['name'] == 'ingredients' ){
                    $composition = $property['value'];
                    break;
                }
            }

            if( empty($composition) )
                throw new \Exception();

            $this->close();

            return [
                "barcode" => $barcode,
                "name" => $productData['product']['name'],
                "composition" => $composition,
                "img_source" => $productData['product']['images'][0]['product_url'],
                "link" => $productUrl
            ];
        } catch (\Exception $e){

            sleep(3);
            $image = $page->evaluate('document.querySelector(\'div[data-qa="multisearch_item_0"] a img\').getAttribute("src")')->getReturnValue();
            sleep(1);
            $page->mouse()->find('div[data-qa=\"multisearch_item_0\"] a')->click();
            sleep(5);

            $evaluation = $page->callFunction("
            async function getIngredients(){
                if( document.querySelector('.ingredients__text') !== null ){
                    return {
                       \"barcode\": \"$barcode\",
                        \"composition\": document.querySelector('.ingredients__text').innerText,
                        \"name\": document.querySelector('h1[itemprop=\"name\"]').innerText,
                        \"img\": \"$image\"
                    }
                } else return null;
            }");

            $res = $evaluation->getReturnValue();
            $this->close();

            return $res;

//            $page->waitUntilContainsElement("div[data-qa=\"multisearch_item_0\"] > a");
//
//            return ["d"];

//            sleep(5);
//
//            $evaluation = $page->callFunction("
//    async function checkModal(clicked = false){
//
//        async function getIngredients(){
//                if( document.querySelector('.ingredients__text') !== null ){
//                    return {
//                       \"barcode\": \"$barcode\",
//                        \"composition\": document.querySelector('.ingredients__text').innerText,
//                        \"name\": document.querySelector('h1[itemprop=\"name\"]').innerText,
//                        \"img\": ''
//                    }
//                } else return new Promise((resolve, reject) => {
//                          setTimeout(() => {
//                            resolve(getIngredients());
//                          }, 100)
//                        });
//            }
//
//        if( document.querySelector('div[data-qa=\"multisearch_item_0\"] a') !== null ){
//
//            if( !clicked )
//                document.querySelector('div[data-qa=\"multisearch_item_0\"] a').click();
//
//            while(document.querySelector('.ingredients__text') === null){
//                return getIngredients(true);
//            }
//
//            return getIngredients(true);
//        } else return new Promise((resolve, reject) => {
//                  setTimeout(() => {
//                    resolve(checkModal());
//                  }, 100)
//                });
//    }");
//            return $evaluation->getReturnValue();


        }

    }

}
