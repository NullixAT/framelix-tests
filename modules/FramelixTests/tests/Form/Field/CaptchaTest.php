<?php

namespace Form\Field;

use Framelix\Framelix\Config;
use Framelix\Framelix\ErrorCode;
use Framelix\Framelix\Form\Field\Captcha;
use Framelix\Framelix\Network\JsCall;
use Framelix\Framelix\Utils\ArrayUtils;
use Framelix\FramelixTests\TestCase;
use Throwable;

final class CaptchaTest extends TestCase
{
    public function tests(): void
    {
        Config::set('captchaKeys[recaptchav2][publicKey]', 'test');
        Config::set('captchaKeys[recaptchav2][privateKey]', 'test');
        Config::set('captchaKeys[recaptchav3][publicKey]', 'test');
        Config::set('captchaKeys[recaptchav3][privateKey]', 'test');
        $field = new Captcha();
        $field->type = $field::TYPE_RECAPTCHA_V2;
        $field->name = $field::class;
        $field->required = true;
        $this->setSimulatedPostData([$field->name => "Foo"]);

        $jsCall = new JsCall('verify', ['type' => $field->type]);
        $jsCall->call(Captcha::class);
        $this->assertTrue(ArrayUtils::keyExists($jsCall->result, 'hash'));

        $field->type = $field::TYPE_RECAPTCHA_V3;
        $jsCall = new JsCall('verify', ['type' => $field->type]);
        $jsCall->call(Captcha::class);
        $this->assertTrue(ArrayUtils::keyExists($jsCall->result, 'hash'));

        $this->assertIsString($field->validate());

        $this->assertTrue(ArrayUtils::keyExists($field->jsonSerialize(), 'properties[signedUrlVerifyToken]'));

        $e = null;
        try {
            $field->type = null;
            $field->jsonSerialize();
        } catch (Throwable $e) {
        }
        $this->assertFramelixErrorCode(ErrorCode::FORM_CAPTCHA_TYPE_MISSING, $e);
    }
}
