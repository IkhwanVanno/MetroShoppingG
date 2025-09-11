<?php

use SilverStripe\Control\HTTPRequest;

class FlashSalePageController extends PageController
{
    private static $allowed_action = [
        "index",
    ];
    private static $url_segment = 'flashsale';
    private static $url_handlers = [
        '$ID' => 'index',
    ];
    public function index(HTTPRequest $request)
    {
        $id = $request->param("ID");
        $flashSale = FlashSale::get()->byID($id);

        if (!$flashSale) {
            return $this->httpError(404);
        }

        $data = array_merge($this->getCommonData(), [
            "FlashSale" => $flashSale,
        ]);
        return $this->customise($data)->renderWith(["FlashSalePage", "Page"]);
    }
}