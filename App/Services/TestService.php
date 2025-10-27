<?php
require_once "Micron2/Micron2.php";

#[Controller("test")]
class TestService {

    #[Get("")]
    public function GetMockData(){
        return HttpResponse::HttpOk200([
            "k1" => "d1", 
            "k2" => "d2",
            "k3" => "d3",
        ]);
    }
}