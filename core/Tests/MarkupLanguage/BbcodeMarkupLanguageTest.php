<?php

namespace Forum9000\Tests\MarkupLanguage;

use Forum9000\MarkupLanguage\BbcodeMarkupLanguage;
use PHPUnit\Framework\TestCase;

class BbcodeMarkupLanguageTest extends TestCase {
    public function testSanitizePlaintest() {
        $bbcodeMl = new BbcodeMarkupLanguage();
        $maliciousBbcode = "Safe crap";
        
        $sanitizedOutput = $bbcodeMl->formatMessage($maliciousBbcode);
        
        $this->assertEquals($sanitizedOutput->__toString(), 'Safe crap');
        $this->assertFalse($bbcodeMl->isLastMessageMalicious());
    }
    
    public function testSanitizeEmbeddedHtml() {
        $bbcodeMl = new BbcodeMarkupLanguage();
        $maliciousBbcode = "<script>alert('malicious');</script>";
        
        $sanitizedOutput = $bbcodeMl->formatMessage($maliciousBbcode);
        
        $this->assertEquals($sanitizedOutput->__toString(), '&lt;script&gt;alert(\'malicious\');&lt;/script&gt;');
        $this->assertFalse($bbcodeMl->isLastMessageMalicious());
        
        //That last assertion needs some explanation: The purpose of the malice
        //counter is to determine if a message has been altered by filtering.
        //It can be used to detect false positive filtering or actual attacks on
        //a forum. However, the mere presence of HTML tags in input does not
        //indicate malice on it's own. By design you should be able to stick
        //HTML into a BBCode parser and get escaped entities out - our parser
        //is just terrible at doing it, so we need to test us doing it here.
    }
    
    public function testSanitizeUrlAbsolute() {
        $bbcodeMl = new BbcodeMarkupLanguage();
        $nonmaliciousBbcode = "[url=http://example.com/forum]Safe[/url]";
        
        $sanitizedOutput = $bbcodeMl->formatMessage($nonmaliciousBbcode);
        
        $this->assertEquals($sanitizedOutput->__toString(), '<a href="http://example.com/forum">Safe</a>');
        $this->assertFalse($bbcodeMl->isLastMessageMalicious());
    }
    
    public function testSanitizeUrlMalicious() {
        $bbcodeMl = new BbcodeMarkupLanguage();
        $maliciousBbcode = "[url]javascript:alert('xss')[/url]";
        
        $sanitizedOutput = $bbcodeMl->formatMessage($maliciousBbcode);
        
        $this->assertEquals($sanitizedOutput->__toString(), '<a href="#">#</a>');
        $this->assertTrue($bbcodeMl->isLastMessageMalicious());
    }
    
    public function testSanitizeUrlMaliciousInline() {
        $bbcodeMl = new BbcodeMarkupLanguage();
        $maliciousBbcode = "[url=javascript:alert('xss')]Malicious[/url]";
        
        $sanitizedOutput = $bbcodeMl->formatMessage($maliciousBbcode);
        
        $this->assertEquals($sanitizedOutput->__toString(), '<a href="#">Malicious</a>');
        $this->assertTrue($bbcodeMl->isLastMessageMalicious());
    }
    
    public function testSanitizeImg() {
        $bbcodeMl = new BbcodeMarkupLanguage();
        $maliciousBbcode = "[img]javascript:alert('xss')[/img]";
        
        $sanitizedOutput = $bbcodeMl->formatMessage($maliciousBbcode);
        
        $this->assertEquals($sanitizedOutput->__toString(), '<img src="#" />');
        $this->assertTrue($bbcodeMl->isLastMessageMalicious());
    }
    
    public function testSanitizeColorHex() {
        $bbcodeMl = new BbcodeMarkupLanguage();
        $nonmaliciousBbcode = "[color=#12abc2aa]Safe[/color]";
        
        $sanitizedOutput = $bbcodeMl->formatMessage($nonmaliciousBbcode);
        
        $this->assertEquals($sanitizedOutput->__toString(), '<span style="color: #12abc2aa">Safe</span>');
        $this->assertFalse($bbcodeMl->isLastMessageMalicious());
    }
    
    public function testSanitizeColorWord() {
        $bbcodeMl = new BbcodeMarkupLanguage();
        $nonmaliciousBbcode = "[color=whitesmoke]Safe[/color]";
        
        $sanitizedOutput = $bbcodeMl->formatMessage($nonmaliciousBbcode);
        
        $this->assertEquals($sanitizedOutput->__toString(), '<span style="color: whitesmoke">Safe</span>');
        $this->assertFalse($bbcodeMl->isLastMessageMalicious());
    }
    
    public function testSanitizeColorHexMalformatted() {
        $bbcodeMl = new BbcodeMarkupLanguage();
        $maliciousBbcode = "[color=#ab]Malicious[/color]";
        
        $sanitizedOutput = $bbcodeMl->formatMessage($maliciousBbcode);
        
        $this->assertEquals($sanitizedOutput->__toString(), '<span style="color: black">Malicious</span>');
        $this->assertTrue($bbcodeMl->isLastMessageMalicious());
    }
    
    public function testSanitizeColorMalicious() {
        $bbcodeMl = new BbcodeMarkupLanguage();
        $maliciousBbcode = "[color=blue;background-image:expression()]Malicious[/color]";
        
        $sanitizedOutput = $bbcodeMl->formatMessage($maliciousBbcode);
        
        $this->assertEquals($sanitizedOutput->__toString(), '<span style="color: black">Malicious</span>');
        $this->assertTrue($bbcodeMl->isLastMessageMalicious());
    }
    
    public function testSanitizeSizeMalicious() {
        $bbcodeMl = new BbcodeMarkupLanguage();
        $maliciousBbcode = "[size=120;background-image:expression()]Malicious[/size]";
        
        $sanitizedOutput = $bbcodeMl->formatMessage($maliciousBbcode);
        
        $this->assertEquals($sanitizedOutput->__toString(), '<span style="font-size: 120%">Malicious</span>');
        $this->assertTrue($bbcodeMl->isLastMessageMalicious());
    }
    
    public function testSanitizeSizeSafe() {
        $bbcodeMl = new BbcodeMarkupLanguage();
        $nonmaliciousBbcode = "[size=120]Safe[/size]";
        
        $sanitizedOutput = $bbcodeMl->formatMessage($nonmaliciousBbcode);
        
        $this->assertEquals($sanitizedOutput->__toString(), '<span style="font-size: 120%">Safe</span>');
        $this->assertFalse($bbcodeMl->isLastMessageMalicious());
    }
    
    public function testSanitizeFontMalicious() {
        $bbcodeMl = new BbcodeMarkupLanguage();
        $maliciousBbcode = "[font=Arial;background-image:expression()]Malicious[/font]";
        
        $sanitizedOutput = $bbcodeMl->formatMessage($maliciousBbcode);
        
        $this->assertEquals($sanitizedOutput->__toString(), '<span style="font-family: Comic Sans, cursive">Malicious</span>');
        $this->assertTrue($bbcodeMl->isLastMessageMalicious());
    }
    
    public function testSanitizeFontSafe() {
        $bbcodeMl = new BbcodeMarkupLanguage();
        $nonmaliciousBbcode = "[font=Arial]Safe[/font]";
        
        $sanitizedOutput = $bbcodeMl->formatMessage($nonmaliciousBbcode);
        
        $this->assertEquals($sanitizedOutput->__toString(), '<span style="font-family: Arial, sans-serif">Safe</span>');
        $this->assertFalse($bbcodeMl->isLastMessageMalicious());
    }
}