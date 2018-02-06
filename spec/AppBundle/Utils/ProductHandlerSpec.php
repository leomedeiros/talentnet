<?php
namespace AppBundle\Spec\Utils;

describe("ProductHandler", function() {
    describe("Utils/ProductHandler", function() {

        it("When creating name is mandatory", function() {
            $params = [];
            $handler = new \AppBundle\Utils\ProductHandler;
            $ret = $this->create($params);
            expect($ret)->toContain('Name not found');
        });
    });
});