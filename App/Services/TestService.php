<?php
require_once "Micron2/Micron2.php";

class TsBody {
    public function __construct(public string $p1, public string $p2){}
}

#[Controller("test")]
class TestService {
    private HttpContext $_httpContext;
    private AppConfiguration $_config;

    public function __construct(HttpContext $context, AppConfiguration $config){
        $this->_httpContext = $context;
        $this->_config = $config;
    }

    #[Post("{id}")]
    public function GetMockData(#[FromPath] int $id, #[FromQuery] string $q1, #[FromFormData] TsBody $body){
        return HttpResponse::HttpOk200([
            "id" => $id, 
            "q1" => $q1
        ]);
    }
}