<?php

use SilverStripe\Control\HTTPRequest;

class EventPageController extends PageController
{
    private static $allowed_actions = [
        "index",
    ];
    private static $url_segment = 'event';
    private static $url_handlers = [
        '$ID' => 'index',
    ];

    public function index(HTTPRequest $request)
    {
        $id = $request->param("ID");
        $event = EventShop::get()->byID($id);

        if (!$event) {
            return $this->httpError(404);
        }

        $data = array_merge($this->getCommonData(), [
            "EventShop" => $event,
        ]);
        return $this->customise($data)->renderWith(["EventPage", "Page"]);
    }
}